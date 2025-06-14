<!DOCTYPE html>
<html>

<head>
    <title>SenangPay Test Payment</title>
</head>

<body>
    <form method="POST" action="{{ url('/payment/process') }}">
        @csrf
        <p><input type="text" name="detail" placeholder="Transaction Detail"></p>
        <p><input type="text" name="amount" placeholder="Amount (e.g. 10.00)"></p>
        <p><input type="text" name="order_id" value="{{ time() }}" placeholder="Order ID"></p>
        <p><input type="text" name="name" placeholder="Customer Name"></p>
        <p><input type="email" name="email" placeholder="Customer Email"></p>
        <p><input type="text" name="phone" placeholder="Customer Phone"></p>
        <button type="submit">Pay Now</button>
    </form>
</body>

</html>