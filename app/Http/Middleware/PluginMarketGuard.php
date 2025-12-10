<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class PluginMarketGuard
{
    /**
     * 核心文件列表
     */
    private $coreFiles = [
        'app/Http/Controllers/PluginMarketController.php',
        'app/Service/PluginLicenseService.php',
        'app/Http/Middleware/PluginMarketGuard.php',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next)
    {
        // 检查1：核心文件是否存在
        $this->checkFiles();

        // 检查2：文件完整性（MD5）
        $this->checkIntegrity();

        // 检查3：数据库标记
        $this->checkDatabase();

        return $next($request);
    }

    /**
     * 检查核心文件是否存在
     */
    private function checkFiles()
    {
        foreach ($this->coreFiles as $file) {
            if (!File::exists(base_path($file))) {
                $this->lockSystem('核心文件缺失: ' . $file);
            }
        }
    }

    /**
     * 检查文件完整性
     */
    private function checkIntegrity()
    {
        // 只在10%的请求中检查（降低性能影响）
        if (rand(1, 10) !== 1) {
            return;
        }

        $storedHash = Cache::get('plugin_market_hash');
        
        if ($storedHash) {
            $currentHash = md5_file(app_path('Http/Controllers/PluginMarketController.php'));
            
            if ($currentHash !== $storedHash) {
                // 不立即锁定，而是记录时间，7天后锁定
                if (!Cache::has('system_compromised')) {
                    Cache::put('system_compromised', time(), now()->addDays(7));
                }
                
                // 检查是否已过7天
                $compromisedTime = Cache::get('system_compromised');
                if (time() - $compromisedTime > 7 * 24 * 3600) {
                    $this->lockSystem('核心文件被篡改');
                }
            }
        } else {
            // 首次运行，记录哈希
            $hash = md5_file(app_path('Http/Controllers/PluginMarketController.php'));
            Cache::forever('plugin_market_hash', $hash);
        }
    }

    /**
     * 检查数据库标记
     */
    private function checkDatabase()
    {
        try {
            $installed = DB::table('plugin_market_config')
                ->where('key', 'market_installed')
                ->where('value', 'true')
                ->exists();

            if (!$installed) {
                $this->lockSystem('系统配置异常');
            }
        } catch (\Exception $e) {
            // 数据库表不存在，可能是首次安装
            // 不做处理
        }
    }

    /**
     * 锁定系统
     */
    private function lockSystem($reason)
    {
        // 写入锁定文件
        $lockFile = storage_path('framework/down');
        $lockData = json_encode([
            'time' => time(),
            'reason' => $reason,
            'message' => '系统维护中，请联系技术支持',
            'retry' => time() + 3600, // 1小时后重试
        ]);
        
        File::put($lockFile, $lockData);

        // 记录日志
        \Log::error('系统已锁定: ' . $reason);

        // 返回503错误
        abort(503, '系统维护中，请联系技术支持');
    }
}
