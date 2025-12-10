<div class="card">
    <div class="card-header">
        <h4>激活插件 - {{ $plugin->name }}</h4>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> 请输入您购买的授权码来激活插件
        </div>

        <form id="activateForm">
            <input type="hidden" name="plugin_id" value="{{ $plugin->id }}">
            
            <div class="form-group">
                <label>插件名称</label>
                <input type="text" class="form-control" value="{{ $plugin->name }}" readonly>
            </div>

            <div class="form-group">
                <label>当前域名</label>
                <input type="text" class="form-control" value="{{ request()->getHost() }}" readonly>
                <small class="form-text text-muted">授权码将绑定到此域名</small>
            </div>

            <div class="form-group">
                <label>授权码 <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="license_key" id="license_key" placeholder="请输入授权码" required>
                <small class="form-text text-muted">请输入您购买后收到的授权码</small>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary" id="activateBtn">
                    <i class="fa fa-check"></i> 激活并安装
                </button>
                <a href="{{ admin_url('plugins') }}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> 返回
                </a>
            </div>
        </form>

        <div id="activateResult" style="display: none;">
            <div class="alert" id="resultAlert"></div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#activateForm').on('submit', function(e) {
        e.preventDefault();
        
        const licenseKey = $('#license_key').val().trim();
        if (!licenseKey) {
            Dcat.error('请输入授权码');
            return;
        }

        const $btn = $('#activateBtn');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 激活中...');

        $.ajax({
            url: '{{ admin_url("plugins/do-activate") }}',
            method: 'POST',
            data: {
                plugin_id: {{ $plugin->id }},
                license_key: licenseKey,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.status) {
                    Dcat.success(response.message || '激活成功');
                    setTimeout(function() {
                        window.location.href = '{{ admin_url("plugins") }}';
                    }, 1500);
                } else {
                    Dcat.error(response.message || '激活失败');
                    $btn.prop('disabled', false).html('<i class="fa fa-check"></i> 激活并安装');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                Dcat.error(response?.message || '激活失败，请检查授权码是否正确');
                $btn.prop('disabled', false).html('<i class="fa fa-check"></i> 激活并安装');
            }
        });
    });
});
</script>
