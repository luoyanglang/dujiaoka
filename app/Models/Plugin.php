<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    protected $table = 'plugins';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'version',
        'price',
        'is_free',
        'author',
        'download_url',
        'icon',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_free' => 'boolean',
    ];

    /**
     * 检查插件是否已安装
     */
    public function isInstalled()
    {
        return InstalledPlugin::where('plugin_slug', $this->slug)->exists();
    }

    /**
     * 检查是否免费插件
     */
    public function isFree()
    {
        return $this->is_free == true;
    }

    /**
     * 检查是否激活状态
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * 获取已安装信息
     */
    public function installedInfo()
    {
        return $this->hasOne(InstalledPlugin::class, 'plugin_slug', 'slug');
    }

    /**
     * 格式化价格显示
     */
    public function getPriceFormatAttribute()
    {
        if ($this->is_free) {
            return '免费';
        }
        return '¥' . number_format($this->price, 2);
    }
}
