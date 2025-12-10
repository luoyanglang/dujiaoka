<?php
/**
 * The file was created by Assimon.
 *
 * @author    assimon<ashang@utf8.hk>
 * @copyright assimon<ashang@utf8.hk>
 * @link      http://utf8.hk/
 */
use Illuminate\Support\Facades\Route;

Route::get('pay-gateway/{handle}/{payway}/{orderSN}', 'PayController@redirectGateway');

// 支付相关 - 动态加载已安装的支付插件路由
Route::group(['middleware' => ['dujiaoka.pay_gate_way']], function () {
    // 动态加载已安装支付插件的路由
    $installedPlugins = \App\Models\InstalledPlugin::where('status', 'active')->get();
    
    foreach ($installedPlugins as $plugin) {
        $pluginPath = public_path("plugins/{$plugin->plugin_slug}");
        $routeFile = $pluginPath . '/routes.php';
        
        if (file_exists($routeFile)) {
            // ========== 安全验证：插件完整性检查 ==========
            try {
                // 检查 plugin.json 是否存在
                $pluginJsonFile = $pluginPath . '/plugin.json';
                if (!file_exists($pluginJsonFile)) {
                    \Log::warning("插件缺少 plugin.json: {$plugin->plugin_slug}");
                    continue;
                }
                
                // 验证 plugin.json 格式
                $pluginJson = json_decode(file_get_contents($pluginJsonFile), true);
                if (!$pluginJson || !isset($pluginJson['slug'])) {
                    \Log::warning("插件 plugin.json 格式错误: {$plugin->plugin_slug}");
                    continue;
                }
                
                // 验证 slug 一致性
                if ($pluginJson['slug'] !== $plugin->plugin_slug) {
                    \Log::warning("插件标识不匹配: {$plugin->plugin_slug}");
                    continue;
                }
                
                // 加载路由
                require $routeFile;
                
            } catch (\Exception $e) {
                \Log::error("加载插件路由失败: {$plugin->plugin_slug}, 错误: " . $e->getMessage());
            }
        }
    }
    
    // 注意：原有的支付路由已移除，现在通过插件提供
    // 如果需要临时兼容，可以在这里添加回退逻辑
});
