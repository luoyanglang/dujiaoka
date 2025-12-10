<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePluginsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 插件表
        Schema::create('plugins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100)->comment('插件名称');
            $table->string('slug', 50)->unique()->comment('插件标识');
            $table->text('description')->nullable()->comment('插件描述');
            $table->string('version', 20)->nullable()->comment('版本号');
            $table->decimal('price', 10, 2)->default(0)->comment('价格');
            $table->boolean('is_free')->default(false)->comment('是否免费');
            $table->string('author', 50)->nullable()->comment('作者');
            $table->string('download_url')->nullable()->comment('下载地址');
            $table->string('icon')->nullable()->comment('图标');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('状态');
            $table->timestamps();
            
            $table->index('slug');
            $table->index('status');
        });

        // 已安装插件表
        Schema::create('installed_plugins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('plugin_slug', 50)->comment('插件标识');
            $table->string('license_key')->nullable()->comment('授权码（付费插件）');
            $table->string('domain', 100)->nullable()->comment('绑定域名');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('状态');
            $table->timestamp('activated_at')->nullable()->comment('激活时间');
            $table->timestamp('expire_at')->nullable()->comment('过期时间');
            $table->timestamps();
            
            $table->index('plugin_slug');
            $table->index('license_key');
            $table->index('domain');
        });

        // 插件市场配置表
        Schema::create('plugin_market_config', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key', 50)->unique()->comment('配置键');
            $table->text('value')->nullable()->comment('配置值');
            $table->timestamps();
        });

        // 初始化插件市场标记
        DB::table('plugin_market_config')->insert([
            ['key' => 'market_installed', 'value' => 'true', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'market_version', 'value' => '1.0.0', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('installed_plugins');
        Schema::dropIfExists('plugins');
        Schema::dropIfExists('plugin_market_config');
    }
}
