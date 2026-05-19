<!DOCTYPE html>
<html>
<head>
    <title>Razorpay Payment</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
<h2>Redirecting to secure payment gateway...</h2>

<script>
    var options = {
        "key": "{{ $key }}",
        "amount": "{{ $amount }}",
        "currency": "{{ $currency }}",
        "name": "Shaun Store",
        "description": "{{ $description }} #{{ $order_id }}",
        "order_id": "{{ $order_id }}",
        "handler": function (response){
            window.location.href = "{{ $url_successful }}";
        },
        "modal": {
            "ondismiss": function(){
                window.location.href = "{{ $url_cancel }}";
            }
        },
        "prefill": {
            "name": "{{ $name }}",
            "email": "{{ $email }}"
        },
        "theme": {
            "color": "#528FF0"
        }
    };

    var rzp = new Razorpay(options);
    window.onload = function() {
        rzp.open();
    };
</script>
</body>
</html>
