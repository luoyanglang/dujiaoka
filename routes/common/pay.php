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
        $routeFile = public_path("plugins/{$plugin->plugin_slug}/routes.php");
        
        if (file_exists($routeFile)) {
            require $routeFile;
        }
    }
    
    // 注意：原有的支付路由已移除，现在通过插件提供
    // 如果需要临时兼容，可以在这里添加回退逻辑
});
