<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to SenangPay...</title>
</head>
<body onload="document.forms[0].submit();">
    <form method="POST" action="https://sandbox.senangpay.my/payment/{{ $merchant_id }}">
        <input type="hidden" name="detail" value="{{ $data['detail'] }}">
        <input type="hidden" name="amount" value="{{ $data['amount'] }}">
        <input type="hidden" name="order_id" value="{{ $data['order_id'] }}">
        <input type="hidden" name="name" value="{{ $data['name'] }}">
        <input type="hidden" name="email" value="{{ $data['email'] }}">
        <input type="hidden" name="phone" value="{{ $data['phone'] }}">
        <input type="hidden" name="hash" value="{{ $hash }}">
    </form>
    <p>Redirecting to payment page...</p>
</body>
</html>
