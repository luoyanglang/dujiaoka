<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class InstalledPlugin extends Model
{
    protected $table = 'installed_plugins';

    protected $fillable = [
        'plugin_slug',
        'license_key',
        'domain',
        'status',
        'activated_at',
        'expire_at',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'expire_at' => 'datetime',
    ];

    /**
     * 关联插件信息
     */
    public function plugin()
    {
        return $this->belongsTo(Plugin::class, 'plugin_slug', 'slug');
    }

    /**
     * 检查是否激活状态
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * 检查是否已过期
     */
    public function isExpired()
    {
        if (!$this->expire_at) {
            return false; // 永久授权
        }
        return Carbon::now()->gt($this->expire_at);
    }

    /**
     * 获取剩余天数
     */
    public function getRemainingDaysAttribute()
    {
        if (!$this->expire_at) {
            return '永久';
        }
        
        $days = Carbon::now()->diffInDays($this->expire_at, false);
        
        if ($days < 0) {
            return '已过期';
        }
        
        return $days . ' 天';
    }

    /**
     * 获取状态文本
     */
    public function getStatusTextAttribute()
    {
        $statusMap = [
            'active' => '已激活',
            'inactive' => '未激活',
        ];
        
        return $statusMap[$this->status] ?? '未知';
    }

    /**
     * 启用插件
     */
    public function enable()
    {
        $this->status = 'active';
        return $this->save();
    }

    /**
     * 禁用插件
     */
    public function disable()
    {
        $this->status = 'inactive';
        return $this->save();
    }
}
