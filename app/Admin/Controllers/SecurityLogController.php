<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\SecurityLog;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class SecurityLogController extends AdminController
{
    protected $title = '安全日志';
    
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new SecurityLog(), function (Grid $grid) {
            $grid->model()->orderBy('id', 'desc');
            
            $grid->column('id', 'ID')->sortable();
            $grid->column('type', '类型')->using(\App\Models\SecurityLog::getTypeMap())->label([
                'payment_request' => 'primary',
                'suspicious_request' => 'danger',
            ]);
            $grid->column('ip', 'IP地址');
            $grid->column('order_sn', '订单号')->limit(20);
            $grid->column('method', '请求方法')->label();
            $grid->column('url', '请求URL')->limit(50)->copyable();
            $grid->column('reason', '可疑原因')->limit(30);
            $grid->column('created_at', '时间')->sortable();
            
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('type', '类型')->select(\App\Models\SecurityLog::getTypeMap());
                $filter->like('ip', 'IP地址');
                $filter->like('order_sn', '订单号');
                $filter->between('created_at', '时间')->datetime();
            });
            
            // 禁用创建和编辑
            $grid->disableCreateButton();
            $grid->disableActions();
            
            // 启用批量删除
            $grid->enableBatchDelete();
            
            $grid->export();
        });
    }
    
    /**
     * 清理旧日志
     */
    public function clearOldLogs()
    {
        $days = dujiaoka_config_get('security_log_keep_days', 30);
        $date = now()->subDays($days);
        $count = \App\Models\SecurityLog::where('created_at', '<', $date)->delete();
        
        return response()->json([
            'status' => true,
            'message' => "已清理 {$count} 条旧日志"
        ]);
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new SecurityLog(), function (Show $show) {
            $show->field('id', 'ID');
            $show->field('type', '类型')->using(\App\Models\SecurityLog::getTypeMap());
            $show->field('ip', 'IP地址');
            $show->field('url', '请求URL');
            $show->field('method', '请求方法');
            $show->field('user_agent', 'User Agent');
            $show->field('params', '请求参数')->json();
            $show->field('order_sn', '订单号');
            $show->field('reason', '可疑原因');
            $show->field('created_at', '创建时间');
            $show->field('updated_at', '更新时间');
            
            $show->disableEditButton();
            $show->disableDeleteButton();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new SecurityLog(), function (Form $form) {
            $form->display('id');
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
