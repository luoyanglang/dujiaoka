<?php
/**
 * The file was created by Assimon.
 *
 * @author    assimon<ashang@utf8.hk>
 * @copyright assimon<ashang@utf8.hk>
 * @link      http://utf8.hk/
 */
use Illuminate\Support\Facades\Route;


Route::group(['middleware' => ['dujiaoka.boot'],'namespace' => 'Home'], function () {
    // 首页
    Route::get('/', 'HomeController@index');
    // 极验效验
    Route::get('check-geetest', 'HomeController@geetest');
    // 商品详情
    Route::get('buy/{id}', 'HomeController@buy');
    // 提交订单
    Route::post('create-order', 'OrderController@createOrder');
    // 结算页
    Route::get('bill/{orderSN}', 'OrderController@bill');
    // 通过订单号详情页
    Route::get('detail-order-sn/{orderSN}', 'OrderController@detailOrderSN');
    // 订单查询页
    Route::get('order-search', 'OrderController@orderSearch');
    // 检查订单状态
    Route::get('check-order-status/{orderSN}', 'OrderController@checkOrderStatus');
    // 通过订单号查询（动态限流）
    Route::post('search-order-by-sn', 'OrderController@searchOrderBySN')->middleware('dynamic.throttle:throttle_order_search,10');
    // 通过邮箱查询（动态限流）
    Route::post('search-order-by-email', 'OrderController@searchOrderByEmail')->middleware('dynamic.throttle:throttle_email_search,10');
    // 通过浏览器查询（动态限流）
    Route::post('search-order-by-browser', 'OrderController@searchOrderByBrowser')->middleware('dynamic.throttle:throttle_browser_search,20');
});

Route::group(['middleware' => ['install.check'],'namespace' => 'Home'], function () {
    // 安装
    Route::get('install', 'HomeController@install');
    // 执行安装
    Route::post('do-install', 'HomeController@doInstall');
});

// 插件市场API接口（只保留API，删除前台页面）
Route::group(['middleware' => ['dujiaoka.boot']], function () {
    // API接口
    Route::post('api/plugin/create-order', '\App\Http\Controllers\PluginMarketController@createOrder');
    Route::get('api/plugin/check-order/{orderNo}', '\App\Http\Controllers\PluginMarketController@checkOrder');
    Route::post('api/plugin/activate', '\App\Http\Controllers\PluginMarketController@doActivate');
    Route::post('api/plugin/install/{slug}', '\App\Http\Controllers\PluginMarketController@install');
    Route::post('api/plugin/uninstall/{slug}', '\App\Http\Controllers\PluginMarketController@uninstall');
    Route::post('api/plugin/toggle/{slug}', '\App\Http\Controllers\PluginMarketController@toggle');
});

