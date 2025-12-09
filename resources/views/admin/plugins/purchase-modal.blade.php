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
        <div id="qrcode"></div>
        <p id="amount"></p>
    </div>
</div>

<script>
$('#submit-btn').click(function() {
    var btn = $(this);
    var email = $('[name=email]').val();
    if (!email) {
        Dcat.error('请输入邮箱');
        return;
    }
    btn.prop('disabled', true).text('创建中...');
    $.post('{{ admin_url("plugins/submit-purchase") }}', {
        _token: '{{ csrf_token() }}',
        plugin_id: {{ $plugin->id }},
        email: email,
        currency: $('[name=currency]').val()
    }, function(res) {
        if (res.status) {
            $('#purchase-form').hide();
            $('#order-no').text('订单号：' + res.data.order_no);
            $('#qrcode').html('<img src="' + res.data.qrcode_url + '" style="width:250px">');
            $('#amount').text('支付金额：' + res.data.amount + ' ' + res.data.currency);
            $('#payment-area').show();
            checkOrder(res.data.order_no);
        } else {
            Dcat.error(res.message);
            btn.prop('disabled', false).text('立即购买');
        }
    }).fail(function() {
        Dcat.error('网络错误');
        btn.prop('disabled', false).text('立即购买');
    });
});

function checkOrder(orderNo) {
    var timer = setInterval(function() {
        $.get('/api/plugin/check-order/' + orderNo, function(res) {
            if (res.status === 'paid') {
                clearInterval(timer);
                Dcat.success('支付成功！');
                setTimeout(function() { location.reload(); }, 2000);
            }
        });
    }, 3000);
    setTimeout(function() { clearInterval(timer); }, 300000);
}
</script>
