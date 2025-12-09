<?php

namespace App\Http\Controllers;

use App\Models\Plugin;
use App\Models\InstalledPlugin;
use App\Service\PluginLicenseService;
use App\Service\PluginInstallService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PluginMarketController extends Controller
{
    private $licenseService;
    private $installService;

    public function __construct(PluginLicenseService $licenseService, PluginInstallService $installService)
    {
        $this->licenseService = $licenseService;
        $this->installService = $installService;
    }



    /**
     * 创建订单（API代理）
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'feature' => 'required|string',
            'payment_method' => 'required|in:tokenpay,wechat,alipay',
            'currency' => 'nullable|in:USDT_TRC20,TRX',
        ]);

        $result = $this->licenseService->createOrder(
            $request->email,
            $request->feature,
            $request->payment_method,
            $request->currency
        );

        // 如果创建成功，将订单数据存入 session（包含支付二维码等信息）
        if (isset($result['success']) && $result['success'] && isset($result['data'])) {
            $orderNo = $result['data']['order_no'] ?? null;
            if ($orderNo) {
                session(['order_' . $orderNo => $result['data']]);
            }
        }

        return response()->json($result);
    }

    /**
     * 查询订单状态（API代理）
     */
    public function checkOrder($orderNo)
    {
        $result = $this->licenseService->checkOrderStatus($orderNo);
        return response()->json($result);
    }



    /**
     * 激活授权码
     */
    public function doActivate(Request $request)
    {
        $request->validate([
            'plugin_slug' => 'required|string',
            'license_key' => 'required|string',
        ]);

        $plugin = Plugin::where('slug', $request->plugin_slug)->firstOrFail();
        $domain = $request->getHost();

        // 调用授权系统激活
        $result = $this->licenseService->activateLicense($request->license_key, $domain);

        if ($result['success'] ?? false) {
            // 保存到数据库
            InstalledPlugin::updateOrCreate(
                ['plugin_slug' => $request->plugin_slug],
                [
                    'license_key' => $request->license_key,
                    'domain' => $domain,
                    'status' => 'active',
                    'activated_at' => now(),
                    'expire_at' => isset($result['data']['expire_date']) ? $result['data']['expire_date'] : null,
                ]
            );

            // 下载并安装插件
            if ($plugin->download_url) {
                $downloadResult = $this->installService->downloadPlugin($plugin->download_url);
                if ($downloadResult['success']) {
                    $this->installService->installPlugin($plugin->slug, $downloadResult['file_path']);
                }
            }

            return response()->json([
                'success' => true,
                'message' => '激活成功'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? '激活失败'
        ]);
    }

    /**
     * 安装插件（免费插件直接安装）
     */
    public function install($slug)
    {
        $plugin = Plugin::where('slug', $slug)->firstOrFail();

        // 免费插件直接安装
        if ($plugin->is_free) {
            if ($plugin->download_url) {
                $downloadResult = $this->installService->downloadPlugin($plugin->download_url);
                if ($downloadResult['success']) {
                    $installResult = $this->installService->installPlugin($plugin->slug, $downloadResult['file_path']);
                    
                    if ($installResult['success']) {
                        // 记录安装信息
                        InstalledPlugin::create([
                            'plugin_slug' => $plugin->slug,
                            'status' => 'active',
                            'activated_at' => now(),
                        ]);

                        return response()->json([
                            'success' => true,
                            'message' => '安装成功'
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'success' => false,
            'message' => '安装失败'
        ]);
    }

    /**
     * 卸载插件
     */
    public function uninstall($slug)
    {
        $installed = InstalledPlugin::where('plugin_slug', $slug)->first();
        
        if ($installed) {
            // 删除插件文件
            $this->installService->uninstallPlugin($slug);
            
            // 删除数据库记录
            $installed->delete();

            return response()->json([
                'success' => true,
                'message' => '卸载成功'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => '插件未安装'
        ]);
    }

    /**
     * 启用/禁用插件
     */
    public function toggle($slug)
    {
        $installed = InstalledPlugin::where('plugin_slug', $slug)->first();
        
        if ($installed) {
            if ($installed->status === 'active') {
                $installed->disable();
                $this->installService->disablePlugin($slug);
                $message = '已禁用';
            } else {
                $installed->enable();
                $this->installService->enablePlugin($slug);
                $message = '已启用';
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => '插件未安装'
        ]);
    }

}
