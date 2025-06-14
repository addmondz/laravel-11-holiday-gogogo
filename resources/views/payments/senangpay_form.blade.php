<!DOCTYPE html>
<html>

<head>
    <title>Redirecting to senangPay...</title>
</head>

<body onload="document.forms['senangpay'].submit();">
    <form name="senangpay" method="post" action="https://sandbox.senangpay.my/payment/{{ $merchant_id }}">
        <input type="hidden" name="detail" value="{{ $detail }}">
        <input type="hidden" name="amount" value="{{ $amount }}">
        <input type="hidden" name="order_id" value="{{ $order_id }}">
        <input type="hidden" name="name" value="{{ $name }}">
        <input type="hidden" name="email" value="{{ $email }}">
        <input type="hidden" name="phone" value="{{ $phone }}">
        <input type="hidden" name="hash" value="{{ $hash }}">
        <noscript>
            <p>JavaScript is required to redirect to senangPay. Please click the button below.</p>
            <button type="submit">Proceed to Payment</button>
        </noscript>
    </form>
</body>

</html>