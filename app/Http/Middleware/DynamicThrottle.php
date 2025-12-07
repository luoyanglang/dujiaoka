<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Routing\Middleware\ThrottleRequests;

class DynamicThrottle extends ThrottleRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $configKey  配置键名
     * @param  int  $defaultLimit  默认限制次数
     * @return mixed
     */
    public function handle($request, Closure $next, $configKey = 'throttle_order_search', $defaultLimit = 10)
    {
        // 从配置中获取限流次数
        $maxAttempts = dujiaoka_config_get($configKey, $defaultLimit);
        $decayMinutes = 1;

        return parent::handle($request, $next, $maxAttempts, $decayMinutes);
    }
}
