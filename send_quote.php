<?php
// send_quote.php - Handles Quote Request Submission (Telegram Alert)
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Collect Data
    $visitor_name = $_SESSION['visitor_name'] ?? 'Guest';
    $visitor_location = $_SESSION['visitor_location'] ?? 'Unknown Location';

    $product_name = $_POST['product_name'] ?? 'Unknown Product';
    $quantity = $_POST['quantity'] ?? 'Not Specified';
    $incoterm = $_POST['incoterm'] ?? 'Not Specified';
    $message = $_POST['message'] ?? 'No extra message';

    // 2. Format Telegram Message
    $msg = "🧾 *New Quote Request!*\n\n" .
        "👤 *Buyer:* $visitor_name\n" .
        "📍 *Location:* $visitor_location\n" .
        "📦 *Product:* $product_name\n" .
        "⚖️ *Quantity:* $quantity\n" .
        "🚚 *Incoterm:* $incoterm\n" .
        "💬 *Note:* $message\n\n" .
        "🕒 *Time:* " . date('d M Y, H:i');

    // 3. Send to Telegram (Using System Curl for Reliability)
    // Your Config
    // Your Config
    $telegram_token = '8264418717:AAGfM2aYqPykwdf-rUCYYthMet9wg41w2lc';
    $telegram_chat_id = '7538958303';


    $url = "https://api.telegram.org/bot$telegram_token/sendMessage?chat_id=$telegram_chat_id&text=" . urlencode($msg) . "&parse_mode=Markdown";

    // Send in background
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $cmd = "start /B curl -s \"$url\"";
        pclose(popen($cmd, "r"));
    } else {
        // For Linux/Mac servers (if you deploy later)
        exec("curl -s \"$url\" > /dev/null 2>&1 &");
    }

    echo "Success";
} else {
    echo "Invalid Request";
}
?>