<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityLog extends Model
{
    protected $table = 'security_logs';

    protected $fillable = [
        'type',
        'ip',
        'url',
        'method',
        'user_agent',
        'params',
        'order_sn',
        'reason',
    ];

    const TYPE_PAYMENT_REQUEST = 'payment_request';
    const TYPE_SUSPICIOUS_REQUEST = 'suspicious_request';

    /**
     * 类型映射
     */
    public static function getTypeMap()
    {
        return [
            self::TYPE_PAYMENT_REQUEST => '支付请求',
            self::TYPE_SUSPICIOUS_REQUEST => '可疑请求',
        ];
    }
}
