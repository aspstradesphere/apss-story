<?php
// Interactive Catalog Wrapper
session_start();
$json_data = file_get_contents('data/story_content.json');
$data = json_decode($json_data, true);
$products = isset($data['products']) ? $data['products'] : [];

// Get current ID if passed, else default
$current_id = isset($_GET['id']) ? $_GET['id'] : (count($products) > 0 ? $products[0]['id'] : 'areca');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APSS TradeSphere - Interactive Catalog</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            /* No scroll on wrapper */
            font-family: 'Montserrat', sans-serif;
            background: #f0f0f0;
        }

        /* Top Bar */
        .top-bar {
            height: 60px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 100;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: #2E7D32;
            text-decoration: none;
        }

        .brand img {
            height: 30px;
        }

        .filter-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-label {
            font-size: 14px;
            color: #666;
            display: none;
            /* Hidden on small mobile */
        }

        @media(min-width: 600px) {
            .filter-label {
                display: block;
            }
        }

        select {
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #ddd;
            font-family: inherit;
            font-size: 14px;
            background: #f9f9f9;
            cursor: pointer;
            outline: none;
        }

        select:focus {
            border-color: #2E7D32;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .btn-action {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #f0f0f0;
            color: #555;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-action:hover {
            background: #e0e0e0;
            color: #2E7D32;
        }

        /* Iframe Container */
        .iframe-container {
            height: calc(100% - 60px);
            width: 100%;
        }

        iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }
    </style>
</head>

<body>

    <div class="top-bar">
        <a href="story.php" class="brand">
            <img src="assets/images/logo.png" alt="Logo">
            <span>APSS Catalog</span>
        </a>

        <div class="filter-container">
            <span class="filter-label">Viewing Product:</span>
            <select id="productSelect" onchange="updateCatalog(this.value)">
                <?php foreach ($products as $p): ?>
                    <option value="<?php echo htmlspecialchars($p['id']); ?>" <?php echo ($p['id'] == $current_id) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="actions">
            <!-- Print Button (Triggers iframe print) -->
            <a href="#" class="btn-action" onclick="printCatalog(); return false;" title="Print Catalog">
                <i class="fas fa-print"></i>
            </a>
            <!-- Home -->
            <a href="story.php" class="btn-action" title="Back to Story">
                <i class="fas fa-home"></i>
            </a>
        </div>
    </div>

    <div class="iframe-container">
        <iframe id="catalogFrame" src="catalog_view.php?id=<?php echo htmlspecialchars($current_id); ?>"></iframe>
    </div>

    <script>
        function updateCatalog(productId) {
            var frame = document.getElementById('catalogFrame');
            // Add fade effect if desired, or just reload
            frame.src = 'catalog_view.php?id=' + productId;

            // Update URL query param without reload (pushState)
            var newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?id=' + productId;
            window.history.pushState({ path: newUrl }, '', newUrl);
        }

        function printCatalog() {
            var frame = document.getElementById('catalogFrame');
            frame.contentWindow.focus();
            frame.contentWindow.print();
        }
    </script>

</body>

</html>