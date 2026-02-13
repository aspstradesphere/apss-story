<?php
// Catalog View Page - Dynamic Content
session_start();

// Gatekeeper Check
if (!isset($_SESSION['visitor_name'])) {
    header("Location: welcome.php");
    exit;
}

// Get ID (e.g., ?id=areca)
$product_id = isset($_GET['id']) ? $_GET['id'] : 'areca';

// Load Data
$json_data = file_get_contents('data/story_content.json');
$data = json_decode($json_data, true);

// Find Product
$product = null;
if (isset($data['products'])) {
    foreach ($data['products'] as $p) {
        if ($p['id'] == $product_id) {
            $product = $p;
            break;
        }
    }
}

// Fallback if not found (or default to first)
if (!$product && !empty($data['products'])) {
    $product = $data['products'][0];
}

// Personalization
$visitor_name = isset($_SESSION['visitor_name']) ? $_SESSION['visitor_name'] : '';
$visitor_location = isset($_SESSION['visitor_location']) ? $_SESSION['visitor_location'] : '';

$personalization_text = "";
if ($visitor_name && $visitor_name !== 'Guest') {
    $personalization_text = "Prepared Exclusively for you " . htmlspecialchars($visitor_name);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APSS TradeSphere -
        <?php echo htmlspecialchars($product['name']); ?> Catalog
    </title>
    <!-- Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* General Reset & Print Settings */
        body {
            background: #e0e0e0;
            margin: 0;
            padding: 20px;
            font-family: 'Montserrat', sans-serif;
            color: #333;
            -webkit-print-color-adjust: exact;
        }

        @page {
            size: A4;
            margin: 0;
        }

        .page {
            width: 210mm;
            height: 297mm;
            background: white;
            margin: 0 auto 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
            page-break-after: always;
        }

        @media print {
            body {
                background: none;
                margin: 0;
                padding: 0;
            }

            .page {
                width: 100%;
                height: 100%;
                margin: 0;
                box-shadow: none;
                border: none;
            }

            .no-print {
                display: none;
            }

            /* Hide HTML Video in Print, Show Link/QR instead */
            .video-container {
                display: none !important;
            }

            .video-qr-fallback {
                display: block !important;
            }
        }

        /* Typography */
        h1,
        h2,
        h3,
        h4 {
            font-family: 'Playfair Display', serif;
        }

        .text-center {
            text-align: center;
        }

        .text-primary {
            color: #2E7D32;
        }

        /* Green */
        .text-secondary {
            color: #D98600;
        }

        /* Orange */
        .bold {
            font-weight: 700;
        }

        /* Global Header (Pages 2-8 commonly) */
        .global-header {
            text-align: center;
            padding: 20px 0 10px;
            border-bottom: 2px solid #eee;
        }

        .global-header img {
            height: 40px;
        }

        .global-tagline {
            font-size: 10px;
            color: #666;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 5px;
        }

        /* Global Footer */
        .global-footer {
            margin-top: auto;
            background: #002147;
            /* Navy Blue */
            color: white;
            padding: 10px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
        }

        .footer-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .footer-item i {
            color: #D98600;
        }

        /* Print Button */
        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            padding: 12px 24px;
            background: #D98600;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        /* Back Button */
        .btn-back {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            padding: 12px 24px;
            background: #2E7D32;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'Montserrat', sans-serif;
            font-size: 13.33px;
        }

        .btn-back:hover {
            background: #1b5e20;
        }

        /* Request Quote Button (Floating) - Green (Moved Up) */
        .btn-quote {
            position: fixed;
            bottom: 95px;
            right: 30px;
            z-index: 1000;
            padding: 15px 30px;
            background: #2E7D32;
            /* Green */
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(46, 125, 50, 0.4);
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: transform 0.2s;
        }

        .btn-quote:hover {
            transform: scale(1.05);
            background: #1b5e20;
        }

        /* Request Sample Button (Floating) - Yellow (Moved Down) */
        .btn-sample {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            padding: 12px 25px;
            background: #FBC02D;
            /* Yellow */
            color: #333;
            /* Dark text for contrast */
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(251, 192, 45, 0.4);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: transform 0.2s;
        }

        .btn-sample:hover {
            transform: scale(1.05);
            background: #F9A825;
        }

        /* Quote Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10001;
            justify-content: center;
            align-items: center;
        }

        .quote-modal {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
            position: relative;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #888;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            font-size: 13px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            font-family: inherit;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #2E7D32;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        /* --- Page 1: Hero Cover --- */
        .cover-top {
            padding-top: 50px;
            text-align: center;
        }

        .cover-logo {
            height: 80px;
            margin-bottom: 20px;
        }

        .cover-company {
            font-size: 24px;
            letter-spacing: 2px;
            color: #222;
        }

        .cover-tagline {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #555;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .cover-tagline::before,
        .cover-tagline::after {
            content: "";
            height: 1px;
            width: 50px;
            background: #999;
            margin: 0 15px;
        }

        .personalization-text {
            color: #D98600;
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            margin-top: 25px;
            font-weight: 700;
            /* Make it bold */
            font-style: italic;
            background: #fff8e1;
            /* Light yellow background */
            display: inline-block;
            padding: 5px 20px;
            border-radius: 20px;
            border: 1px solid #ffe0b2;
        }

        .cover-middle {
            text-align: center;
            margin: 40px 0;
        }

        .cover-title {
            font-size: 48px;
            letter-spacing: 5px;
            line-height: 1.1;
            font-weight: 900;
            color: #1a1a1a;
            text-transform: uppercase;
        }

        .ribbon-container {
            position: relative;
            background: #f4f8f4;
            padding: 15px;
            margin: 20px auto 0;
            width: 80%;
            display: flex;
            align-items: center;
            justify-content: center;
            clip-path: polygon(0% 0%, 100% 0%, 95% 50%, 100% 100%, 0% 100%, 5% 50%);
        }

        .ribbon-text {
            font-family: 'Montserrat', sans-serif;
            font-size: 11px;
            letter-spacing: 1px;
            color: #2E7D32;
            font-weight: 600;
        }

        .ribbon-leaf {
            position: absolute;
            left: 20px;
            color: #2E7D32;
            font-size: 18px;
        }

        .cover-bottom {
            flex: 1;
            /* Dynamic Image Background */
            background: url('<?php echo !empty($product['image']) ? $product['image'] : 'assets/images/product-areca-leaf.jpg'; ?>') no-repeat center bottom / cover;
            position: relative;
        }

        .cover-overlay-footer {
            position: absolute;
            bottom: 30px;
            left: 0;
            right: 0;
            background: rgba(0, 33, 71, 0.9);
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 12px;
        }

        /* --- Page 2: Company Profile --- */
        .content-padding {
            padding: 40px;
        }

        .section-title h2 {
            font-size: 28px;
            color: #222;
            margin-bottom: 20px;
            letter-spacing: 1px;
        }

        .profile-bullets {
            margin-top: 30px;
        }

        .profile-bullet {
            display: flex;
            margin-bottom: 25px;
        }

        .bullet-icon {
            width: 40px;
            color: #2E7D32;
            font-size: 20px;
            text-align: center;
        }

        .bullet-text strong {
            display: block;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .bullet-text {
            font-size: 13px;
            color: #555;
            line-height: 1.5;
        }

        .why-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 50px;
            background: #f9f9f9;
            padding: 30px;
            border-radius: 8px;
        }

        .why-item {
            text-align: center;
        }

        .why-icon {
            font-size: 30px;
            color: #D98600;
            margin-bottom: 10px;
        }

        .why-title {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .why-desc {
            font-size: 11px;
            color: #666;
        }

        /* Video Container Styles */
        .video-wrapper {
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
            background: #000;
            margin-top: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .video-wrapper video {
            width: 100%;
            display: block;
        }

        .video-qr-fallback {
            display: none;
            /* Hidden by default, shown in print */
            text-align: center;
            margin-top: 20px;
            border: 2px dashed #D98600;
            padding: 20px;
            border-radius: 8px;
        }

        /* --- Page 3: Product Portfolio --- */
        .split-layout {
            display: flex;
            height: 100%;
        }

        .split-half {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .bg-light-green {
            background: #f1f8f1;
        }

        .bg-light-wood {
            background: #fcf6ef;
        }

        .portfolio-img {
            width: 100%;
            height: auto;
            /* Allow natural height */
            display: block;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .portfolio-list {
            list-style: none;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .portfolio-list li {
            background: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid #ddd;
        }

        /* --- Page 4 & 5 Grid Basics --- */
        .feature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .feature-box {
            display: flex;
            align-items: center;
            border: 1px solid #eee;
            padding: 15px;
            border-radius: 6px;
        }

        .feature-icon {
            font-size: 24px;
            color: #2E7D32;
            margin-right: 15px;
        }

        .catalog-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            align-items: end;
            /* Aligns bottom of cards */
        }

        .catalog-card {
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            /* Removed masonry props */
            width: auto;
            margin-bottom: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .catalog-card img {
            width: 100%;
            height: auto;
            /* Natural height */
            display: block;
            border-radius: 4px;
        }

        .caption {
            font-size: 12px;
            font-weight: 600;
            text-align: center;
            margin-top: 8px;
            color: #444;
        }

        /* --- Page 5: Visual Gallery Masonry --- */
        .masonry-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            padding: 0 40px;
            align-items: center;
        }

        .masonry-item img {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 4px;
        }

        .masonry-caption {
            text-align: center;
            font-size: 10px;
            margin-top: 4px;
            color: #666;
        }

        /* --- Page 6: Performance --- */
        .icon-bar {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin: 30px 0;
        }

        .icon-label {
            text-align: center;
            font-size: 12px;
            font-weight: 600;
        }

        .icon-circle {
            width: 50px;
            height: 50px;
            background: #eee;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 5px;
            color: #2E7D32;
            font-size: 20px;
        }

        /* --- Page 9: Back Cover --- */
        .world-map-bg {
            flex: 1;
            background: url('https://upload.wikimedia.org/wikipedia/commons/8/80/World_map_-_low_resolution.svg') no-repeat center;
            background-size: contain;
            opacity: 0.1;
            position: absolute;
            top: 200px;
            left: 0;
            right: 0;
            bottom: 200px;
        }

        .achievements-bar {
            display: flex;
            justify-content: space-around;
            background: #f4f4f4;
            padding: 20px;
            margin: 30px 40px;
            border-radius: 8px;
        }

        .achievement-item {
            text-align: center;
        }

        .achievement-icon {
            font-size: 24px;
            color: #D98600;
            display: block;
            margin-bottom: 5px;
        }

        .achievement-text {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .contact-box {
            background: #fff;
            border: 2px solid #D98600;
            margin: 0 40px 40px;
            padding: 30px;
            position: relative;
            border-radius: 8px;
            text-align: center;
        }

        .gold-seal {
            position: absolute;
            left: -20px;
            top: 50%;
            transform: translateY(-50%);
            width: 80px;
            height: 80px;
            background: #D98600;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            border: 3px solid white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .cta-btn {
            background: #2E7D32;
            color: white;
            padding: 10px 30px;
            display: inline-block;
            text-decoration: none;
        }

        /* Fixed Image Size & Cropping - Default */
        .catalog-card img {
            width: 100%;
            height: 280px;
            /* Fallback default */
            object-fit: cover;
            border-radius: 4px;
            display: block;
            cursor: zoom-in;
            transition: transform 0.2s;
        }

        .catalog-card img:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Video containers should match */
        .catalog-card video {
            width: 100%;
            height: 220px;
            /* Reduced height */
            object-fit: cover;
            border-radius: 4px;
        }

        /* Lightbox Styles */
        .lightbox {
            display: none;
            position: fixed;
            z-index: 9999;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            justify-content: center;
            align-items: center;
        }

        .lightbox img {
            max-width: 90%;
            max-height: 90%;
            border: 2px solid white;
            border-radius: 4px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            object-fit: contain;
        }

        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            font-size: 40px;
            cursor: pointer;
            font-weight: bold;
            z-index: 10000;
        }

        /* --- Mobile Responsive Support --- */
        @media screen and (max-width: 768px) {
            body {
                padding: 0;
                background: #f4f4f4;
            }

            html,
            body {
                overflow-x: hidden;
                width: 100%;
                margin: 0;
            }

            .page {
                width: 100% !important;
                max-width: 100vw !important;
                height: auto;
                min-height: 100vh;
                margin: 0 0 10px 0;
                box-shadow: none;
                border: none;
                border-bottom: 1px solid #ddd;
                clip-path: none !important;
                box-sizing: border-box;
                /* Crucial for padding */
                overflow-x: hidden;
                /* Prevent spillover */
                padding: 15px !important;
                /* Reduce padding to ensure fit */
            }

            /* Adjust Floating Buttons */
            .btn-back,
            .btn-print {
                position: static;
                display: flow-root;
                /* Better than inline block for spacing */
                width: 100%;
                margin: 5px 0;
                text-align: center;
                font-size: 14px;
                padding: 12px;
                box-sizing: border-box;
                border-radius: 0;
            }

            .btn-back {
                margin: 0;
                background: #333;
            }

            .btn-print {
                margin: 0;
                float: none;
                background: #D98600;
            }

            /* Fix Quote/Sample Buttons Position on Mobile */
            .btn-quote {
                bottom: 70px;
                right: 15px;
                padding: 10px 20px;
                font-size: 13px;
            }

            .btn-sample {
                bottom: 15px;
                right: 15px;
                padding: 10px 20px;
                font-size: 12px;
            }

            /* Stack layouts vertically */
            .split-layout,
            .feature-grid,
            .catalog-grid,
            .masonry-grid,
            .achievements-bar,
            .why-grid {
                display: flex !important;
                flex-direction: column !important;
                grid-template-columns: 1fr !important;
                gap: 20px;
                padding: 20px !important;
                height: auto !important;
            }

            /* Icons Grid - Allow wrapping instead of strict column */
            .icon-bar {
                display: flex !important;
                flex-wrap: wrap !important;
                justify-content: center !important;
                gap: 20px !important;
                padding: 20px !important;
                margin: 0 !important;
            }

            .icon-item {
                flex: 0 0 100px;
                /* Allow items to sit side-by-side if space permits */
            }

            /* Remove fixed heights/sizes */
            .catalog-card img,
            .catalog-card video,
            .portfolio-img {
                height: auto !important;
                max-height: 300px;
            }

            .catalog-card {
                width: 100% !important;
            }

            .content-padding {
                padding: 20px !important;
            }

            /* Header/Footer Adjustments */
            .global-footer {
                flex-direction: column;
                gap: 10px;
                text-align: center;
                padding: 20px;
            }

            .foot-item {
                margin: 5px 0;
            }

            /* Specific Page Tweaks */
            .cover-title {
                font-size: 28px !important;
                /* Safe fixed size for mobile */
                letter-spacing: 1px !important;
                word-wrap: break-word;
                overflow-wrap: break-word;
                line-height: 1.3;
                margin: 0 auto;
                width: 100%;
                max-width: 100%;
                padding: 0 10px;
                /* safety padding */
            }

            .cover-company {
                font-size: 18px !important;
                /* Reduce from 24px */
                letter-spacing: 1px !important;
                /* Reduce from 2px */
                width: 90%;
                margin: 0 auto;
                word-wrap: break-word;
            }

            .cover-tagline {
                font-size: 10px !important;
            }

            .personalization-text {
                font-size: 13px !important;
                white-space: normal;
                /* Allow wrapping */
                max-width: 90%;
                line-height: 1.4;
                padding: 8px 15px;
            }

            .ribbon-text {
                font-size: 9px !important;
                /* Make sure it fits */
                white-space: normal;
                line-height: 1.4;
            }

            .ribbon-container {
                padding: 10px 5px !important;
            }

            .cover-middle {
                margin: 20px 0;
                /* reduce margin */
            }

            .cover-bottom {
                min-height: 250px;
                /* Ensure image shows */
            }

            /* Modals */
            .quote-modal {
                width: 90%;
                padding: 15px;
                max-height: 80vh;
                overflow-y: auto;
            }

            .ribbon-container {
                width: 95%;
                /* Use full width */
                clip-path: none;
                /* Simplify ribbon */
                border: 1px solid #e0e0e0;
                border-radius: 4px;
                margin: 10px auto;
                padding: 10px;
            }

            .contact-box {
                margin: 15px;
                padding: 15px;
            }

            .world-map-bg {
                top: 20px;
                bottom: 20px;
            }

            /* Hide print-specific elements if they clutter */
            .video-qr-fallback {
                display: none !important;
            }
        }
    </style>
</head>

<body>


    <a href="story.php" class="btn-back no-print"><i class="fas fa-home"></i> Back to Home</a>
    <button onclick="window.print()" class="btn-print no-print"><i class="fas fa-print"></i> Save as PDF</button>
    <button onclick="openQuoteModal()" class="btn-quote no-print"><i class="fas fa-file-invoice-dollar"></i> Request
        Quote</button>
    <button onclick="openSampleModal()" class="btn-sample no-print"><i class="fas fa-box-open"></i> Request Free
        Sample</button>

    <!-- Quote Modal -->
    <div id="quoteModal" class="modal-overlay no-print">
        <div class="quote-modal">
            <span class="close-modal" onclick="closeQuoteModal()">&times;</span>
            <h2 style="margin-top:0; color:#2E7D32; text-align:center;">Request a Quote</h2>
            <p style="text-align:center; font-size:13px; color:#666; margin-bottom:20px;">
                Interested in <strong><?php echo htmlspecialchars($product['name']); ?></strong>? Let us know your
                requirements.
            </p>

            <form id="quoteForm" onsubmit="submitQuote(event)">
                <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>">
                <div class="form-group">
                    <label>Quantity Required</label>
                    <input type="text" name="quantity" placeholder="e.g. 500 kgs, 1 Container" required>
                </div>
                <div class="form-group">
                    <label>Preferred Incoterm</label>
                    <select name="incoterm" required>
                        <option value="" disabled selected>Select Incoterm</option>
                        <option value="EXW">EXW - Ex Works</option>
                        <option value="FOB">FOB - Free on Board</option>
                        <option value="CIF">CIF - Cost, Insurance & Freight</option>
                        <option value="DDP">DDP - Delivered Duty Paid</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Your Message (Optional)</label>
                    <textarea name="message" placeholder="Any specific packaging or quality requirements?"
                        rows="3"></textarea>
                </div>
                <button type="submit" class="btn-submit">Send Request</button>
            </form>
        </div>
    </div>

    <!-- Sample Modal -->
    <div id="sampleModal" class="modal-overlay no-print">
        <div class="quote-modal">
            <span class="close-modal" onclick="closeSampleModal()">&times;</span>
            <h2 style="margin-top:0; color:#D98600; text-align:center;">Request Free Sample</h2>
            <p style="text-align:center; font-size:13px; color:#666; margin-bottom:15px;">
                Experience the premium quality of <strong><?php echo htmlspecialchars($product['name']); ?></strong>.
            </p>

            <div
                style="background:#e8f5e9; padding:12px; border-radius:6px; margin-bottom:20px; font-size:12px; color:#2E7D32; text-align:center; border:1px solid #c8e6c9;">
                <strong><i class="fas fa-check-circle"></i> Sample Cost: FREE ($0)</strong><br>
                <span style="color:#d32f2f; font-weight:600;">Recipient pays shipping/courier charges.</span>
            </div>

            <form id="sampleForm" onsubmit="submitSample(event)">
                <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>">

                <div class="form-group">
                    <label>Email ID</label>
                    <input type="email" name="email" placeholder="yourname@company.com" required>
                </div>

                <div class="form-group">
                    <label>Contact Number (For Courier)</label>
                    <input type="tel" name="phone" placeholder="+1 234..." required>
                </div>

                <div class="form-group">
                    <label>Full Shipping Address</label>
                    <textarea name="address" placeholder="Street, Building, Area" required rows="2"></textarea>
                </div>

                <div style="display:flex; gap:10px;">
                    <div class="form-group" style="flex:1;">
                        <label>City & Zip Code</label>
                        <input type="text" name="city_zip" required>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>Country</label>
                        <select name="country" id="countrySelect" required onchange="calculateShipping()">
                            <option value="" disabled selected>Select Country</option>
                            <option value="USA">USA</option>
                            <option value="UK">UK</option>
                            <option value="Canada">Canada</option>
                            <option value="Australia">Australia</option>
                            <option value="UAE">UAE</option>
                            <option value="Germany">Germany</option>
                            <option value="France">France</option>
                            <option value="Netherlands">Netherlands</option>
                            <option value="Singapore">Singapore</option>
                            <option value="Saudi Arabia">Saudi Arabia</option>
                            <option value="Japan">Japan</option>
                            <option value="Malaysia">Malaysia</option>
                            <option value="South Africa">South Africa</option>
                            <option value="India">India</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div id="shipping-estimate"
                    style="margin-bottom:15px; padding:10px; background:#e3f2fd; color:#0d47a1; font-size:12px; border-radius:4px; display:none; border: 1px solid #bbdefb;">
                    <i class="fas fa-calculator"></i> <strong>Est. Courier Cost (1kg):</strong> <span id="est-cost"
                        style="font-weight:bold; font-size:13px;">...</span>
                    <br><span style="font-size:10px;color:#666;">(Approximate DHL/FedEx rates)</span>
                </div>

                <div class="form-group">
                    <label
                        style="font-weight:normal; font-size:12px; display:flex; gap:8px; align-items:start; cursor:pointer; background: #fffde7; padding: 10px; border: 1px solid #fff59d; border-radius: 4px;">
                        <input type="checkbox" name="agree_shipping" required style="width:auto; margin-top:2px;">
                        <span>I consent to pay the <strong>shipping/delivery charges</strong> as estimated above.</span>
                    </label>
                </div>

                <button type="submit" class="btn-submit" style="background:#FBC02D; color:#333;">Request Sample</button>
            </form>
        </div>
    </div>

    <!-- JavaScript for Modals -->
    <script>
        // Shipping Estimator Logic
        function calculateShipping() {
            const country = document.getElementById('countrySelect').value;
            const estimateBox = document.getElementById('shipping-estimate');
            const costSpan = document.getElementById('est-cost');

            // Basic Rate Table (Approx 1kg DHL/FedEx in USD)
            const rates = {
                'USA': '$55 - $70 USD',
                'Canada': '$60 - $75 USD',
                'UK': '$50 - $65 USD',
                'Germany': '$50 - $65 USD',
                'France': '$50 - $65 USD',
                'Netherlands': '$50 - $65 USD',
                'Australia': '$65 - $80 USD',
                'UAE': '$35 - $45 USD',
                'Saudi Arabia': '$40 - $50 USD',
                'Singapore': '$30 - $40 USD',
                'Malaysia': '$30 - $40 USD',
                'Japan': '$50 - $65 USD',
                'South Africa': '$70 - $90 USD',
                'India': '₹100 - ₹500 INR (Domestic)',
                'Other': '$50 - $100 USD (Varies)'
            };

            if (rates[country]) {
                estimateBox.style.display = 'block';
                costSpan.innerText = rates[country];
            } else {
                estimateBox.style.display = 'none';
            }
        }

        // Quote Modal Functions
        function openQuoteModal() {
            document.getElementById('quoteModal').style.display = 'flex';
        }

        function closeQuoteModal() {
            document.getElementById('quoteModal').style.display = 'none';
        }

        // Sample Modal Functions
        function openSampleModal() {
            document.getElementById('sampleModal').style.display = 'flex';
        }

        function closeSampleModal() {
            document.getElementById('sampleModal').style.display = 'none';
        }

        // Submit Quote
        function submitQuote(e) {
            e.preventDefault();
            const btn = e.target.querySelector('button');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            btn.disabled = true;

            const formData = new FormData(e.target);

            fetch('send_quote.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(data => {
                    alert('Quote Request Sent Successfully!');
                    closeQuoteModal();
                    btn.innerHTML = 'Send Request';
                    btn.disabled = false;
                    e.target.reset();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Something went wrong.');
                    btn.innerHTML = 'Send Request';
                    btn.disabled = false;
                });
        }

        // Submit Sample
        function submitSample(e) {
            e.preventDefault();
            const btn = e.target.querySelector('button');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            btn.disabled = true;

            const formData = new FormData(e.target);

            fetch('send_sample.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(data => {
                    alert('Sample Request Sent! We will contact you with shipping invoice.');
                    closeSampleModal();
                    btn.innerHTML = 'Request Sample';
                    btn.disabled = false;
                    e.target.reset();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Something went wrong.');
                    btn.innerHTML = 'Request Sample';
                    btn.disabled = false;
                });
        }
    </script>

    <!-- Page 1: Front Cover -->
    <div class="page">
        <div class="cover-top">
            <img src="assets/images/logo.png" alt="APSS Logo" class="cover-logo">
            <div class="cover-company bold">APSS TRADESPHERE LLP</div>
            <div class="cover-tagline">Let's Connect the Globe</div>
            <?php if ($personalization_text): ?>
                <div class="personalization-text">
                    <?php echo $personalization_text; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="cover-middle">
            <div class="cover-title">
                <?php echo htmlspecialchars($product['name']); ?><br>BROCHURE
            </div>
            <div class="ribbon-container">
                <i class="fas fa-leaf ribbon-leaf"></i>
                <div class="ribbon-text">WE BALANCE OUR PLANET, LET US SOFTLY SUSTAIN THE EARTH</div>
            </div>
        </div>

        <div class="cover-bottom">
            <div class="cover-overlay-footer">
                APSS TradeSphere LLP |
                <?php
                if ($product['id'] == 'areca') {
                    echo "Premium Sustainable Tableware Exporters";
                } elseif ($product['id'] == 'turmeric') {
                    echo "Premium Quality Spices Exporters";
                } else {
                    echo "Premium Multi-Product Exporters";
                }
                ?>
                | India
            </div>
        </div>
    </div>

    <!-- Page 2: Company Profile -->


    <!-- Page 2: Company Profile -->
    <div class="page">
        <header class="global-header">
            <img src="assets/images/logo.png" alt="Logo">
            <div class="global-tagline">Let's Connect the Globe</div>
        </header>

        <div class="content-padding" style="padding-top: 15px; padding-bottom: 10px;">
            <!-- Formal About Us -->
            <div class="section-title" style="margin-bottom: 8px;">
                <h2>WHO WE ARE?</h2>
            </div>
            <div style="line-height: 1.6; color: #444; font-size: 13px; text-align: justify; margin-bottom: 20px;">
                <?php if (!empty($product['profile_text'])): ?>
                    <?php echo nl2br(htmlspecialchars($product['profile_text'])); ?>
                <?php else: ?>
                    <p style="margin-bottom: 12px;">
                        APSS TradeSphere LLP stands as a premier Indian merchant exporter, dedicated to bridging the
                        vital
                        gap between India’s finest sustainable manufacturers and the dynamic needs of the global
                        marketplace. Founded on the core pillars of integrity, reliability, and unparalleled quality, we
                        specialize in sourcing and supplying a diverse range of premium products, including eco-friendly
                        Areca Palm Leaf tableware, ethically sourced Birch wood cutlery, and high-potency Indian spices.
                    </p>
                    <p style="margin-bottom: 0;">
                        Our mission goes beyond simple trade; we aim to deliver excellence by ensuring that every single
                        item we export not only meets rigorous international safety and quality standards but also
                        authentically reflects the rich agricultural heritage and skilled craftsmanship of India. We
                        understand that in today’s fast-paced international trade environment, trust and consistency are
                        paramount. That is why we have established a robust, transparent supply chain network that
                        guarantees timely delivery and uncompromising quality.
                    </p>
                <?php endif; ?>
            </div>

            <!-- Personalized Conversation (Header Removed) -->
            <div
                style="line-height: 1.6; color: #444; font-size: 13px; text-align: justify; font-family: 'Montserrat', sans-serif; margin-top: 20px;">

                <?php if (!empty($product['welcome_message'])): ?>
                    <p style="margin-bottom: 10px;">
                        <strong>Hey <?php echo htmlspecialchars($visitor_name ?: 'Partner'); ?>,</strong>
                    </p>
                    <p>
                        <?php echo nl2br(htmlspecialchars($product['welcome_message'])); ?>
                    </p>
                <?php else: ?>

                    <?php if ($visitor_name && $visitor_name !== 'Guest'): ?>
                        <p style="margin-bottom: 10px;">
                            <strong>Hey
                                <?php echo htmlspecialchars($visitor_name); ?>,
                            </strong>
                        </p>
                        <p style="margin-bottom: 10px;">
                            Have you ever wondered who is truly standing behind your shipments? <br>
                            You might be asking, <em>"Is APSS TradeSphere just another exporter, or the partner I've been
                                looking for?"</em>
                        </p>
                        <p style="margin-bottom: 10px;">
                            Let me answer that, <strong>
                                <?php echo htmlspecialchars($visitor_name); ?>
                            </strong>. We aren't just
                            moving boxes; we are protecting your reputation. When you sleep, we are here in Shimoga and
                            Salem,
                            checking every item. You see, we believe that when you grow, we grow.
                        </p>
                    <?php else: ?>
                        <p style="margin-bottom: 10px;">
                            <strong>Hey there,</strong>
                        </p>
                        <p style="margin-bottom: 10px;">
                            Have you ever wondered who is truly standing behind your shipments? <br>
                            You might be asking, <em>"Is APSS TradeSphere just another exporter, or the partner I've been
                                looking for?"</em>
                        </p>
                        <p style="margin-bottom: 10px;">
                            Let us answer that. We aren't just moving boxes; we are protecting your reputation. We are here
                            on
                            the ground, checking every single item to ensure it's perfect.
                        </p>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Certifications Section -->
                <div
                    style="background: #fff; padding: 15px; border: 1px dashed #D98600; border-radius: 6px; margin-top: 15px;">
                    <p
                        style="margin-bottom: 8px; font-size: 12px; font-weight: 600; color: #D98600; text-align: center;">
                        "Do you have the paperwork to back this up?"
                    </p>
                    <p style="color: #555; font-size: 11px; text-align: center; margin-bottom: 0;">
                        Absolutely. We are fully government recognized (IEC, GST, MSME) and certified by the Spices
                        Board, APEDA, and FSSAI.
                    </p>
                    <div style="text-align: center; margin-top: 8px;">
                        <a href="https://apsstradesphere.com/business-policies" target="_blank"
                            style="color: #2E7D32; font-size: 11px; text-decoration: none;">
                            <i class="fas fa-check-circle"></i> Yes, verify our documents here
                        </a>
                    </div>
                </div>
            </div>

            <!-- Why Choose APSS Grid -->
            <div class="why-grid" style="margin-top: 25px; padding: 15px; gap: 15px;">
                <div class="why-item">
                    <div class="why-icon" style="font-size: 24px; margin-bottom: 6px;"><i
                            class="fas fa-search-plus"></i></div>
                    <div class="why-title" style="font-size: 11px;">100% PRE-SHIPMENT QC</div>
                    <div class="why-desc" style="font-size: 10px;">&lt;10-12% Moisture Control</div>
                </div>
                <div class="why-item">
                    <div class="why-icon" style="font-size: 24px; margin-bottom: 6px;"><i class="fas fa-ship"></i>
                    </div>
                    <div class="why-title" style="font-size: 11px;">INDUSTRIAL SCALABILITY</div>
                    <div class="why-desc" style="font-size: 10px;">From Small Pallet to Full Container</div>
                </div>
                <div class="why-item">
                    <div class="why-icon" style="font-size: 24px; margin-bottom: 6px;"><i class="fas fa-heart"></i>
                    </div>
                    <div class="why-title" style="font-size: 11px;">VARIETY & CUSTOMIZATION</div>
                    <div class="why-desc" style="font-size: 10px;">Tailored shapes & branding</div>
                </div>
            </div>
        </div>

        <footer class="global-footer">
            <div class="footer-item"><i class="fas fa-phone"></i> +91 87000 72233</div>
            <div class="footer-item"><i class="fas fa-envelope"></i> sales@apsstradesphere.com</div>
            <div class="footer-item"><i class="fas fa-globe"></i> www.apsstradesphere.com</div>
        </footer>
    </div>

    <!-- Page 3: Product Description & Why Choose -->
    <div class="page">
        <header class="global-header">
            <h4>
                <?php echo htmlspecialchars($product['name']); ?>
            </h4>
        </header>

        <div class="content-padding" style="padding-top: 15px; padding-bottom: 10px;">
            <!-- Section 1: Product Description -->
            <div class="section-title" style="margin-bottom: 10px;">
                <h2>ABOUT THE PRODUCT</h2>
            </div>
            <div
                style="font-size: 11px; line-height: 1.5; color: #444; margin-bottom: 15px; padding: 10px; background: #fff8f0; border-left: 3px solid #D98600; border-radius: 4px; text-align: justify;">
                <?php
                // Description - allow HTML tags like <br>
                $desc = isset($product['long_description']) && !empty($product['long_description'])
                    ? $product['long_description']
                    : $product['description'];
                echo $desc;
                ?>
            </div>

            <!-- Section 2: Why Choose From Us (Q&A Style) -->
            <div class="section-title" style="margin-bottom: 10px;">
                <h2>WHY CHOOSE FROM US,
                    <?php echo strtoupper($visitor_name && $visitor_name !== 'Guest' ? htmlspecialchars($visitor_name) : 'PARTNER'); ?>?
                </h2>
            </div>

            <!-- Personalized Q&A Block -->
            <div
                style="background: #F3F9F4; padding: 10px; border-radius: 6px; margin-bottom: 15px; border: 1px solid #C8E6C9;">
                <p style="font-weight: bold; color: #2E7D32; font-size: 11px; margin-bottom: 5px;">
                    <i class="fas fa-comment-dots"></i>
                    <?php if ($visitor_name && $visitor_name !== 'Guest'): ?>
                        Hey
                        <?php echo htmlspecialchars($visitor_name); ?>,
                    <?php else: ?>
                        Hey there,
                    <?php endif; ?>

                    <?php if ($product['id'] == 'areca'): ?>
                        do you know how our Areca Plates can't get mold?
                    <?php elseif ($product['id'] == 'turmeric'): ?>
                        do you know how we guarantee such high curcumin?
                    <?php else: ?>
                        do you know how we ensure consistent quality?
                    <?php endif; ?>
                </p>
                <p style="font-size: 10px; color: #444; line-height: 1.4; font-style: italic; margin: 0;">
                    <?php if (!empty($product['why_choose_custom'])): ?>
                        "<?php echo nl2br(htmlspecialchars($product['why_choose_custom'])); ?>"
                    <?php elseif ($product['id'] == 'areca'): ?>
                        "You might be worried about moisture damage during transit. It's a valid concern! That is
                        exactly why we shrink-wrap every single pack and include silica gel desiccants. We don't take
                        risks with your reputation, so you get fresh, clean plates every time."
                    <?php elseif ($product['id'] == 'turmeric'): ?>
                        "You might ask yourself, 'Is this color real?' We totally understand. Unlike others, we use a
                        traditional boiling process and sun-drying on clean mats. We test every batch in a lab, so you
                        pay for pure health, not artificial fillers."
                    <?php else: ?>
                        "You might wonder if the product will match the sample. We get it. That's why we implement a
                        strict 3-stage QC process: at the farm, at our warehouse, and before loading. We are your eyes
                        on the ground."
                    <?php endif; ?>
                </p>
            </div>

            <div class="profile-bullets" style="margin-top: 5px; margin-bottom: 5px;">
                <?php
                // Highlights Loop
                $highlights = isset($product['highlights']) ? $product['highlights'] : [];
                foreach ($highlights as $hl):
                    ?>
                    <div class="profile-bullet" style="margin-bottom: 8px;">
                        <div class="bullet-icon" style="font-size: 16px;"><i class="<?php echo $hl['icon']; ?>"></i>
                        </div>
                        <div class="bullet-text">
                            <strong style="font-size: 12px; color: #333;">
                                <?php echo htmlspecialchars($hl['title']); ?>
                            </strong>
                            <span style="font-size: 10px; display: block; line-height: 1.2;">
                                <?php echo htmlspecialchars($hl['desc']); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Optional Image at bottom to fill space -->
            <div
                style="margin-top: 5px; text-align: center; flex: 1; display: flex; align-items: center; justify-content: center; overflow: hidden; min-height: 0;">
                <?php
                // Logic to resolve Page 3 Bottom Image (Custom > Gallery Fallback)
                $rawP3 = $product['page3_image'] ?? '';
                $p3Path = is_array($rawP3) ? ($rawP3['path'] ?? '') : $rawP3;
                $p3Height = is_array($rawP3) ? ($rawP3['height'] ?? '500px') : '500px';

                $imgSrc = $p3Path;
                $heightStyle = $p3Height;

                // Fallback if no custom image
                if (!$imgSrc) {
                    $img0 = isset($product['gallery'][0]) ? $product['gallery'][0] : '';
                    $imgSrc = is_array($img0) ? ($img0['path'] ?? '') : $img0;
                    $heightStyle = '200px'; // Default for fallback
                }

                if ($imgSrc):
                    ?>
                    <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                        style="max-width: 100%; max-height: <?php echo htmlspecialchars($heightStyle); ?>; object-fit: contain; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                <?php endif; ?>
            </div>
        </div>

        <footer class="global-footer">
            <div class="footer-item"><i class="fas fa-phone"></i> +91 87000 72233</div>
            <div class="footer-item"><i class="fas fa-envelope"></i> sales@apsstradesphere.com</div>
            <div class="footer-item"><i class="fas fa-globe"></i> www.apsstradesphere.com</div>
        </footer>
    </div>

    <!-- Page 4: Product Portfolio -->
    <div class="page">
        <header class="global-header">
            <h4>
                <?php echo htmlspecialchars($product['name']); ?> Portfolio
            </h4>
        </header>

        <div class="content-padding" style="padding-top: 20px;">
            <div class="section-title" style="margin-bottom: 20px;">
                <h2>OUR COLLECTION</h2>
            </div>

            <div class="catalog-grid">
                <?php if ($product['id'] == 'turmeric'): ?>
                    <div
                        style="grid-column: 1 / -1; margin-bottom: 5px; color: #555; font-size: 11px; text-align: justify; line-height: 1.5; background: #fffde7; padding: 15px; border-left: 3px solid #fbc02d; border-radius: 4px;">
                        <strong style="color: #f57f17; display: block; margin-bottom: 5px; font-size: 14px;">Spotlight:
                            The
                            Gold of Erode & Salem</strong>
                        <p style="margin: 0;">
                            We exclusively source the world-famous <strong>"Erode Finger"</strong> and <strong>"Salem
                                Gem"</strong> varieties, celebrated globally for their high Curcumin content (3-5%) and
                            healing properties. The <strong>Erode variety</strong> is prized for its bright yellow hue
                            and
                            distinct aroma, making it the top choice for culinary brilliance. Meanwhile, the
                            <strong>Salem
                                variety</strong> is revered for its premium size and purity, often preferred for
                            medicinal
                            extractions and high-end retail packs. By choosing APSS, you get the authentic,
                            unadulterated
                            essence of India's finest turmeric belt.
                        </p>
                    </div>
                <?php endif; ?>

                <?php
                // Display first 4 items from gallery
                $gallery = isset($product['gallery']) ? $product['gallery'] : [];
                $count = 0;
                foreach ($gallery as $item):
                    if ($count >= 4)
                        break;
                    $i_params = is_array($item) ? $item : [];
                    $i_path = $i_params['path'] ?? $item;
                    $i_name = $i_params['name'] ?? '';
                    $i_desc = $i_params['description'] ?? '';
                    $i_height = $i_params['height'] ?? '280px'; // Get custom height or default
                    ?>
                    <div class="catalog-card">
                        <img src="<?php echo htmlspecialchars($i_path); ?>" alt="<?php echo htmlspecialchars($i_name); ?>"
                            style="height: <?php echo htmlspecialchars($i_height); ?>;">
                        <?php if ($i_name): ?>
                            <div class="caption">
                                <?php echo htmlspecialchars($i_name); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($i_desc): ?>
                            <div style="font-size: 11px; color: #555; margin-top: 5px; text-align: justify; line-height: 1.4;">
                                <?php echo nl2br(htmlspecialchars($i_desc)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php
                    $count++;
                endforeach;
                ?>
            </div>
        </div>

        <footer class="global-footer">
            <div class="footer-item"><i class="fas fa-phone"></i> +91 87000 72233</div>
            <div class="footer-item"><i class="fas fa-envelope"></i> sales@apsstradesphere.com</div>
            <div class="footer-item"><i class="fas fa-globe"></i> www.apsstradesphere.com</div>
        </footer>
    </div>

    <!-- Page 5: Top Performing (Specs - Dynamic) -->
    <div class="page">
        <header class="global-header">
            <h4><?php echo htmlspecialchars($product['performance_prefix'] ?? 'Top Performing'); ?>
                <?php echo strtoupper($product['name']); ?>
            </h4>
        </header>

        <div class="content-padding">
            <?php if ($product['id'] == 'turmeric'): ?>
                <div
                    style="margin-bottom: 10px; padding: 8px; background: #fff; border: 1px solid #eee; border-radius: 8px;">
                    <h5
                        style="margin: 0 0 5px 0; color: #D98600; font-size: 13px; border-bottom: 1px solid #f0f0f0; padding-bottom: 3px;">
                        Premium Varieties & Processing
                    </h5>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 10px; color: #555;">
                        <div>
                            <strong style="color: #333;">The Salem & Erode Advantage</strong>
                            <p style="margin: 3px 0 5px 0; text-align: justify; line-height: 1.3;">
                                We specialize in the <strong>Salem</strong> variety, known for its deep golden hue and
                                high
                                Curcumin (>4%), and the <strong>Erode</strong> variety, preferred for its distinct
                                aroma.
                                Both are sourced directly from GI-tagged regions to ensure authentic flavor.
                            </p>
                            <strong style="color: #333;">Available Forms</strong>
                            <ul style="margin: 3px 0 0 15px; padding: 0; line-height: 1.3;">
                                <li>Double/Single Polished Fingers</li>
                                <li>Premium Mother Bulbs (Gatta)</li>
                                <li>High-Mesh Curcumin Powder</li>
                            </ul>
                        </div>

                        <div>
                            <strong style="color: #333;">Rigorous Quality Protocol</strong>
                            <p style="margin: 3px 0 5px 0; text-align: justify; line-height: 1.3;">
                                Our rigorous mechanical drying and double-polishing process ensures <strong>Zero
                                    Moisture
                                    Risk</strong> and extended shelf life. Every batch is tested for:
                            </p>
                            <div style="background: #fdf8f0; padding: 5px; border-radius: 4px; border: 1px dashed #D98600;">
                                <ul style="margin: 0 0 0 15px; padding: 0; line-height: 1.3;">
                                    <li>Curcumin Percentage Verification</li>
                                    <li>Spices Board Certified Purity</li>
                                    <li>No Artificial Fillers or Colors</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="feature-grid" style="margin-bottom: 10px; gap: 10px;">
                <?php
                $specs = isset($product['specs']) ? $product['specs'] : [
                    ['icon' => 'fas fa-leaf', 'title' => 'TRULY SUSTAINABLE', 'desc' => '100% Compostable'],
                    ['icon' => 'fas fa-shield-alt', 'title' => '100% FOOD SAFE', 'desc' => 'No BPA, Toxins or Chemicals']
                ];

                foreach ($specs as $spec):
                    ?>
                    <div class="feature-box" style="padding: 8px;">
                        <i class="<?php echo $spec['icon']; ?> feature-icon"></i>
                        <div>
                            <div class="bold">
                                <?php echo htmlspecialchars($spec['title']); ?>
                            </div>
                            <div style="font-size: 11px; color: #666;">
                                <?php echo htmlspecialchars($spec['desc']); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="section-title" style="margin-top: 10px; margin-bottom: 10px;">
                <h3>Our Collection</h3>
            </div>
            <div class="catalog-grid" style="gap: 10px;">
                <?php
                // Check for Performance Gallery (Page 5 specific)
                $perfGallery = isset($product['performance_gallery']) ? $product['performance_gallery'] : [];
                // Filter out empty entries
                $perfGallery = array_filter($perfGallery);

                if (!empty($perfGallery)) {
                    // Use Specific Performance Images
                    foreach ($perfGallery as $pImg) {
                        if ($pImg) {
                            // Safe handling for string or object
                            $src = is_array($pImg) ? ($pImg['path'] ?? '') : $pImg;
                            $h = is_array($pImg) ? ($pImg['height'] ?? '280px') : '280px';

                            if ($src) {
                                echo '<div class="catalog-card">';
                                echo '<img src="' . htmlspecialchars($src) . '" alt="Performance Detail" style="height: ' . htmlspecialchars($h) . ';">';
                                echo '</div>';
                            }
                        }
                    }
                } else {
                    // Fallback to Main Gallery (Remaining Items or Reuse)
                    $max_gallery = isset($product['gallery']) ? count($product['gallery']) : 0;
                    $shown = 0;

                    // Logic: If plenty images (Areca), start at 6 (remainders). If few (Turmeric), start at 0 (reuse).
                    $startIndex = ($max_gallery > 6) ? 6 : 0;
                    $endIndex = $startIndex + 2;

                    // Show items
                    for ($i = $startIndex; $i < $endIndex; $i++) {
                        if ($i >= $max_gallery)
                            break;
                        $item = $product['gallery'][$i];
                        if ($item) {
                            // Normalize to object
                            $path = is_array($item) ? ($item['path'] ?? '') : $item;
                            $name = is_array($item) && !empty($item['name']) ? $item['name'] : "";
                            $desc = is_array($item) ? ($item['description'] ?? '') : '';

                            if ($path) {
                                echo '<div class="catalog-card">';
                                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                                if (in_array($ext, ['mp4', 'webm', 'ogg', 'mov'])) {
                                    echo '<video controls><source src="' . htmlspecialchars($path) . '">Your browser does not support the video tag.</video>';
                                } else {
                                    $h = is_array($item) ? ($item['height'] ?? '280px') : '280px';
                                    echo '<img src="' . htmlspecialchars($path) . '" alt="' . htmlspecialchars($name) . '" style="height: ' . htmlspecialchars($h) . ';">';
                                }
                                if ($name)
                                    echo '<div class="caption">' . htmlspecialchars($name) . '</div>';
                                if ($desc)
                                    echo '<div style="font-size: 10px; color: #777; text-align: center; margin-top: 2px;">' . htmlspecialchars($desc) . '</div>';
                                echo '</div>';
                                $shown++;
                            }
                        }
                    }
                    if ($shown == 0) {
                        echo '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #888; font-style: italic;">Complete product collection coming soon.</div>';
                    }
                }
                ?>
            </div>

            <!-- New 6 Suta+ Description Block -->
            <?php if (stripos($product['name'], 'Makhana') !== false): ?>
                <div
                    style="background: #f1f8e9; border-left: 4px solid #33691e; padding: 15px; margin-top: 20px; border-radius: 4px;">
                    <h4 style="color: #33691e; margin-top: 0; margin-bottom: 5px; font-size: 14px;">Why our Top
                        Performing
                        Grade?</h4>
                    <p style="margin: 0; font-size: 12px; color: #444; line-height: 1.5; text-align: justify;">
                        This isn't just standard Makhana. We proudly present our <strong>6 Suta+ Platinum
                            Grade</strong>—the
                        largest and most premium kernel available in the market.
                        Hand-selected for perfect roundness and brilliant white color, these fox nuts offer superior
                        expansion once roasted, delivering more volume and a better crunch.
                        Ideal for luxury gifting and high-end retail packs, this grade represents the pinnacle of
                        quality
                        from Bihar.
                    </p>
                </div>
            <?php endif; ?>

            <!-- Areca Specific Description Block -->
            <?php if (stripos($product['name'], 'Areca') !== false): ?>
                <div
                    style="background: #e8f5e9; border-left: 4px solid #2e7d32; padding: 15px; margin-top: 20px; border-radius: 4px;">
                    <h4 style="color: #2e7d32; margin-top: 0; margin-bottom: 5px; font-size: 14px;">Why our
                        Eco-Tableware
                        Leads the Market?</h4>
                    <p style="margin: 0; font-size: 12px; color: #444; line-height: 1.5; text-align: justify;">
                        Our Areca Palm Leaf plates are not just biodegradable; they are nature’s own engineering at its
                        finest.
                        Unlike soggy paper plates or toxic plastic, our tableware creates a premium dining experience
                        with a
                        sturdy, wood-like texture that can handle hot soups and heavy gravies without leaking.
                        Completely chemical-free, durable, and compostable in 90 days, they are the preferred choice for
                        eco-conscious caterers and high-end events across Europe and the USA.
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <footer class="global-footer">
            <div class="footer-item"><i class="fas fa-phone"></i> +91 87000 72233</div>
            <div class="footer-item"><i class="fas fa-envelope"></i> sales@apsstradesphere.com</div>
            <div class="footer-item"><i class="fas fa-globe"></i> www.apsstradesphere.com</div>
        </footer>
    </div>

    <!-- Page 6: Performance Proof -->
    <div class="page">
        <header class="global-header">
            <h4>Guaranteed Safety & Performance</h4>
        </header>

        <div class="content-padding">
            <?php if ($product['id'] == 'areca'): ?>
                <div class="icon-bar">
                    <div class="icon-item">
                        <div class="icon-circle"><i class="fas fa-fire-alt"></i></div>
                        <div class="icon-label">Heat Resistant</div>
                    </div>
                    <div class="icon-item">
                        <div class="icon-circle"><i class="fas fa-tint-slash"></i></div>
                        <div class="icon-label">Leak Resistant</div>
                    </div>
                    <div class="icon-item">
                        <div class="icon-circle"><i class="fas fa-recycle"></i></div>
                        <div class="icon-label">Eco Responsible</div>
                    </div>
                </div>
            <?php endif; ?>

            <div style="margin-top: 40px;">
                <?php
                $videoList = [];
                // Primary Video
                if (!empty($product['qa_video'])) {
                    $videoList[] = $product['qa_video'];
                }
                // Check if title field is actually a URL (User safeguard)
                $pTitle = isset($product['qa_video_title']) ? $product['qa_video_title'] : '';
                if (!empty($pTitle) && (strpos($pTitle, 'http') === 0)) {
                    $videoList[] = $pTitle;
                    $videoTitle = 'Quality Assurance Video'; // Fallback title
                } elseif (!empty($pTitle)) {
                    $videoTitle = $pTitle;
                } else {
                    $videoTitle = 'Quality Assurance Stress Test';
                }
                // Secondary Video field (future proofing)
                if (isset($product['qa_video_2']) && !empty($product['qa_video_2'])) {
                    $videoList[] = $product['qa_video_2'];
                }

                // Display Title (Once)
                echo '<h4 style="margin-bottom: 15px; color: #444;">' . htmlspecialchars($videoTitle) . '</h4>';

                foreach ($videoList as $index => $videoSrc):
                    $isYouTube = (strpos($videoSrc, 'youtube.com') !== false || strpos($videoSrc, 'youtu.be') !== false);
                    $isLocal = (!$isYouTube && !empty($videoSrc)); // assume local if not YT
                
                    if ($isYouTube):
                        // Extract Video ID (Enhanced for Studio links)
                        $video_id = '';
                        if (preg_match('/(?:(?:[a-z0-9-]+\.)?youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?|shorts|video)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $videoSrc, $matches)) {
                            $video_id = $matches[1];
                        }
                        ?>
                        <!-- YouTube Embed -->
                        <div class="video-container"
                            style="position: relative; padding-bottom: 45%; height: 0; overflow: hidden; max-width: 85%; margin: 0 auto 10px auto; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <iframe src="https://www.youtube.com/embed/<?php echo $video_id; ?>" frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></iframe>
                        </div>

                        <!-- Print Fallback for YouTube -->
                        <div class="video-qr-fallback">
                            <h4>See Video
                                <?php echo $index + 1; ?>
                            </h4>
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($videoSrc); ?>"
                                alt="Scan to watch video">
                            <p style="font-size: 12px; color: #666; margin-top: 10px;">Scan to watch on YouTube.</p>
                        </div>

                    <?php elseif (!empty($videoSrc) && $isLocal): ?>
                        <!-- Local Video Player -->
                        <div class="video-container video-wrapper" style="margin-bottom: 15px; text-align: center;">
                            <video controls style="max-height: 250px; width: 85%; border-radius: 6px;">
                                <source src="<?php echo htmlspecialchars($videoSrc); ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>

                        <!-- Print Fallback Local -->
                        <div class="video-qr-fallback">
                            <h4>See Video
                                <?php echo $index + 1; ?>
                            </h4>
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode('https://yourwebsite.com/' . $videoSrc); ?>"
                                alt="Scan to watch video">
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <!-- QA Images Gallery -->
                <?php if (!empty($product['qa_gallery']) && count($product['qa_gallery']) > 0): ?>
                    <div style="display: flex; gap: 10px; margin-top: 20px; flex-wrap: wrap; justify-content: center;">
                        <?php foreach ($product['qa_gallery'] as $qaItem):
                            $qPath = is_array($qaItem) ? ($qaItem['path'] ?? '') : $qaItem;
                            // Default height logic preserves legacy behavior if not specified
                            $defH = ($product['id'] == 'turmeric') ? 'auto' : '150px';
                            $qHeight = is_array($qaItem) ? ($qaItem['height'] ?? $defH) : $defH;

                            if (!empty($qPath) && file_exists($qPath)):
                                ?>
                                <?php if ($product['id'] == 'turmeric'): ?>
                                    <img src="<?php echo htmlspecialchars($qPath); ?>"
                                        style="width: 100%; height: <?php echo htmlspecialchars($qHeight); ?>; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 10px; object-fit: contain;">
                                <?php else: ?>
                                    <img src="<?php echo htmlspecialchars($qPath); ?>"
                                        style="width: 48%; height: <?php echo htmlspecialchars($qHeight); ?>; object-fit: cover; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                <?php endif; ?>
                            <?php endif; endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="text-center"
                style="margin-top: 40px; color: #555; background: #e8f5e9; padding: 15px; border-radius: 8px; overflow: visible; height: auto; word-wrap: break-word;">
                <p style="margin: 0; line-height: 1.5;">
                    <strong style="color: #2E7D32;">Did you know?</strong>
                    <?php
                    echo isset($product['fun_fact'])
                        ? htmlspecialchars($product['fun_fact'])
                        : "Areca plates withstand temperatures up to 200°C for short durations and are completely non-toxic even when heated.";
                    ?>
                </p>
            </div>
        </div>

        <footer class="global-footer">
            <div class="footer-item"><i class="fas fa-phone"></i> +91 87000 72233</div>
            <div class="footer-item"><i class="fas fa-envelope"></i> sales@apsstradesphere.com</div>
            <div class="footer-item"><i class="fas fa-globe"></i> www.apsstradesphere.com</div>
        </footer>
    </div>

    <!-- ... Rest of pages ... -->
    <!-- Page 9: Back Cover -->
    <div class="page" style="display: flex; flex-direction: column;">
        <header class="global-header">
            <h4>Your Supply Chain Solution</h4>
            <div style="font-size: 12px; color: #666;">FOR ECO-FRIENDLY TABLEWARE</div>
        </header>

        <div class="achievements-bar">
            <!-- Items... -->
            <div class="achievement-item">
                <i class="fas fa-star achievement-icon"></i>
                <div class="achievement-text">New In 2025</div>
            </div>
            <div class="achievement-item">
                <i class="fas fa-award achievement-icon"></i>
                <div class="achievement-text">Premium Exports</div>
            </div>
        </div>

        <div class="world-map-bg"></div>
        <div style="z-index: 1; text-align: center; margin-top: 50px;">
            <i class="fas fa-map-marker-alt" style="color: #D98600; font-size: 24px;"></i>
            <div style="font-family: 'Playfair Display', serif; font-size: 20px; margin-top: 10px;">Trusted Partners
                in
                35+ Countries</div>
        </div>

        <div style="flex: 1; display: flex; align-items: flex-end;">
            <div class="contact-box" style="width: 100%; box-sizing: border-box;">
                <div class="gold-seal">CERTIFIED<br>EXCELLENCE</div>
                <h2>CONTACT US TODAY</h2>
                <ul style="list-style: none; padding: 0; font-size: 13px; color: #555; margin-bottom: 20px;">
                    <li>Partner with a premier name in sustainable exports</li>
                    <li>Secure consistent, high-quality supply for your market</li>
                </ul>
                <a href="mailto:sales@apsstradesphere.com" class="cta-btn">GET A QUOTE</a>
            </div>
        </div>

        <footer class="global-footer">
            <div class="footer-item"><i class="fas fa-phone"></i> +91 87000 72233</div>
            <div class="footer-item"><i class="fas fa-envelope"></i> sales@apsstradesphere.com</div>
            <div class="footer-item"><i class="fas fa-globe"></i> www.apsstradesphere.com</div>
            <div class="footer-item" style="margin-left: auto;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=50x50&data=https://apsstradesphere.com"
                    style="width: 40px; height: 40px; background: white;">
            </div>
        </footer>
    </div>

    <!-- Lightbox Modal -->
    <div id="lightbox" class="lightbox" onclick="closeLightbox()">
        <span class="lightbox-close">&times;</span>
        <img id="lightbox-img" src="">
    </div>

    <style>
        /* Lightbox Styles */
        .lightbox {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            backdrop-filter: blur(5px);
        }

        .lightbox img {
            max-width: 90%;
            max-height: 90%;
            border-radius: 4px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);
            animation: zoomIn 0.3s ease;
        }

        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 30px;
            font-size: 40px;
            color: white;
            cursor: pointer;
            z-index: 100000;
        }

        .zoomable-img {
            cursor: zoom-in;
            transition: transform 0.2s ease;
        }

        .zoomable-img:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        @keyframes zoomIn {
            from {
                transform: scale(0.9);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>

    <script>
        function openLightbox(src) {
            const lightbox = document.getElementById('lightbox');
            const lightboxImg = document.getElementById('lightbox-img');
            lightboxImg.src = src;
            lightbox.style.display = 'flex';
        }

        function closeLightbox() {
            document.getElementById('lightbox').style.display = 'none';
        }

        // Close on Esc key
        document.addEventListener('keydown', function (event) {
            if (event.key === "Escape") {
                closeLightbox();
            }
        });

        // Add click listener to all catalog images
        document.addEventListener('DOMContentLoaded', function () {
            // Target all relevant product images
            const selectors = [
                '.catalog-card img',
                '.portfolio-img',
                'img[src*="assets/images/products/"]',
                'img[src*="assets/images/performance/"]',
                'img[src*="assets/images/qa/"]'
            ];

            // Collect and process images
            const images = document.querySelectorAll(selectors.join(', '));
            images.forEach(img => {
                // exclude logo/icons
                if (img.classList.contains('cover-logo') || img.classList.contains('global-header-img')) return;

                img.classList.add('zoomable-img');
                img.onclick = function (e) {
                    e.stopPropagation(); // Prevent bubbling
                    openLightbox(this.src);
                }
            });
        });
    </script>

    <script>
        // Disable Right Click for Content Protection (Except on Localhost)
        if (window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
            document.addEventListener('contextmenu', event => event.preventDefault());
            document.onkeydown = function (e) {
                // Disable F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U
                if (e.keyCode == 123) return false;
                if (e.ctrlKey && e.shiftKey && (e.keyCode == 'I'.charCodeAt(0) || e.keyCode == 'J'.charCodeAt(0))) return false;
                if (e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) return false;
            }
        }
    </script>
</body>

</html>