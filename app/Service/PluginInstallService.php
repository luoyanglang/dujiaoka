<?php

namespace App\Service;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use ZipArchive;

class PluginInstallService
{
    private $pluginPath;
    private $tempPath;

    public function __construct()
    {
        $this->pluginPath = public_path('plugins');
        $this->tempPath = storage_path('plugins');
        
        // 确保目录存在
        if (!File::exists($this->pluginPath)) {
            File::makeDirectory($this->pluginPath, 0755, true);
        }
        if (!File::exists($this->tempPath)) {
            File::makeDirectory($this->tempPath, 0755, true);
        }
    }

    /**
     * 下载插件
     */
    public function downloadPlugin($downloadUrl)
    {
        try {
            // 如果是本地文件路径，直接返回
            if (file_exists($downloadUrl)) {
                return [
                    'success' => true,
                    'file_path' => $downloadUrl
                ];
            }

            // ========== 安全验证：URL 白名单 ==========
            $parsedUrl = parse_url($downloadUrl);
            
            if (!$parsedUrl || !isset($parsedUrl['host'])) {
                throw new \Exception('无效的下载地址');
            }
            
            // 白名单域名
            $allowedDomains = [
                'shouquan.ymd.cc',  // 授权系统域名
                'cdn.ymd.cc',       // CDN 域名
                parse_url(env('LICENSE_API_URL'), PHP_URL_HOST), // 配置的授权系统域名
            ];
            
            // 移除空值
            $allowedDomains = array_filter($allowedDomains);
            
            if (!in_array($parsedUrl['host'], $allowedDomains)) {
                throw new \Exception('下载地址不在白名单中: ' . $parsedUrl['host']);
            }
            
            // 只允许 HTTPS
            if ($parsedUrl['scheme'] !== 'https') {
                throw new \Exception('只允许 HTTPS 下载');
            }

            $fileName = 'plugin_' . time() . '_' . md5($downloadUrl) . '.zip';
            $filePath = $this->tempPath . '/' . $fileName;

            // ========== 启用 SSL 验证 ==========
            $client = new Client([
                'timeout' => 300,
                'verify' => true,  // 启用 SSL 验证
                'headers' => [
                    'User-Agent' => 'DujiaokaPluginInstaller/1.0',
                ],
            ]);
            
            $response = $client->get($downloadUrl);
            
            if ($response->getStatusCode() === 200) {
                $content = $response->getBody()->getContents();
                
                // ========== 验证文件大小 ==========
                $maxSize = 50 * 1024 * 1024; // 50MB
                if (strlen($content) > $maxSize) {
                    throw new \Exception('文件过大，超过 50MB 限制');
                }
                
                // ========== 验证文件类型（魔术字节） ==========
                $magicBytes = substr($content, 0, 4);
                // ZIP 文件的魔术字节：PK\x03\x04
                if ($magicBytes !== "PK\x03\x04") {
                    throw new \Exception('文件不是有效的 ZIP 格式');
                }
                
                File::put($filePath, $content);
                
                Log::info("插件下载成功: {$downloadUrl}");
                
                return [
                    'success' => true,
                    'file_path' => $filePath
                ];
            }
            
            return [
                'success' => false,
                'message' => '下载失败，HTTP 状态码: ' . $response->getStatusCode()
            ];
        } catch (\Exception $e) {
            Log::error('下载插件失败: ' . $e->getMessage());
            
            // 生产环境隐藏详细错误
            if (app()->environment('production')) {
                return [
                    'success' => false,
                    'message' => '下载失败，请联系管理员'
                ];
            }
            
            return [
                'success' => false,
                'message' => '下载失败: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 安装插件
     */
    public function installPlugin($pluginSlug, $zipFile)
    {
        try {
            $zip = new ZipArchive();
            
            if ($zip->open($zipFile) === true) {
                $extractPath = $this->pluginPath . '/' . $pluginSlug;
                
                // ========== 安全验证：检查 zip 文件内容 ==========
                $allowedExtensions = ['php', 'json', 'md', 'txt', 'js', 'css', 'png', 'jpg', 'jpeg', 'gif', 'svg'];
                
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);
                    
                    // 检查路径遍历攻击
                    if (strpos($filename, '..') !== false || strpos($filename, './') === 0) {
                        $zip->close();
                        File::delete($zipFile);
                        throw new \Exception('检测到非法文件路径，安装已终止');
                    }
                    
                    // 检查绝对路径
                    if (strpos($filename, '/') === 0 || preg_match('/^[a-zA-Z]:/', $filename)) {
                        $zip->close();
                        File::delete($zipFile);
                        throw new \Exception('检测到绝对路径，安装已终止');
                    }
                    
                    // 检查文件扩展名（白名单）
                    if (!is_dir($filename)) {
                        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                        if (!empty($ext) && !in_array($ext, $allowedExtensions)) {
                            $zip->close();
                            File::delete($zipFile);
                            throw new \Exception('检测到非法文件类型: ' . $ext . '，安装已终止');
                        }
                    }
                }
                
                // 如果目录已存在，先删除
                if (File::exists($extractPath)) {
                    File::deleteDirectory($extractPath);
                }
                
                // 解压
                $zip->extractTo($extractPath);
                $zip->close();
                
                // ========== 验证必需文件 ==========
                if (!file_exists($extractPath . '/plugin.json')) {
                    File::deleteDirectory($extractPath);
                    File::delete($zipFile);
                    throw new \Exception('插件缺少 plugin.json 文件，安装已终止');
                }
                
                // 验证 plugin.json 格式
                $pluginJson = json_decode(file_get_contents($extractPath . '/plugin.json'), true);
                if (!$pluginJson || !isset($pluginJson['name']) || !isset($pluginJson['slug'])) {
                    File::deleteDirectory($extractPath);
                    File::delete($zipFile);
                    throw new \Exception('plugin.json 格式错误，安装已终止');
                }
                
                // 验证 slug 一致性
                if ($pluginJson['slug'] !== $pluginSlug) {
                    File::deleteDirectory($extractPath);
                    File::delete($zipFile);
                    throw new \Exception('插件标识不匹配，安装已终止');
                }
                
                // 删除临时文件
                File::delete($zipFile);
                
                // ========== 记录安全日志 ==========
                Log::info("插件安装成功", [
                    'plugin_slug' => $pluginSlug,
                    'plugin_name' => $pluginJson['name'] ?? 'Unknown',
                    'plugin_version' => $pluginJson['version'] ?? 'Unknown',
                    'user_ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'timestamp' => now()->toDateTimeString(),
                ]);
                
                return [
                    'success' => true,
                    'message' => '安装成功'
                ];
            }
            
            return [
                'success' => false,
                'message' => '解压失败'
            ];
        } catch (\Exception $e) {
            Log::error('安装插件失败: ' . $e->getMessage());
            
            // 生产环境隐藏详细错误
            if (app()->environment('production')) {
                return [
                    'success' => false,
                    'message' => '安装失败，请联系管理员'
                ];
            }
            
            return [
                'success' => false,
                'message' => '安装失败: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 卸载插件
     */
    public function uninstallPlugin($pluginSlug)
    {
        try {
            $pluginDir = $this->pluginPath . '/' . $pluginSlug;
            
            if (File::exists($pluginDir)) {
                File::deleteDirectory($pluginDir);
                return [
                    'success' => true,
                    'message' => '卸载成功'
                ];
            }
            
            return [
                'success' => false,
                'message' => '插件目录不存在'
            ];
        } catch (\Exception $e) {
            Log::error('卸载插件失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => '卸载失败: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 启用插件
     */
    public function enablePlugin($pluginSlug)
    {
        // 这里可以添加启用插件的逻辑
        // 例如：加载插件配置、注册路由等
        return [
            'success' => true,
            'message' => '启用成功'
        ];
    }

    /**
     * 禁用插件
     */
    public function disablePlugin($pluginSlug)
    {
        // 这里可以添加禁用插件的逻辑
        return [
            'success' => true,
            'message' => '禁用成功'
        ];
    }

    /**
     * 检查插件是否已安装
     */
    public function isInstalled($pluginSlug)
    {
        $pluginDir = $this->pluginPath . '/' . $pluginSlug;
        return File::exists($pluginDir);
    }

    /**
     * 完整安装流程（下载 + 解压 + 记录）
     */
    public function install($pluginSlug, $downloadUrl = null, $licenseKey = null)
    {
        try {
            // 如果提供了下载地址，先下载
            if ($downloadUrl) {
                $downloadResult = $this->downloadPlugin($downloadUrl);
                if (!$downloadResult['success']) {
                    return $downloadResult;
                }
                $zipFile = $downloadResult['file_path'];
            } else {
                // 免费插件，使用默认下载地址（这里可以配置一个默认的插件仓库）
                return [
                    'success' => false,
                    'message' => '缺少下载地址'
                ];
            }

            // 安装插件
            $installResult = $this->installPlugin($pluginSlug, $zipFile);
            if (!$installResult['success']) {
                return $installResult;
            }

            // 记录到数据库
            $domain = request()->getHost();
            \App\Models\InstalledPlugin::create([
                'plugin_slug' => $pluginSlug,
                'license_key' => $licenseKey,
                'domain' => $domain,
                'status' => 'active',
                'activated_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => '插件安装成功'
            ];
        } catch (\Exception $e) {
            Log::error('插件安装失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => '安装失败: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 完整卸载流程（删除文件 + 删除记录）
     */
    public function uninstall($pluginSlug)
    {
        try {
            // 获取插件信息（用于日志）
            $installedPlugin = \App\Models\InstalledPlugin::where('plugin_slug', $pluginSlug)->first();
            
            // 删除插件文件
            $uninstallResult = $this->uninstallPlugin($pluginSlug);
            if (!$uninstallResult['success']) {
                return $uninstallResult;
            }

            // 删除数据库记录
            \App\Models\InstalledPlugin::where('plugin_slug', $pluginSlug)->delete();

            // ========== 记录安全日志 ==========
            Log::info("插件卸载成功", [
                'plugin_slug' => $pluginSlug,
                'license_key' => $installedPlugin ? substr($installedPlugin->license_key, 0, 10) . '...' : null,
                'user_ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            return [
                'success' => true,
                'message' => '插件卸载成功'
            ];
        } catch (\Exception $e) {
            Log::error('插件卸载失败: ' . $e->getMessage());
            
            // 生产环境隐藏详细错误
            if (app()->environment('production')) {
                return [
                    'success' => false,
                    'message' => '卸载失败，请联系管理员'
                ];
            }
            
            return [
                'success' => false,
                'message' => '卸载失败: ' . $e->getMessage()
            ];
        }
    }
}
