<?php

namespace App\Http\Middleware;

use App\Models\SecurityLog;
use Closure;
use Illuminate\Support\Facades\Log;

class PayGateWay
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $orderSN = $request->route('orderSN');
        
        // 记录支付请求日志到数据库
        try {
            SecurityLog::create([
                'type' => SecurityLog::TYPE_PAYMENT_REQUEST,
                'ip' => $request->getClientIp(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_agent' => $request->userAgent(),
                'params' => json_encode($request->all()),
                'order_sn' => $orderSN,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log payment request', ['error' => $e->getMessage()]);
        }

        // 基础安全检查
        if ($orderSN && !$this->isValidOrderSN($orderSN)) {
            // 记录可疑请求
            try {
                SecurityLog::create([
                    'type' => SecurityLog::TYPE_SUSPICIOUS_REQUEST,
                    'ip' => $request->getClientIp(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'user_agent' => $request->userAgent(),
                    'params' => json_encode($request->all()),
                    'order_sn' => $orderSN,
                    'reason' => '订单号格式无效',
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to log suspicious request', ['error' => $e->getMessage()]);
            }
            
            abort(400, 'Invalid order format');
        }

        return $next($request);
    }

    /**
     * 验证订单号格式
     *
     * @param string $orderSN
     * @return bool
     */
    private function isValidOrderSN(string $orderSN): bool
    {
        // 订单号应该是字母数字组合，长度合理
        return preg_match('/^[A-Za-z0-9]{10,50}$/', $orderSN);
    }
}
