<?php

namespace App\Service;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class PluginLicenseService
{
    private $apiUrl;
    private $apiSecret;
    private $client;

    public function __construct()
    {
        $this->apiUrl = env('LICENSE_API_URL', 'http://localhost:5000');
        $this->apiSecret = env('LICENSE_API_SECRET', '');
        $this->client = new Client([
            'timeout' => 10,
            'verify' => false, // 忽略 SSL 验证（生产环境建议开启）
        ]);
    }

    /**
     * 获取插件列表（从授权系统）
     */
    public function getPluginList()
    {
        try {
            $response = $this->client->get($this->apiUrl . '/api/public/features');
            
            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody()->getContents(), true);
                if ($data['success'] ?? false) {
                    return [
                        'success' => true,
                        'data' => $data['data'] ?? []
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => '获取插件列表失败'
            ];
        } catch (\Exception $e) {
            Log::error('获取插件列表失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => '网络请求失败: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 创建订单
     * 
     * @param array $data 订单数据
     */
    public function createOrder($data)
    {
        try {
            $params = [
                'email' => $data['email'],
                'feature' => $data['feature'],
                'payment_method' => $data['payment_method'],
                'program_id' => 3, // 独角卡数插件对应程序ID=3
            ];

            // TokenPay 需要币种参数
            if ($data['payment_method'] === 'tokenpay' && isset($data['currency'])) {
                $params['currency'] = $data['currency'];
            }

            $response = $this->client->post($this->apiUrl . '/api/public/create-order', [
                'json' => $params,
                'timeout' => 30,
            ]);
            
            if ($response->getStatusCode() === 200 || $response->getStatusCode() === 201) {
                $data = json_decode($response->getBody()->getContents(), true);
                return $data;
            }
            
            return [
                'success' => false,
                'message' => '创建订单失败: ' . $response->getBody()->getContents()
            ];
        } catch (\Exception $e) {
            Log::error('创建订单失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => '网络请求失败: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 查询订单状态
     */
    public function checkOrderStatus($orderNo)
    {
        try {
            $response = $this->client->get($this->apiUrl . '/api/public/order-status/' . $orderNo);
            
            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody()->getContents(), true);
                return $data;
            }
            
            return [
                'success' => false,
                'message' => '查询订单失败'
            ];
        } catch (\Exception $e) {
            Log::error('查询订单失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => '网络请求失败: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 生成机器码（基于服务器信息）
     */
    private function generateMachineCode()
    {
        // 获取服务器唯一标识
        $serverInfo = [
            php_uname('n'), // 主机名
            php_uname('s'), // 操作系统
            $_SERVER['SERVER_ADDR'] ?? '', // 服务器IP
            $_SERVER['DOCUMENT_ROOT'] ?? '', // 文档根目录
        ];
        
        return md5(implode('|', $serverInfo));
    }

    /**
     * 激活授权码
     * 
     * @param string $licenseKey 授权码
     * @param string $domain 域名
     * @param string $pluginName 插件名称（可选，如果不传则激活授权码对应的所有功能）
     */
    public function activateLicense($licenseKey, $domain, $pluginName = null)
    {
        try {
            $machineCode = $this->generateMachineCode();
            
            $params = [
                'license_key' => $licenseKey,
                'domain' => $domain,
                'machine_code' => $machineCode,
                'machine_name' => php_uname('n'),
                'os_info' => php_uname('s') . ' ' . php_uname('r'),
            ];
            
            // 如果指定了插件名称，添加到参数中
            if ($pluginName) {
                $params['plugin_name'] = $pluginName;
            }
            
            $response = $this->client->post($this->apiUrl . '/api/licenses/activate', [
                'json' => $params,
                'http_errors' => false, // 不抛出 HTTP 错误异常
            ]);
            
            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);
            
            if ($statusCode === 200) {
                return $data;
            }
            
            // 返回授权系统的错误信息
            return [
                'success' => false,
                'message' => $data['message'] ?? '激活失败'
            ];
        } catch (\Exception $e) {
            Log::error('激活授权码失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => '网络请求失败: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 验证授权码
     * 
     * @param string $licenseKey 授权码
     * @param string $domain 域名
     * @param string $pluginName 插件名称（必须，用于验证是否有该插件的权限）
     */
    public function verifyLicense($licenseKey, $domain, $pluginName)
    {
        try {
            $response = $this->client->post($this->apiUrl . '/api/licenses/verify', [
                'json' => [
                    'license_key' => $licenseKey,
                    'machine_code' => $this->generateMachineCode(),
                    'domain' => $domain,
                    'plugin_name' => $pluginName, // 必须传插件名称
                ],
            ]);
            
            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody()->getContents(), true);
                return $data;
            }
            
            return [
                'success' => false,
                'message' => '验证失败'
            ];
        } catch (\Exception $e) {
            Log::error('验证授权码失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => '网络请求失败: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 获取授权码的插件列表
     * 
     * @param string $licenseKey 授权码
     * @param string $domain 域名
     */
    public function getLicensePlugins($licenseKey, $domain)
    {
        try {
            $response = $this->client->post($this->apiUrl . '/api/licenses/plugins', [
                'json' => [
                    'license_key' => $licenseKey,
                    'machine_code' => $this->generateMachineCode(),
                    'domain' => $domain,
                ],
            ]);
            
            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody()->getContents(), true);
                return $data;
            }
            
            return [
                'success' => false,
                'message' => '获取插件列表失败'
            ];
        } catch (\Exception $e) {
            Log::error('获取授权插件列表失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => '网络请求失败: ' . $e->getMessage()
            ];
        }
    }
}
