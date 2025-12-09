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

            $fileName = 'plugin_' . time() . '.zip';
            $filePath = $this->tempPath . '/' . $fileName;

            $client = new Client([
                'timeout' => 300,
                'verify' => false,
            ]);
            
            $response = $client->get($downloadUrl);
            
            if ($response->getStatusCode() === 200) {
                File::put($filePath, $response->getBody()->getContents());
                return [
                    'success' => true,
                    'file_path' => $filePath
                ];
            }
            
            return [
                'success' => false,
                'message' => '下载失败'
            ];
        } catch (\Exception $e) {
            Log::error('下载插件失败: ' . $e->getMessage());
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
                
                // 如果目录已存在，先删除
                if (File::exists($extractPath)) {
                    File::deleteDirectory($extractPath);
                }
                
                // 解压
                $zip->extractTo($extractPath);
                $zip->close();
                
                // 删除临时文件
                File::delete($zipFile);
                
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
            // 删除插件文件
            $uninstallResult = $this->uninstallPlugin($pluginSlug);
            if (!$uninstallResult['success']) {
                return $uninstallResult;
            }

            // 删除数据库记录
            \App\Models\InstalledPlugin::where('plugin_slug', $pluginSlug)->delete();

            return [
                'success' => true,
                'message' => '插件卸载成功'
            ];
        } catch (\Exception $e) {
            Log::error('插件卸载失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => '卸载失败: ' . $e->getMessage()
            ];
        }
    }
}
