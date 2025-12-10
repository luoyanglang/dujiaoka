<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateSecurityLogMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 更新安全日志菜单标题
        DB::table('admin_menu')
            ->where('uri', 'security-log')
            ->orWhere('uri', '/security-log')
            ->update(['title' => '安全日志']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('admin_menu')
            ->where('uri', 'security-log')
            ->orWhere('uri', '/security-log')
            ->update(['title' => 'Security_Log']);
    }
}
