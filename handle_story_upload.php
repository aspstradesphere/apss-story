<?php
// handle_story_upload.php
// Handles text story submissions for the "Wall of Trust"

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// --- Configuration ---
$jsonFile = __DIR__ . '/data/story_content.json';

// --- Functions ---
function getFlagEmoji($countryName)
{
    // Basic mapping for common countries (Extend as needed)
    $map = [
        'india' => '🇮🇳',
        'usa' => '🇺🇸',
        'uk' => '🇬🇧',
        'uae' => '🇦🇪',
        'dubai' => '🇦🇪',
        'canada' => '🇨🇦',
        'australia' => '🇦🇺',
        'germany' => '🇩🇪',
        'france' => '🇫🇷',
        'japan' => '🇯🇵',
        'china' => '🇨🇳',
        'russia' => '🇷🇺',
        'brazil' => '🇧🇷'
    ];
    $lower = strtolower(trim($countryName));
    return $map[$lower] ?? '🌍'; // Default to globe if unknown
}

// --- Validation ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Get POST data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$country = trim($_POST['country'] ?? '');
$message = trim($_POST['message'] ?? '');

if (empty($name) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Name and Message are required.']);
    exit;
}

// --- Update JSON Data ---
if (file_exists($jsonFile)) {
    $data = json_decode(file_get_contents($jsonFile), true);
} else {
    $data = ['testimonials' => []];
}

// Create new testimonial entry
$newTestimonial = [
    'type' => 'text',
    'quote' => $message,
    'flag' => getFlagEmoji($country),
    'author' => $name,
    'country_name' => $country, // Store original text too
    'video_url' => '',
    'date' => date('Y-m-d H:i:s')
];

// Append to testimonials (Add to beginning for visibility?)
// Let's add to the beginning so the user sees it immediately
array_unshift($data['testimonials'], $newTestimonial);

// Limit to last 50 testimonials to prevent infinite growth? 
// For now, let's keep it simple.

if (file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {


    // --- Notify Admin (Telegram) ---
    // Using valid credentials from welcome.php
    $telegram_token = '8264418717:AAGfM2aYqPykwdf-rUCYYthMet9wg41w2lc';
    $telegram_chat_id = '7538958303';

    $tgMessage = "📝 *New Wall of Trust Story!*\n\n" .
        "👤 *Name:* $name\n" .
        "🌍 *Country:* $country\n" .
        "💬 *Message:* \"$message\"\n" .
        "✅ *Status:* Auto-published to website.";

    $url = "https://api.telegram.org/bot$telegram_token/sendMessage?chat_id=$telegram_chat_id&text=" . urlencode($tgMessage) . "&parse_mode=Markdown";

    // Use System Curl to bypass PHP SSL issues (Async Background)
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $cmd = "start /B curl -s \"$url\"";
        pclose(popen($cmd, "r"));
    } else {
        exec("curl -s \"$url\" > /dev/null 2>&1 &");
    }

    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your story has been posted to our Wall of Trust.'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save story. Please try again.']);
}
?>