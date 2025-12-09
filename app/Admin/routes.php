<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;

Admin::routes();

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'namespace'  => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('/', 'HomeController@index');
    $router->resource('goods', 'GoodsController');
    $router->resource('goods-group', 'GoodsGroupController');
    $router->resource('carmis', 'CarmisController');
    // $router->resource('coupon', 'CouponController'); // 已移除，改为通过会员系统插件提供
    $router->resource('emailtpl', 'EmailtplController');
    // $router->resource('pay', 'PayController'); // 已移除，改为通过支付插件提供
    $router->resource('order', 'OrderController');
    $router->get('import-carmis', 'CarmisController@importCarmis');
    $router->get('system-setting', 'SystemSettingController@systemSetting');
    $router->get('email-test', 'EmailTestController@emailTest');
    $router->resource('security-log', 'SecurityLogController');
    $router->post('security-log/clear-old', 'SecurityLogController@clearOldLogs');
    
    // 插件管理路由（注意：特殊路由必须放在 resource 之前）
    $router->get('plugins/sync', 'PluginController@sync')->name('admin.plugins.sync');
    $router->get('plugins/{id}/install', 'PluginController@install')->name('admin.plugins.install');
    $router->get('plugins/{id}/uninstall', 'PluginController@uninstall')->name('admin.plugins.uninstall');
    $router->get('plugins/{id}/enable', 'PluginController@enable')->name('admin.plugins.enable');
    $router->get('plugins/{id}/disable', 'PluginController@disable')->name('admin.plugins.disable');
    $router->get('plugins/{id}/purchase', 'PluginController@purchase')->name('admin.plugins.purchase');
    $router->get('plugins/{id}/activate', 'PluginController@activate')->name('admin.plugins.activate');
    $router->post('plugins/do-activate', 'PluginController@doActivate')->name('admin.plugins.do-activate');
    $router->get('plugins/check-order', 'PluginController@checkOrder')->name('admin.plugins.check-order');
    $router->resource('plugins', 'PluginController');
});
