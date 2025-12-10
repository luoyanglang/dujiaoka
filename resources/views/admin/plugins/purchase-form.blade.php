<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

<div class="card">
    <div class="card-body">
        <form id="purchase-form">
            @csrf
            <input type="hidden" name="plugin_id" value="{{ $plugin->id }}">
            
            <div class="alert alert-info">
                <h5>{{ $plugin->name }}</h5>
                <p class="mb-0">{{ $plugin->description }}</p>
                <h4 class="mt-2 mb-0 text-primary">¥{{ number_format($plugin->price, 2) }}</h4>
            </div>
            
            <div class="form-group">
                <label>接收授权码的邮箱 <span class="text-danger">*</span></label>
                <input type="email" class="form-control" name="email" required placeholder="请输入邮箱">
            </div>
            
            <div class="form-group">
                <label>币种 <span class="text-danger">*</span></label>
                <select class="form-control" name="currency">
                    <option value="USDT_TRC20">USDT (TRC20)</option>
                    <option value="TRX">TRX</option>
                </select>
            </div>
            
            <button type="button" class="btn btn-primary" id="submit-btn">立即购买</button>
        </form>
    </div>
</div>

<div id="payment-area" style="display:none;" class="card mt-3">
    <div class="card-body text-center">
        <h5 id="order-no"></h5>
        <div id="qrcode" style="display:inline-block;"></div>
        <div id="amount" class="mt-3"></div>
        <div class="mt-3">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="manualCheck()">刷新订单状态</button>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#submit-btn').on('click', function() {
        var btn = $(this);
        var email = $('input[name="email"]').val();
        if (!email) {
            alert('请输入邮箱');
            return;
        }
        btn.prop('disabled', true).text('创建中...');
        $.ajax({
            url: '{{ admin_url("plugins") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                plugin_id: {{ $plugin->id }},
                email: email,
                currency: $('select[name="currency"]').val()
            },
            success: function(res) {
                if (res.status) {
                    $('#purchase-form').hide();
                    $('#order-no').text('订单号：' + res.data.order_no);
                    
                    // 清空二维码区域
                    $('#qrcode').empty();
                    
                    // 生成二维码
                    new QRCode(document.getElementById('qrcode'), {
                        text: res.data.pay_address,
                        width: 256,
                        height: 256,
                        colorDark: '#000000',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.H
                    });
                    
                    // 显示支付信息
                    var payInfo = '<p><strong>支付金额：</strong>' + res.data.pay_amount + ' ' + res.data.currency + '</p>';
                    payInfo += '<p><strong>收款地址：</strong></p>';
                    payInfo += '<div class="input-group" style="max-width:500px;margin:0 auto;">';
                    payInfo += '<input type="text" class="form-control" id="pay-address" value="' + res.data.pay_address + '" readonly>';
                    payInfo += '<div class="input-group-append">';
                    payInfo += '<button class="btn btn-primary" type="button" onclick="copyAddress()">复制</button>';
                    payInfo += '</div></div>';
                    $('#amount').html(payInfo);
                    
                    $('#payment-area').show();
                    currentOrderNo = res.data.order_no;
                    checkOrder(res.data.order_no);
                } else {
                    alert(res.message || '创建订单失败');
                    btn.prop('disabled', false).text('立即购买');
                }
            },
            error: function() {
                alert('网络错误');
                btn.prop('disabled', false).text('立即购买');
            }
        });
    });
});

function checkOrder(orderNo) {
    var checkCount = 0;
    var timer = setInterval(function() {
        checkCount++;
        console.log('检查订单状态 #' + checkCount + ':', orderNo);
        
        $.ajax({
            url: '/api/plugin/check-order/' + orderNo,
            type: 'GET',
            success: function(result) {
                console.log('返回结果:', result);
                
                if (result.success && result.data && result.data.status === 'paid') {
                    clearInterval(timer);
                    
                    // 隐藏二维码和支付信息
                    $('#qrcode').hide();
                    $('#amount').hide();
                    
                    // 显示成功信息
                    var licenseKey = result.data.license_key || '';
                    var successHtml = '<div class="alert alert-success">';
                    successHtml += '<h4>✓ 支付成功！</h4>';
                    successHtml += '<p>授权码已发送到您的邮箱</p>';
                    if (licenseKey) {
                        successHtml += '<div class="mt-3">';
                        successHtml += '<p><strong>授权码：</strong></p>';
                        successHtml += '<div class="input-group" style="max-width:500px;margin:0 auto;">';
                        successHtml += '<input type="text" class="form-control" id="license-key" value="' + licenseKey + '" readonly>';
                        successHtml += '<div class="input-group-append">';
                        successHtml += '<button class="btn btn-success" type="button" onclick="copyLicense()">复制授权码</button>';
                        successHtml += '</div></div></div>';
                    }
                    successHtml += '<p class="mt-3"><a href="{{ admin_url("plugins") }}" class="btn btn-primary">返回插件列表</a></p>';
                    successHtml += '</div>';
                    
                    $('#order-no').after(successHtml);
                }
            },
            error: function(xhr) {
                console.error('查询失败:', xhr.status);
            }
        });
    }, 10000);
    
    // 5分钟后停止
    setTimeout(function() {
        clearInterval(timer);
        console.log('停止轮询');
    }, 300000);
}

function copyLicense() {
    var input = document.getElementById('license-key');
    input.select();
    document.execCommand('copy');
    alert('授权码已复制到剪贴板');
}

var currentOrderNo = '';

function manualCheck() {
    if (!currentOrderNo) {
        alert('没有订单号');
        return;
    }
    $.ajax({
        url: '/api/plugin/check-order/' + currentOrderNo,
        type: 'GET',
        success: function(res) {
            console.log('手动查询结果:', res);
            if (res.success && res.data && res.data.status === 'paid') {
                alert('订单已支付！页面即将刷新');
                location.reload();
            } else {
                alert('订单状态：' + (res.data ? res.data.status : '未知'));
            }
        },
        error: function(xhr) {
            alert('查询失败：' + xhr.status);
        }
    });
}

function copyAddress() {
    var input = document.getElementById('pay-address');
    input.select();
    document.execCommand('copy');
    alert('地址已复制到剪贴板');
}
</script>
