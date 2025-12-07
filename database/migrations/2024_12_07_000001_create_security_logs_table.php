<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecurityLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('security_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->comment('日志类型：payment_request, suspicious_request');
            $table->string('ip', 45)->index()->comment('IP地址');
            $table->string('url', 500)->comment('请求URL');
            $table->string('method', 10)->comment('请求方法');
            $table->text('user_agent')->nullable()->comment('User Agent');
            $table->text('params')->nullable()->comment('请求参数');
            $table->string('order_sn', 50)->nullable()->index()->comment('订单号');
            $table->string('reason', 200)->nullable()->comment('可疑原因');
            $table->timestamps();
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('security_logs');
    }
}
