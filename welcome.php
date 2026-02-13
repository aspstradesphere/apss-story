<?php
session_start();
date_default_timezone_set('Asia/Kolkata'); // Set Timezone for India

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['visitor_name'] = $_POST['name'] ?? 'Guest';

    $location = trim($_POST['location'] ?? '');
    if (empty($location)) {
        // Fallback to IP address if JS failed or blocked
        $location = 'IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown');
    }
    $_SESSION['visitor_location'] = $location;

    // Log Visitor to JSON File
    $logFile = 'data/visitors.json';
    $newVisitor = [
        'name' => $_SESSION['visitor_name'],
        'location' => $_SESSION['visitor_location'],
        'visit_date' => gmdate('Y-m-d H:i:s') // Store UTC for consistency with logs code
    ];

    $currentData = [];
    if (file_exists($logFile)) {
        $jsonContent = file_get_contents($logFile);
        $currentData = json_decode($jsonContent, true);
        if (!is_array($currentData))
            $currentData = [];
    }

    // Keep only last 500 visitors to manage file size
    if (count($currentData) > 500) {
        $currentData = array_slice($currentData, 0, 500);
    }

    // Add new visitor to top
    array_unshift($currentData, $newVisitor);

    file_put_contents($logFile, json_encode($currentData, JSON_PRETTY_PRINT));

    // --- Send Telegram Alert ---
    $telegram_token = '8264418717:AAGfM2aYqPykwdf-rUCYYthMet9wg41w2lc'; // Your Bot Token
    $telegram_chat_id = '7538958303'; // Sujit Singh's Chat ID

    if (!empty($telegram_token) && !empty($telegram_chat_id)) {
        $msg = "🔔 *New Visitor Alert!*\n\n👤 *Name:* " . $_SESSION['visitor_name'] . "\n📍 *Location:* " . $_SESSION['visitor_location'] . "\n🕒 *Time:* " . date('d M Y, H:i');

        $url = "https://api.telegram.org/bot$telegram_token/sendMessage?chat_id=$telegram_chat_id&text=" . urlencode($msg) . "&parse_mode=Markdown";

        // Send in background (Robust Method)
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $cmd = "start /B curl -s \"$url\"";
            pclose(popen($cmd, "r"));
        } else {
            exec("curl -s \"$url\" > /dev/null 2>&1 &");
        }

        // Slight delay to ensure process spawns before redirect kills the script context
        usleep(100000); // 0.1 seconds
    }
    header("Location: story.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to APSS TradeSphere</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: #fdfdfd;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #333;
        }

        .welcome-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        .logo {
            height: 60px;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #2E7D32;
        }

        p {
            font-size: 14px;
            color: #666;
            margin-bottom: 30px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
            font-family: inherit;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #D98600;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        button:hover {
            background: #b77100;
        }
    </style>
</head>

<body>

    <div class="welcome-card">
        <img src="assets/images/logo.png" alt="APSS Logo" class="logo">
        <h1>Welcome</h1>
        <p>Discover the stories behind our premium products. Enter your name to begin.</p>

        <form method="POST">
            <input type="text" name="name" placeholder="Your Name" required>
            <input type="hidden" name="location" id="locationField">
            <button type="submit">Enter Site</button>
        </form>
    </div>

    <script>
        // Simple IP Geolocation Fetch (HTTPS compatible)
        fetch('https://ipwho.is/')
            .then(response => response.json())
            .then(data => {
                if (data.success) { // ipwho.is uses boolean 'success'
                    const location = `${data.city}, ${data.country}`;
                    document.getElementById('locationField').value = location;
                } else {
                    console.log('Location fetch failed:', data.message);
                }
            })
            .catch(err => console.log('Location fetch error', err));
    </script>
</body>

</html>