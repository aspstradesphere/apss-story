<?php
// Product Details Page
session_start();

// Gatekeeper Check
if (!isset($_SESSION['visitor_name'])) {
    header("Location: welcome.php");
    exit;
}

// Load Data
$dataFile = 'data/story_content.json';
if (file_exists($dataFile)) {
    $data = json_decode(file_get_contents($dataFile), true);
} else {
    die("Data file missing.");
}

// Get Product ID
$id = isset($_GET['id']) ? $_GET['id'] : '';
$product = null;

// Find product
if (!empty($data['products'])) {
    foreach ($data['products'] as $p) {
        if ($p['id'] == $id) {
            $product = $p;
            break;
        }
    }
}

// If product not found, redirect to story page
if (!$product) {
    header("Location: story.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo htmlspecialchars($product['name']); ?> - Catalog | APSS TradeSphere
    </title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Gilda+Display&display=swap"
        rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/story.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Hero Section (Original Card) */
        .detail-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-wrap: wrap;
            margin-top: 20px;
            margin-bottom: 60px;
        }

        .detail-image {
            flex: 1;
            min-width: 300px;
            height: 400px;
            object-fit: cover;
        }

        .detail-content {
            flex: 1;
            min-width: 300px;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Generic Section Styles */
        .product-section {
            padding: 60px 0;
        }

        .section-title-custom {
            font-family: 'Gilda Display', serif;
            font-size: 36px;
            color: #D98600;
            margin-bottom: 20px;
            text-align: center;
        }

        .section-subtitle-custom {
            text-align: center;
            color: #666;
            margin-bottom: 40px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Why Choose Us Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .feature-box {
            background: white;
            padding: 30px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }

        .feature-box:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 40px;
            color: #D98600;
            margin-bottom: 20px;
        }

        .feature-title {
            font-size: 20px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        /* Top Performing Products (Gallery) */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .product-item {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .product-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .product-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .product-item-body {
            padding: 15px;
            text-align: center;
        }

        .product-item-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }

        .product-item-desc {
            font-size: 13px;
            color: #777;
        }

        .back-link {
            color: #666;
            text-decoration: none;
            margin-bottom: 20px;
            display: inline-block;
        }

        .back-link:hover {
            color: #D98600;
        }

        /* Description Text */
        .description-text {
            font-size: 18px;
            line-height: 1.8;
            color: #444;
            max-width: 900px;
            margin: 0 auto;
            text-align: center;
        }
    </style>
</head>

<body>

    <header class="story-header" style="padding: 20px 0; min-height: auto;">
        <div class="container">
            <h1 style="font-size: 24px; margin: 0;">
                <?php echo htmlspecialchars($data['header']['title']); ?>
            </h1>
        </div>
    </header>

    <div class="container" style="padding-top: 40px;">
        <a href="story.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Story Page</a>

        <!-- 1. Hero / Main Info -->
        <div class="detail-card">
            <img src="<?php echo htmlspecialchars($product['image']); ?>"
                alt="<?php echo htmlspecialchars($product['name']); ?>" class="detail-image">
            <div class="detail-content">
                <h1 style="font-family: var(--font-secondary); font-size: 36px; color: #D98600; margin-bottom: 10px;">
                    <?php echo htmlspecialchars($product['name']); ?>
                </h1>
                <p
                    style="font-weight: 600; font-size: 14px; color: #888; letter-spacing: 1px; margin-bottom: 20px; text-transform: uppercase;">
                    ID:
                    <?php echo htmlspecialchars($product['id']); ?>
                </p>
                <div style="font-size: 18px; line-height: 1.6; color: #555; margin-bottom: 30px;">
                    <?php echo htmlspecialchars($product['description']); ?>
                </div>

                <?php if (!empty($product['pdf'])): ?>
                    <a href="<?php echo htmlspecialchars($product['pdf']); ?>" class="btn btn-primary" download
                        style="margin-right: 15px;">
                        <i class="fas fa-file-pdf"></i> Download PDF Catalog
                    </a>
                <?php endif; ?>

                <a href="catalog_view.php?id=<?php echo urlencode($product['id']); ?>" class="btn btn-primary"
                    style="background: #2E7D32; width: 100%; text-align: center; margin-top: 10px;">
                    <i class="fas fa-eye"></i> VIEW INTERACTIVE BROCHURE
                </a>
            </div>
        </div>
    </div>



    <footer class="site-footer text-center">
        <div class="container">
            <p>&copy;
                <?php echo date('Y'); ?> APSS TradeSphere. All Rights Reserved.
            </p>
        </div>
    </footer>

</body>

</html>