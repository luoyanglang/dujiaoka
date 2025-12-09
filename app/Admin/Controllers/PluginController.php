<?php

namespace App\Admin\Controllers;

use App\Models\Plugin;
use App\Models\InstalledPlugin;
use App\Service\PluginLicenseService;
use App\Service\PluginInstallService;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Layout\Content;
use Illuminate\Http\Request;

class PluginController extends AdminController
{
    protected $title = '插件管理';
    
    private $licenseService;
    private $installService;

    public function __construct(PluginLicenseService $licenseService, PluginInstallService $installService)
    {
        $this->licenseService = $licenseService;
        $this->installService = $installService;
    }

    /**
     * 插件列表
     */
    public function index(Content $content)
    {
        return $content
            ->title('插件管理')
            ->description('管理系统插件')
            ->body($this->grid());
    }

    /**
     * 插件详情
     */
    public function show($id, Content $content)
    {
        return $content
            ->title('插件详情')
            ->description('查看插件信息')
            ->body($this->detail($id));
    }

    /**
     * 表格
     */
    protected function grid()
    {
        // 处理筛选参数
        $query = Plugin::query();
        
        // 按类型筛选
        if (request()->has('is_free') && request('is_free') !== '') {
            $query->where('is_free', request('is_free'));
        }
        
        // 按名称搜索
        if (request()->has('name') && request('name') !== '') {
            $query->where('name', 'like', '%' . request('name') . '%');
        }
        
        return Grid::make($query, function (Grid $grid) {
            $grid->column('id', 'ID')->sortable();
            $grid->column('name', '插件名称');
            $grid->column('slug', '插件标识');
            $grid->column('version', '版本');
            $grid->column('price', '价格')->display(function ($price) {
                if ($this->is_free) {
                    return '<span class="badge badge-success">免费</span>';
                }
                return '¥' . number_format($price, 2);
            });
            $grid->column('author', '作者');
            $grid->column('status', '状态')->display(function () {
                // 检查是否已安装
                $installed = \App\Models\InstalledPlugin::where('plugin_slug', $this->slug)->first();
                
                if ($installed) {
                    if ($installed->status === 'active') {
                        return '<span class="badge badge-success">已安装</span>';
                    }
                    return '<span class="badge badge-warning">已禁用</span>';
                }
                return '<span class="badge badge-secondary">未安装</span>';
            });
            $grid->column('created_at', '创建时间')->sortable();

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableEdit();
                $actions->disableDelete();
                $actions->disableView();
                
                // 获取当前行数据
                $id = $this->id;
                $slug = $this->slug;
                $isFree = $this->is_free;
                
                // 检查是否已安装
                $installed = \App\Models\InstalledPlugin::where('plugin_slug', $slug)->first();
                
                if ($installed) {
                    // 已安装：显示启用/禁用和卸载按钮
                    if ($installed->status === 'active') {
                        $actions->append('<a href="' . admin_url('plugins/' . $id . '/disable') . '" class="btn btn-sm btn-warning">禁用</a>');
                    } else {
                        $actions->append('<a href="' . admin_url('plugins/' . $id . '/enable') . '" class="btn btn-sm btn-success">启用</a>');
                    }
                    $actions->append('<a href="' . admin_url('plugins/' . $id . '/uninstall') . '" class="btn btn-sm btn-danger" onclick="return confirm(\'确定要卸载此插件吗？\')">卸载</a>');
                } else {
                    // 未安装：显示安装、购买或激活按钮
                    if ($isFree) {
                        $actions->append('<a href="' . admin_url('plugins/' . $id . '/install') . '" class="btn btn-sm btn-success">安装</a>');
                    } else {
                        // 付费插件：显示购买和激活按钮
                        $actions->append('<a href="' . admin_url('plugins/' . $id . '/purchase') . '" class="btn btn-sm btn-outline-primary">购买</a>');
                        $actions->append('<a href="' . admin_url('plugins/' . $id . '/activate') . '" class="btn btn-sm btn-outline-primary">激活</a>');
                    }
                }
            });

            $grid->disableCreateButton();
            $grid->disableBatchDelete();

            $grid->tools(function (Grid\Tools $tools) {
                $tools->append('<a href="' . admin_url('plugins/sync') . '" class="btn btn-primary btn-sm">同步插件列表</a>');
                
                // 自定义筛选工具栏
                $tools->append('
                    <div class="btn-group" style="margin-left: 10px;">
                        <select class="form-control form-control-sm" id="plugin-type-filter" style="width: 90px; height: 32px; line-height: 20px; display: inline-block; padding: 6px 25px 6px 8px;">
                            <option value="">全部</option>
                            <option value="1">免费</option>
                            <option value="0">付费</option>
                        </select>
                        <input type="text" class="form-control form-control-sm" id="plugin-name-search" placeholder="搜索插件名称" style="width: 180px; height: 32px; line-height: 20px; display: inline-block; margin-left: 5px; padding: 6px 8px;">
                        <button class="btn btn-sm btn-primary" id="plugin-filter-btn" style="margin-left: 5px;">筛选</button>
                        <button class="btn btn-sm btn-default" id="plugin-reset-btn" style="margin-left: 5px;">重置</button>
                    </div>
                    <script>
                    $(function() {
                        $("#plugin-filter-btn").click(function() {
                            var type = $("#plugin-type-filter").val();
                            var name = $("#plugin-name-search").val();
                            var url = "' . admin_url('plugins') . '?";
                            if (type !== "") {
                                url += "is_free=" + type + "&";
                            }
                            if (name !== "") {
                                url += "name=" + encodeURIComponent(name);
                            }
                            window.location.href = url;
                        });
                        
                        $("#plugin-reset-btn").click(function() {
                            window.location.href = "' . admin_url('plugins') . '";
                        });
                        
                        // 回车触发筛选
                        $("#plugin-name-search").keypress(function(e) {
                            if (e.which == 13) {
                                $("#plugin-filter-btn").click();
                            }
                        });
                    });
                    </script>
                ');
            });
        });
    }

    /**
     * 详情
     */
    protected function detail($id)
    {
        return Show::make($id, new Plugin(), function (Show $show) {
            $show->field('id', 'ID');
            $show->field('name', '插件名称');
            $show->field('slug', '插件标识');
            $show->field('description', '插件描述');
            $show->field('version', '版本');
            $show->field('price', '价格')->as(function ($price) {
                return $this->is_free ? '免费' : '¥' . number_format($price, 2);
            });
            $show->field('author', '作者');
            $show->field('download_url', '下载地址');
            $show->field('status', '状态')->using(['active' => '启用', 'inactive' => '禁用']);
            $show->field('created_at', '创建时间');
            $show->field('updated_at', '更新时间');

            $show->panel()->tools(function ($tools) {
                $tools->disableEdit();
                $tools->disableDelete();
            });
        });
    }

    /**
     * 同步插件列表（从授权系统）
     */
    public function sync()
    {
        try {
            $result = $this->licenseService->getPluginList();

            if ($result['success'] ?? false) {
                $features = $result['data'];
                $syncCount = 0;
                $activeSlugs = []; // 记录所有激活的插件标识

                foreach ($features as $feature) {
                    // 使用授权系统的 unique_code 作为 slug
                    $slug = $feature['unique_code'] ?? 'plugin-' . $feature['id'];
                    $featureStatus = $feature['status'] ?? 'active';
                    
                    // 记录激活的插件
                    if ($featureStatus === 'active') {
                        $activeSlugs[] = $slug;
                    }
                    
                    Plugin::updateOrCreate(
                        ['slug' => $slug],
                        [
                            'name' => $feature['name'],
                            'description' => $feature['description'] ?? '',
                            'version' => $feature['version'] ?? '1.0.0',
                            'price' => $feature['price'] ?? 0,
                            'is_free' => ($feature['price'] ?? 0) == 0,
                            'author' => $feature['author'] ?? '未知',
                            'download_url' => $feature['download_url'] ?? null,
                            'status' => $featureStatus,
                        ]
                    );
                    $syncCount++;
                }
                
                // 删除授权系统中不存在或已下架的插件（只删除未安装的）
                $deletedCount = Plugin::whereNotIn('slug', $activeSlugs)
                    ->whereDoesntHave('installedInfo') // 只删除未安装的
                    ->delete();

                $message = "成功同步 {$syncCount} 个插件";
                if ($deletedCount > 0) {
                    $message .= "，清理 {$deletedCount} 个已下架插件";
                }
                
                return redirect(admin_url('plugins'))->with('success', $message);
            }

            return redirect(admin_url('plugins'))->with('error', '同步失败: ' . ($result['message'] ?? '未知错误'));
        } catch (\Exception $e) {
            return redirect(admin_url('plugins'))->with('error', '同步失败: ' . $e->getMessage());
        }
    }

    /**
     * 安装插件
     */
    public function install($id)
    {
        $plugin = Plugin::findOrFail($id);
        
        if (!$plugin->is_free) {
            return redirect()->back()->with('error', '付费插件需要先购买授权');
        }

        // 检查是否已安装
        $installed = InstalledPlugin::where('plugin_slug', $plugin->slug)->first();
        if ($installed) {
            return redirect()->back()->with('error', '插件已安装');
        }

        // 免费插件需要有下载地址
        if (!$plugin->download_url) {
            return redirect()->back()->with('error', '插件下载地址未配置');
        }

        $result = $this->installService->install($plugin->slug, $plugin->download_url);

        if ($result['success']) {
            return redirect('admin/plugins')->with('success', '插件安装成功');
        }

        return redirect()->back()->with('error', $result['message'] ?? '安装失败');
    }

    /**
     * 卸载插件
     */
    public function uninstall($id)
    {
        $plugin = Plugin::findOrFail($id);
        $result = $this->installService->uninstall($plugin->slug);

        if ($result['success']) {
            return redirect('admin/plugins')->with('success', '插件卸载成功');
        }

        return redirect()->back()->with('error', $result['message'] ?? '卸载失败');
    }

    /**
     * 启用插件
     */
    public function enable($id)
    {
        $plugin = Plugin::findOrFail($id);
        $installed = InstalledPlugin::where('plugin_slug', $plugin->slug)->first();

        if (!$installed) {
            return redirect()->back()->with('error', '插件未安装');
        }

        $installed->update(['status' => 'active']);

        return redirect('admin/plugins')->with('success', '插件已启用');
    }

    /**
     * 禁用插件
     */
    public function disable($id)
    {
        $plugin = Plugin::findOrFail($id);
        $installed = InstalledPlugin::where('plugin_slug', $plugin->slug)->first();

        if (!$installed) {
            return redirect()->back()->with('error', '插件未安装');
        }

        $installed->update(['status' => 'inactive']);

        return redirect('admin/plugins')->with('success', '插件已禁用');
    }

    /**
     * 购买页面
     */
    public function purchase($id, Content $content)
    {
        $plugin = Plugin::findOrFail($id);
        
        return $content
            ->title('购买插件 - ' . $plugin->name)
            ->body(view('admin.plugins.purchase-form', compact('plugin')));
    }

    /**
     * 处理购买请求（使用store方法，Dcat Admin会自动路由）
     */
    public function store()
    {
        $request = request();
        
        $request->validate([
            'plugin_id' => 'required|exists:plugins,id',
            'email' => 'required|email',
            'currency' => 'required|in:USDT_TRC20,TRX',
        ]);

        $plugin = Plugin::findOrFail($request->plugin_id);

        // 调用授权系统创建订单
        $result = $this->licenseService->createOrder([
            'feature' => $plugin->name,
            'email' => $request->email,
            'payment_method' => 'tokenpay',
            'currency' => $request->currency,
        ]);

        if ($result['success'] ?? false) {
            return response()->json([
                'status' => true,
                'message' => '订单创建成功',
                'data' => $result['data']
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => $result['message'] ?? '创建订单失败'
        ], 400);
    }
    
    /**
     * 检查订单状态（AJAX）- 通过URL参数接收订单号
     */
    public function checkOrder()
    {
        $orderNo = request('order_no');
        if (!$orderNo) {
            return response()->json([
                'success' => false,
                'message' => '订单号不能为空'
            ], 400);
        }
        
        $result = $this->licenseService->checkOrderStatus($orderNo);
        return response()->json($result);
    }

    /**
     * 激活页面
     */
    public function activate($id, Content $content)
    {
        $plugin = Plugin::findOrFail($id);
        
        return $content
            ->title('激活插件 - ' . $plugin->name)
            ->body(view('admin.plugins.activate-form', compact('plugin')));
    }

    /**
     * 处理激活请求（AJAX）
     */
    public function doActivate(Request $request)
    {
        $request->validate([
            'plugin_id' => 'required|exists:plugins,id',
            'license_key' => 'required|string',
        ]);

        $plugin = Plugin::findOrFail($request->plugin_id);
        $domain = $request->getHost();

        // 验证授权码格式（授权码格式：PROG0003-2ED8B95F-92648DDF-F46A）
        if (!preg_match('/^PROG\d{4}-[A-F0-9]{8}-[A-F0-9]{8}-[A-F0-9]{4}$/', $request->license_key)) {
            return response()->json([
                'status' => false,
                'message' => '授权码格式不正确'
            ], 400);
        }

        // 检查是否已安装
        $installed = InstalledPlugin::where('plugin_slug', $plugin->slug)->first();
        if ($installed) {
            return response()->json([
                'status' => false,
                'message' => '插件已安装，无需重复激活'
            ], 400);
        }

        // 步骤1：调用授权系统验证授权码并绑定域名（传递插件的唯一识别码）
        $activateResult = $this->licenseService->activateLicense(
            $request->license_key, 
            $domain,
            $plugin->slug  // 传递插件的唯一识别码（unique_code）
        );
        
        if (!($activateResult['success'] ?? false)) {
            // 直接返回授权系统的错误信息，不添加前缀
            return response()->json([
                'status' => false,
                'message' => $activateResult['message'] ?? '授权验证失败'
            ], 400);
        }
        
        // 验证授权码是否包含该插件的权限
        $licenseData = $activateResult['data'] ?? [];
        $features = $licenseData['features'] ?? [];
        
        // features 是字符串数组，存储的是功能的唯一识别码（unique_code）
        // 例如：["tokenpay-payment", "会员系统", "分销系统"]
        if (!in_array($plugin->slug, $features)) {
            return response()->json([
                'status' => false,
                'message' => "您没有购买【{$plugin->name}】插件，无法激活。请先购买【{$plugin->name}】后再进行激活。"
            ], 403);
        }

        // 步骤2：下载并安装插件
        if (!$plugin->download_url) {
            return response()->json([
                'status' => false,
                'message' => '插件下载地址未配置'
            ], 400);
        }

        $installResult = $this->installService->install(
            $plugin->slug, 
            $plugin->download_url, 
            $request->license_key
        );

        if ($installResult['success']) {
            return response()->json([
                'status' => true,
                'message' => '激活并安装成功'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => '安装失败: ' . ($installResult['message'] ?? '未知错误')
        ], 400);
    }
}
