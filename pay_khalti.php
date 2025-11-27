<?php
session_start();

if (!isset($_POST['booking_id']) || !isset($_POST['amount'])) {
    die("Invalid payment request!");
}

$booking_id = $_POST['booking_id'];
$amount = $_POST['amount'];


$amount_paisa = $amount * 100;


$khalti_public_key = "test_public_key_xxxxx";
$khalti_secret_key = "test_secret_key_xxxxx";

?>

<!DOCTYPE html>
<html>
<head>
    <title>Pay with Khalti</title>
</head>
<body>

<h2>Khalti Payment</h2>
<p>Booking ID: <?= $booking_id ?></p>
<p>Amount: NPR <?= $amount ?></p>


<script src="https://khalti.com/static/khalti-checkout.js"></script>

<button id="payment-button" style="background:#5a2ca0;color:white;padding:10px 20px;border:none;border-radius:5px;cursor:pointer;">
    Pay with Khalti
</button>

<script>
var config = {
    "publicKey": "<?= $khalti_public_key ?>",
    "productIdentity": "<?= $booking_id ?>",
    "productName": "Vehicle Booking Payment",
    "productUrl": "#",
    "paymentPreference": [
        "KHALTI"
    ],
    "eventHandler": {
        onSuccess (payload) {
            // send payment info to server for verification
            fetch("verify_khalti.php", {
                method: "POST",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify(payload)
            })
            .then(res => res.text())
            .then(data => alert(data));
        },
        onError (error) {
            console.log(error);
            alert("Payment failed!");
        },
        onClose () {
            console.log("User closed payment widget");
        }
    }
};

var checkout = new KhaltiCheckout(config);
document.getElementById("payment-button").onclick = function () {
    checkout.show({amount: <?= $amount_paisa ?>});
};
</script>

</body>
</html>
