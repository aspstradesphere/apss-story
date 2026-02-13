<?php
// send_sample.php - Handles Free Sample Request
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Collect Data
    $visitor_name = $_SESSION['visitor_name'] ?? 'Guest';
    $visitor_location = $_SESSION['visitor_location'] ?? 'Unknown Location';

    $product_name = $_POST['product_name'] ?? 'Unknown Product';

    // New Fields
    $email = $_POST['email'] ?? 'Not Provided';
    $phone = $_POST['phone'] ?? 'Not Given';

    $address = $_POST['address'] ?? 'Not Provided';
    $city_zip = $_POST['city_zip'] ?? '';
    $country = $_POST['country'] ?? '';

    $agreed = isset($_POST['agree_shipping']) ? 'YES' : 'NO';

    if ($agreed !== 'YES') {
        echo "Error: Must agree to shipping charges.";
        exit;
    }

    // 2. Format Telegram Message
    $msg = "🎁 *New FREE Sample Request!*\n\n" .
        "👤 *Buyer:* $visitor_name\n" .
        "📍 *Location:* $visitor_location\n" .
        "📦 *Product Ref:* $product_name\n\n" .
        "📧 *Email:* $email\n" .
        "📞 *Phone:* $phone\n\n" .
        "🚚 *Shipping Address:*\n" .
        "$address\n$city_zip\n$country\n\n" .
        "✅ *Buyer AGREED to pay Shipping Cost.*\n\n" .
        "🕒 *Time:* " . date('d M Y, H:i');

    // 3. Send to Telegram
    $telegram_token = '8264418717:AAGfM2aYqPykwdf-rUCYYthMet9wg41w2lc';
    $telegram_chat_id = '7538958303';


    $url = "https://api.telegram.org/bot$telegram_token/sendMessage?chat_id=$telegram_chat_id&text=" . urlencode($msg) . "&parse_mode=Markdown";

    // Send in background (Windows/Linux compatible)
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $cmd = "start /B curl -s \"$url\"";
        pclose(popen($cmd, "r"));
    } else {
        exec("curl -s \"$url\" > /dev/null 2>&1 &");
    }

    echo "Success";
} else {
    echo "Invalid Request";
}
?>