<?php
// Catalog Editor - Dedicated Admin Page
// Allows selecting a specific catalog to update

session_start();
$dataFile = 'data/story_content.json';
$message = '';

// --- Authentication ---
if (!isset($_SESSION['is_admin']) && isset($_POST['password'])) {
    if ($_POST['password'] === 'apss2024') {
        $_SESSION['is_admin'] = true;
    } else {
        $message = 'Incorrect Password';
    }
}

if (!isset($_SESSION['is_admin'])) {
    ?>
    <!DOCTYPE html>
    <html>

    <head>
        <title>Login - Catalog Editor</title>
        <style>
            body {
                font-family: sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                background: #f4f4f4;
            }

            .login-box {
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                width: 300px;
                text-align: center;
            }

            input {
                padding: 10px;
                margin-bottom: 10px;
                width: 100%;
                box-sizing: border-box;
            }

            button {
                padding: 10px;
                width: 100%;
                background: #D98600;
                color: white;
                border: none;
                cursor: pointer;
                border-radius: 4px;
            }

            button:hover {
                background: #b77100;
            }
        </style>
    </head>

    <body>
        <div class="login-box">
            <h2>Catalog Editor</h2>
            <?php if ($message)
                echo "<p style='color:red'>$message</p>"; ?>
            <form method="post">
                <input type="password" name="password" placeholder="Enter Password" required>
                <button type="submit">Login</button>
            </form>
        </div>
    </body>

    </html>
    <?php
    exit;
}

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: catalog-editor.php');
    exit;
}

// --- Data Handling ---
$data = json_decode(file_get_contents($dataFile), true);
if (!isset($data['products'])) {
    $data['products'] = [];
}

// Handle Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    $index = (int) $_POST['product_index'];

    // Check if valid index
    if (isset($data['products'][$index])) {
        // Update Fields
        $data['products'][$index]['name'] = $_POST['name'];
        $data['products'][$index]['id'] = $_POST['id'];
        $data['products'][$index]['description'] = $_POST['description'];

        // Dynamic Content Fields
        $data['products'][$index]['profile_text'] = $_POST['profile_text'] ?? '';
        $data['products'][$index]['welcome_message'] = $_POST['welcome_message'] ?? '';
        $data['products'][$index]['why_choose_custom'] = $_POST['why_choose_custom'] ?? '';
        $data['products'][$index]['performance_prefix'] = $_POST['performance_prefix'] ?? '';

        // Page 3 Bottom Image Upload
        // Page 3 Bottom Image Upload & Height
        $currentP3 = $data['products'][$index]['page3_image'] ?? null;
        $p3Path = is_array($currentP3) ? ($currentP3['path'] ?? '') : ($currentP3 ?? '');
        $p3Height = isset($_POST['page3_height']) ? $_POST['page3_height'] : (is_array($currentP3) ? ($currentP3['height'] ?? '500px') : '500px');

        if (isset($_FILES['page3_img_file']) && $_FILES['page3_img_file']['error'] === 0) {
            $target_dir = "assets/images/products/";
            if (!file_exists($target_dir))
                mkdir($target_dir, 0777, true);
            $filename = time() . "_p3_" . basename($_FILES["page3_img_file"]["name"]);
            $filename = preg_replace("/[^a-zA-Z0-9.\-_]/", "_", $filename);
            if (move_uploaded_file($_FILES["page3_img_file"]["tmp_name"], $target_dir . $filename)) {
                $p3Path = $target_dir . $filename;
            }
        }

        if ($p3Path) {
            $data['products'][$index]['page3_image'] = [
                'path' => $p3Path,
                'height' => $p3Height
            ];
        }

        $data['products'][$index]['fun_fact'] = isset($_POST['fun_fact']) ? $_POST['fun_fact'] : '';

        // Handle Highlights (Page 3)
        $data['products'][$index]['highlights'] = [];
        for ($i = 0; $i < 3; $i++) {
            // Only save if title is present to avoid empty junk
            $data['products'][$index]['highlights'][] = [
                'icon' => $_POST["hl_icon_$i"] ?? 'fas fa-check',
                'title' => $_POST["hl_title_$i"] ?? '',
                'desc' => $_POST["hl_desc_$i"] ?? ''
            ];
        }

        // Handle Specs (Page 5)
        $data['products'][$index]['specs'] = [];
        for ($i = 0; $i < 2; $i++) {
            $data['products'][$index]['specs'][] = [
                'icon' => $_POST["spec_icon_$i"] ?? 'fas fa-star',
                'title' => $_POST["spec_title_$i"] ?? '',
                'desc' => $_POST["spec_desc_$i"] ?? ''
            ];
        }

        // Create gallery array if it doesn't exist
        if (!isset($data['products'][$index]['gallery'])) {
            $data['products'][$index]['gallery'] = [];
        }

        // Handle Gallery Images Upload (Array of 8 dedicated slots)
        for ($i = 0; $i < 8; $i++) {
            // Check for Delete
            if (isset($_POST["delete_gallery_$i"])) {
                unset($data['products'][$index]['gallery'][$i]);
                continue;
            }

            $fileKey = "gallery_image_$i";
            $nameKey = "gallery_name_$i";
            $descKey = "gallery_desc_$i";

            // Get Current Path securely
            $currentData = isset($data['products'][$index]['gallery'][$i]) ? $data['products'][$index]['gallery'][$i] : null;
            $currentPath = '';
            if (is_array($currentData) && isset($currentData['path'])) {
                $currentPath = $currentData['path'];
            } elseif (is_string($currentData)) {
                $currentPath = $currentData;
            }

            $newPath = $currentPath;

            // Handle New File
            if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === 0) {
                $target_dir = "assets/images/products/";
                if (!file_exists($target_dir))
                    mkdir($target_dir, 0777, true);

                $filename = basename($_FILES[$fileKey]["name"]);
                $filename = preg_replace("/[^a-zA-Z0-9.]/", "_", $filename);
                $target_file = $target_dir . time() . "_" . $i . "_" . $filename;

                if (move_uploaded_file($_FILES[$fileKey]["tmp_name"], $target_file)) {
                    $newPath = $target_file;
                }
            }

            // Save if we have a path
            if ($newPath) {
                $name = isset($_POST[$nameKey]) ? $_POST[$nameKey] : (is_array($currentData) ? ($currentData['name'] ?? '') : '');
                $desc = isset($_POST[$descKey]) ? $_POST[$descKey] : (is_array($currentData) ? ($currentData['description'] ?? '') : '');
                $height = isset($_POST["gallery_height_$i"]) ? $_POST["gallery_height_$i"] : (is_array($currentData) ? ($currentData['height'] ?? '280px') : '280px');

                $data['products'][$index]['gallery'][$i] = [
                    'path' => $newPath,
                    'name' => $name,
                    'description' => $desc,
                    'height' => $height
                ];
            }
        }

        // Handle QA Video URL (YouTube)
        if (isset($_POST['qa_video_url'])) {
            $data['products'][$index]['qa_video'] = trim($_POST['qa_video_url']);
        }
        if (isset($_POST['qa_video_title'])) {
            $data['products'][$index]['qa_video_title'] = trim($_POST['qa_video_title']);
        }
        if (isset($_POST['qa_video_2'])) {
            $data['products'][$index]['qa_video_2'] = trim($_POST['qa_video_2']);
        }

        if (isset($_POST['qa_video_url']) || isset($_POST['qa_video_title'])) {
            $message .= " Video details updated.";
        }

        // Handle QA Images Upload (Array of 4 dedicated slots for QA section)
        if (!isset($data['products'][$index]['qa_gallery'])) {
            $data['products'][$index]['qa_gallery'] = [];
        }

        for ($i = 0; $i < 4; $i++) {
            if (isset($_POST["delete_qa_$i"])) {
                unset($data['products'][$index]['qa_gallery'][$i]);
                continue;
            }

            // Get existing or new path
            $currentData = isset($data['products'][$index]['qa_gallery'][$i]) ? $data['products'][$index]['qa_gallery'][$i] : null;
            $finalPath = is_array($currentData) ? ($currentData['path'] ?? '') : ($currentData ?? '');

            $fileKey = "qa_image_$i";
            if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === 0) {
                $target_dir = "assets/images/qa/";
                if (!file_exists($target_dir))
                    mkdir($target_dir, 0777, true);

                $filename = basename($_FILES[$fileKey]["name"]);
                $filename = preg_replace("/[^a-zA-Z0-9.]/", "_", $filename);
                $target_file = $target_dir . time() . "_qa_" . $i . "_" . $filename;

                if (move_uploaded_file($_FILES[$fileKey]["tmp_name"], $target_file)) {
                    $finalPath = $target_file;
                    $message .= " QA Image " . ($i + 1) . " uploaded.";
                }
            }

            // Save if we have a path
            if ($finalPath) {
                $height = $_POST["qa_height_$i"] ?? (is_array($currentData) ? ($currentData['height'] ?? '280px') : '280px');
                $data['products'][$index]['qa_gallery'][$i] = [
                    'path' => $finalPath,
                    'height' => $height
                ];
            }
        }

        // Handle Performance Images (Page 5) - Array of 4 slots
        if (!isset($data['products'][$index]['performance_gallery']))
            $data['products'][$index]['performance_gallery'] = [];
        for ($i = 0; $i < 4; $i++) {
            if (isset($_POST["delete_perf_$i"])) {
                unset($data['products'][$index]['performance_gallery'][$i]);
                continue;
            }

            $currentData = isset($data['products'][$index]['performance_gallery'][$i]) ? $data['products'][$index]['performance_gallery'][$i] : null;
            $finalPath = is_array($currentData) ? ($currentData['path'] ?? '') : ($currentData ?? '');

            $fileKey = "perf_image_$i";
            if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === 0) {
                $target_dir = "assets/images/performance/";
                if (!file_exists($target_dir))
                    mkdir($target_dir, 0777, true);
                $filename = time() . "_perf_" . $i . "_" . basename($_FILES[$fileKey]["name"]);
                $filename = preg_replace("/[^a-zA-Z0-9.\-_]/", "_", $filename);
                if (move_uploaded_file($_FILES[$fileKey]["tmp_name"], $target_dir . $filename)) {
                    $finalPath = $target_dir . $filename;
                }
            }

            // Save if we have a path
            if ($finalPath) {
                $height = $_POST["perf_height_$i"] ?? (is_array($currentData) ? ($currentData['height'] ?? '280px') : '280px');
                $data['products'][$index]['performance_gallery'][$i] = [
                    'path' => $finalPath,
                    'height' => $height
                ];
            }
        }

        // Handle PDF Upload
        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === 0) {
            $target_dir = "assets/documents/";
            if (!file_exists($target_dir))
                mkdir($target_dir, 0777, true);

            $filename = basename($_FILES["pdf_file"]["name"]);
            $filename = preg_replace("/[^a-zA-Z0-9.\-_]/", "_", $filename);
            $target_file = $target_dir . "cat_" . time() . "_" . $filename;

            if (move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $target_file)) {
                $data['products'][$index]['pdf'] = $target_file;
                $message .= " PDF uploaded.";
            }
        }

        // Handle Main Image Upload
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === 0) {
            $target_dir = "assets/images/products/";
            if (!file_exists($target_dir))
                mkdir($target_dir, 0777, true);

            $filename = basename($_FILES["image_file"]["name"]);
            $filename = preg_replace("/[^a-zA-Z0-9.]/", "_", $filename);
            $target_file = $target_dir . time() . "_" . $filename;

            if (move_uploaded_file($_FILES["image_file"]["tmp_name"], $target_file)) {
                $data['products'][$index]['image'] = $target_file;
                $message .= " Main image uploaded.";
            }
        }

        // Save Main Image Height
        if (isset($_POST['image_height'])) {
            $data['products'][$index]['image_height'] = $_POST['image_height'];
        }

        // Re-index arrays to consolidate deleted items
        if (isset($data['products'][$index]['gallery']))
            $data['products'][$index]['gallery'] = array_values($data['products'][$index]['gallery']);
        if (isset($data['products'][$index]['qa_gallery']))
            $data['products'][$index]['qa_gallery'] = array_values($data['products'][$index]['qa_gallery']);
        if (isset($data['products'][$index]['performance_gallery']))
            $data['products'][$index]['performance_gallery'] = array_values($data['products'][$index]['performance_gallery']);

        // Save JSON
        file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
        if (!trim($message))
            $message = "Product details updated.";

        $message .= " (Server Limit: " . ini_get('upload_max_filesize') . ")";
    }
}

// Handle Reset Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_product'])) {
    $index = (int) $_POST['product_index'];
    if (isset($data['products'][$index])) {
        // Keep identity
        $p = $data['products'][$index];
        $clean = [
            'name' => $p['name'],
            'id' => $p['id'],
            'description' => '', // Cleared
            'image' => '',       // Cleared
            'gallery' => [],     // Cleared
            'qa_gallery' => [],  // Cleared
            'qa_video' => '',    // Cleared
            'qa_video_title' => '',
            'qa_video_2' => '',
            'pdf' => ''          // Cleared
        ];
        $data['products'][$index] = $clean;
        // Re-index arrays to prevent holes
        $data['products'][$index]['gallery'] = array_values($data['products'][$index]['gallery']);
        $data['products'][$index]['qa_gallery'] = array_values($data['products'][$index]['qa_gallery']);
        $data['products'][$index]['performance_gallery'] = array_values($data['products'][$index]['performance_gallery']);

        file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
        $message .= " Product updated successfully!";
        // Redirect to same page with message
        header("Location: catalog-editor.php?edit=$index&msg=Data+Reset+Successful");
        exit;
    }
}

// Determine View Mode
$editIndex = isset($_GET['edit']) ? (int) $_GET['edit'] : -1;
$editingProduct = ($editIndex >= 0 && isset($data['products'][$editIndex])) ? $data['products'][$editIndex] : null;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalog Editor</title>
    <!-- Simple CSS for Admin Interface -->
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f7fa;
            padding: 0;
            margin: 0;
        }

        .header {
            background: #333;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
        }

        .header a {
            color: #f4f4f4;
            text-decoration: none;
            font-size: 14px;
            margin-left: 20px;
        }

        .header a:hover {
            text-decoration: underline;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .card-header {
            background: #fff;
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }

        .catalog-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .catalog-item {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: bg 0.2s;
        }

        .catalog-item:last-child {
            border-bottom: none;
        }

        .catalog-item:hover {
            background: #f9f9f9;
        }

        .item-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .item-thumb {
            width: 50px;
            height: 50px;
            border-radius: 4px;
            object-fit: cover;
            background: #eee;
        }

        .item-details h3 {
            margin: 0 0 5px;
            font-size: 16px;
            color: #333;
        }

        .item-details p {
            margin: 0;
            color: #777;
            font-size: 13px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: #D98600;
            color: white;
        }

        .btn-primary:hover {
            background: #b77100;
        }

        .btn-outline {
            border: 1px solid #ccc;
            background: white;
            color: #333;
        }

        .btn-outline:hover {
            background: #eee;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #444;
        }

        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        .alert {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .edit-preview-link {
            font-size: 13px;
            color: #007bff;
            text-decoration: none;
            display: inline-block;
            margin-top: 5px;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .gallery-item {
            border: 1px solid #eee;
            padding: 10px;
            border-radius: 6px;
        }

        .section-title {
            font-weight: bold;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
            margin-top: 30px;
            color: #D98600;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>APSS Catalog Editor</h1>
        <div>
            <a href="story.php" target="_blank">View Story Page</a>
            <a href="?logout=1">Logout</a>
        </div>
    </div>

    <div class="container">

        <?php if ($message): ?>
            <div class="alert"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($editingProduct): ?>
            <!-- Edit Mode -->
            <div class="card">
                <div class="card-header">
                    <h2>Edit Product: <?php echo htmlspecialchars($editingProduct['name']); ?></h2>
                    <a href="catalog-editor.php" class="btn btn-outline">Back to List</a>
                </div>
                <div style="padding: 30px;">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="save_product" value="1">
                        <input type="hidden" name="product_index" value="<?php echo $editIndex; ?>">

                        <!-- SECTION 1: COVER PAGE -->
                        <div class="section-title">Page 1: Cover & Identity</div>

                        <div class="form-group">
                            <label>Product Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($editingProduct['name']); ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label>Unique ID (URL Slug)</label>
                            <input type="text" name="id" value="<?php echo htmlspecialchars($editingProduct['id']); ?>"
                                required>
                            <small style="color: #888;">e.g. 'makhana'</small>
                        </div>

                        <div class="form-group">
                            <div class="form-group">
                                <label>Main Cover Image</label>
                                <?php if (!empty($editingProduct['image'])):
                                    $imgHeight = $editingProduct['image_height'] ?? 'auto';
                                    if (!$imgHeight || $imgHeight == 'auto')
                                        $imgHeight = '280px';
                                    ?>
                                    <img src="<?php echo htmlspecialchars($editingProduct['image']); ?>"
                                        style="height: 100px; margin-bottom: 10px; display: block; border-radius: 4px;">

                                    <div style="margin-bottom: 10px; display: flex; align-items: center; gap: 5px;">
                                        <label style="font-size: 12px; color: #666;">Height:</label>
                                        <button type="button" onclick="adjustHeight('image_height', -20)"
                                            style="width: 25px; height: 25px; cursor: pointer;">-</button>
                                        <input type="text" id="image_height" name="image_height"
                                            value="<?php echo htmlspecialchars($imgHeight); ?>" readonly
                                            style="width: 50px; text-align: center; font-size: 12px; height: 25px; padding: 0;">
                                        <button type="button" onclick="adjustHeight('image_height', 20)"
                                            style="width: 25px; height: 25px; cursor: pointer;">+</button>
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="image_file">
                                <small style="color: #666;">Recommended: 1200 x 800 px (Landscape)</small>
                            </div>

                            <!-- SECTION 2: COMPANY PROFILE (PAGE 2) -->
                            <div class="section-title">Page 2: Company Profile & Welcome</div>

                            <div class="form-group">
                                <label>Company Profile Text (Who We Are)</label>
                                <textarea name="profile_text" placeholder="Leave empty to use default text..."
                                    style="height: 100px; font-size:13px;"><?php echo htmlspecialchars($editingProduct['profile_text'] ?? ''); ?></textarea>
                                <small style="color: #888;">Overrides the standard 'Who We Are' paragraph.</small>
                            </div>

                            <div class="form-group">
                                <label>Welcome Message (Hey [Name]...)</label>
                                <textarea name="welcome_message" placeholder="Leave empty for default logic..."
                                    style="height: 80px; font-size:13px;"><?php echo htmlspecialchars($editingProduct['welcome_message'] ?? ''); ?></textarea>
                                <small style="color: #888;">Custom text for the personalized greeting block.</small>
                            </div>

                            <!-- SECTION 3: ABOUT & HIGHLIGHTS (PAGE 3) -->
                            <div class="section-title">Page 3: About Product & Highlights</div>

                            <div class="form-group">
                                <label>Detailed Description</label>
                                <textarea name="description"
                                    style="height: 120px;"><?php echo htmlspecialchars($editingProduct['description']); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>Why Choose Us (Q&A Answer)</label>
                                <textarea name="why_choose_custom"
                                    placeholder="Leave empty for default Areca/Turmeric logic..."
                                    style="height: 80px; font-size:13px;"><?php echo htmlspecialchars($editingProduct['why_choose_custom'] ?? ''); ?></textarea>
                                <small style="color: #888;">Overrides the 'Why Choose From Us' answer block.</small>
                            </div>

                            <div class="form-group">
                                <label>Key Highlights (3 Items)</label>
                                <small style="color: #888; display: block; margin-bottom: 10px;">These appear as 3 icon
                                    boxes
                                    usually.</small>
                                <div class="gallery-grid" style="grid-template-columns: repeat(3, 1fr);">
                                    <?php for ($i = 0; $i < 3; $i++):
                                        $h = $editingProduct['highlights'][$i] ?? ['icon' => 'fas fa-check', 'title' => '', 'desc' => ''];
                                        ?>
                                        <div class="gallery-item">
                                            <label>Highlight <?php echo $i + 1; ?></label>
                                            <input type="text" name="hl_icon_<?php echo $i; ?>"
                                                placeholder="Icon Class (e.g. fas fa-star)"
                                                value="<?php echo htmlspecialchars($h['icon']); ?>" style="margin-bottom: 5px;">
                                            <input type="text" name="hl_title_<?php echo $i; ?>" placeholder="Title"
                                                value="<?php echo htmlspecialchars($h['title']); ?>"
                                                style="margin-bottom: 5px;">
                                            <textarea name="hl_desc_<?php echo $i; ?>" placeholder="Description"
                                                style="height: 60px;"><?php echo htmlspecialchars($h['desc']); ?></textarea>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Page 3 Bottom Image (Optional)</label>
                                <small style="color: #666; display: block; margin-bottom: 5px;">Overrides the default image
                                    at
                                    the
                                    bottom of the "About" page.</small>
                                <?php
                                $p3ImgData = $editingProduct['page3_image'] ?? '';
                                $p3Path = is_array($p3ImgData) ? ($p3ImgData['path'] ?? '') : $p3ImgData;
                                $p3Height = is_array($p3ImgData) ? ($p3ImgData['height'] ?? '500px') : '500px';
                                ?>
                                <?php if (!empty($p3Path)): ?>
                                    <img src="<?php echo htmlspecialchars($p3Path); ?>"
                                        style="height: 100px; margin-bottom: 10px; display: block; border-radius: 4px; object-fit: cover;">

                                    <div style="margin-bottom: 10px; display: flex; align-items: center; gap: 5px;">
                                        <label style="font-size: 12px; color: #666;">Height:</label>
                                        <button type="button" onclick="adjustHeight('page3_height', -20)"
                                            style="width: 25px; height: 25px; cursor: pointer;">-</button>
                                        <input type="text" id="page3_height" name="page3_height"
                                            value="<?php echo htmlspecialchars($p3Height); ?>" readonly
                                            style="width: 50px; text-align: center; font-size: 12px; height: 25px; padding: 0;">
                                        <button type="button" onclick="adjustHeight('page3_height', 20)"
                                            style="width: 25px; height: 25px; cursor: pointer;">+</button>
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="page3_img_file">
                            </div>

                            <!-- SECTION 3: COLLECTION (PAGE 4) -->
                            <div class="section-title">Page 4: Our Collection Gallery</div>
                            <p style="font-size: 13px; color: #666; margin-bottom: 15px;">
                                <strong>Note:</strong> Items 1-2 often used for Profile/Lifecycle. Items 3-8 are the main
                                grid.
                            </p>
                            <div class="gallery-grid">
                                <?php for ($i = 0; $i < 8; $i++): ?>
                                    <?php
                                    $gItem = isset($editingProduct['gallery'][$i]) ? $editingProduct['gallery'][$i] : null;
                                    $gPath = is_array($gItem) ? ($gItem['path'] ?? '') : ($gItem ?? '');
                                    $gName = is_array($gItem) ? ($gItem['name'] ?? '') : '';
                                    $gDesc = is_array($gItem) ? ($gItem['description'] ?? '') : '';
                                    ?>
                                    <div class="gallery-item"
                                        style="border: 1px solid #eee; padding: 10px; border-radius: 4px;">
                                        <label style="font-weight: bold;">Gallery Slot <?php echo $i + 1; ?></label>

                                        <?php if ($gPath): ?>
                                            <div style="margin-bottom: 5px;">
                                                <?php if (preg_match('/\.(mp4|webm|ogg)$/i', $gPath)): ?>
                                                    <video src="<?php echo htmlspecialchars($gPath); ?>"
                                                        style="height: 80px; width: 100%; object-fit: cover;" controls></video>
                                                <?php else: ?>
                                                    <img src="<?php echo htmlspecialchars($gPath); ?>"
                                                        style="height: 80px; width: 100%; object-fit: cover;">
                                                <?php endif; ?>
                                            </div>
                                            <div style="margin-bottom: 5px;">
                                                <label style="font-size: 11px; color: #d00;">
                                                    <input type="checkbox" name="delete_gallery_<?php echo $i; ?>" value="1"> Remove
                                                </label>
                                            </div>
                                        <?php endif; ?>

                                        <input type="file" name="gallery_image_<?php echo $i; ?>" style="margin-bottom: 5px;">
                                        <small style="display:block; color: #888; margin-bottom: 5px;">Rec: 800 x 600 px</small>

                                        <input type="text" name="gallery_name_<?php echo $i; ?>" placeholder="Title"
                                            value="<?php echo htmlspecialchars($gName); ?>"
                                            style="width: 100%; margin-bottom: 5px; padding: 5px; box-sizing: border-box;">
                                        <textarea name="gallery_desc_<?php echo $i; ?>" placeholder="Short Description"
                                            style="width: 100%; padding: 5px; box-sizing: border-box; height: 40px;"><?php echo htmlspecialchars($gDesc); ?></textarea>
                                        <div style="margin-top: 10px; display: flex; align-items: center; gap: 5px;">
                                            <label style="font-size: 12px; color: #666;">Height:</label>
                                            <button type="button"
                                                onclick="adjustHeight('gallery_height_<?php echo $i; ?>', -20)"
                                                style="width: 20px; height: 20px; cursor: pointer;">-</button>
                                            <input type="text" id="gallery_height_<?php echo $i; ?>"
                                                name="gallery_height_<?php echo $i; ?>"
                                                value="<?php echo htmlspecialchars(is_array($gItem) ? ($gItem['height'] ?? '280px') : '280px'); ?>"
                                                readonly
                                                style="width: 50px; text-align: center; font-size: 11px; height: 20px; padding: 2px;">
                                            <button type="button" onclick="adjustHeight('gallery_height_<?php echo $i; ?>', 20)"
                                                style="width: 20px; height: 20px; cursor: pointer;">+</button>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>

                            <!-- SECTION 4: SPECS & FUN FACT (PAGE 5) -->
                            <div class="section-title">Page 5: Performance (Top Performing...)</div>

                            <div class="form-group">
                                <label>Page Heading Prefix</label>
                                <input type="text" name="performance_prefix"
                                    value="<?php echo htmlspecialchars($editingProduct['performance_prefix'] ?? 'Top Performing'); ?>"
                                    placeholder="e.g. Top Performing">
                                <small style="color: #888;">Displayed as '[Prefix] [PRODUCT NAME]'</small>
                            </div>

                            <div class="form-group">
                                <label>Technical Specs (2 Items)</label>
                                <div class="gallery-grid" style="grid-template-columns: repeat(2, 1fr);">
                                    <?php for ($i = 0; $i < 2; $i++):
                                        $s = $editingProduct['specs'][$i] ?? ['icon' => 'fas fa-ruler-combined', 'title' => '', 'desc' => ''];
                                        ?>
                                        <div class="gallery-item">
                                            <label>Spec <?php echo $i + 1; ?></label>
                                            <input type="text" name="spec_icon_<?php echo $i; ?>" placeholder="Icon Class"
                                                value="<?php echo htmlspecialchars($s['icon']); ?>" style="margin-bottom: 5px;">
                                            <input type="text" name="spec_title_<?php echo $i; ?>"
                                                placeholder="Title (e.g. SIZE)"
                                                value="<?php echo htmlspecialchars($s['title']); ?>"
                                                style="margin-bottom: 5px;">
                                            <input type="text" name="spec_desc_<?php echo $i; ?>"
                                                placeholder="Value (e.g. 5+ Suta)"
                                                value="<?php echo htmlspecialchars($s['desc']); ?>" style="margin-bottom: 5px;">
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Performance Images (Max 4)</label>
                                <small style="color: #666; display: block; margin-bottom: 5px;">Images here replace the 'Our
                                    Collection' grid on Page 5.</small>
                                <div class="gallery-grid" style="grid-template-columns: repeat(2, 1fr) !important;">
                                    <?php for ($i = 0; $i < 4; $i++): ?>
                                        <div class="gallery-item">
                                            <label>Image <?php echo $i + 1; ?></label>
                                            <?php if (isset($editingProduct['performance_gallery'][$i])):
                                                $pItem = $editingProduct['performance_gallery'][$i];
                                                $pPath = is_array($pItem) ? ($pItem['path'] ?? '') : $pItem;
                                                $pHeight = is_array($pItem) ? ($pItem['height'] ?? '280px') : '280px';
                                                ?>
                                                <img src="<?php echo htmlspecialchars($pPath); ?>"
                                                    style="width: 100%; height: 80px; object-fit: cover; margin: 5px 0;">
                                                <div style="margin-bottom: 5px; display: flex; align-items: center; gap: 5px;">
                                                    <label style="font-size: 11px; color: #d00; margin-right: auto;">
                                                        <input type="checkbox" name="delete_perf_<?php echo $i; ?>" value="1">
                                                        Remove
                                                    </label>

                                                    <button type="button"
                                                        onclick="adjustHeight('perf_height_<?php echo $i; ?>', -20)"
                                                        style="width: 20px; cursor: pointer;">-</button>
                                                    <input type="text" id="perf_height_<?php echo $i; ?>"
                                                        name="perf_height_<?php echo $i; ?>"
                                                        value="<?php echo htmlspecialchars($pHeight); ?>" readonly
                                                        style="width: 45px; text-align: center; font-size: 11px; padding: 2px;">
                                                    <button type="button"
                                                        onclick="adjustHeight('perf_height_<?php echo $i; ?>', 20)"
                                                        style="width: 20px; cursor: pointer;">+</button>
                                                </div>
                                            <?php endif; ?>
                                            <input type="file" name="perf_image_<?php echo $i; ?>">
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Did You Know? (Fun Fact)</label>
                                <textarea name="fun_fact" placeholder="Enter a fun fact..."
                                    style="height: 60px;"><?php echo htmlspecialchars($editingProduct['fun_fact'] ?? ''); ?></textarea>
                            </div>

                            <!-- SECTION 5: QUALITY ASSURANCE (PAGE 6) -->
                            <div class="section-title">Page 6: Quality Assurance</div>

                            <div class="form-group">
                                <label>QA Video Title</label>
                                <input type="text" name="qa_video_title"
                                    value="<?php echo htmlspecialchars($editingProduct['qa_video_title'] ?? ''); ?>"
                                    placeholder="e.g. Quality Stress Test">
                            </div>

                            <div class="form-group">
                                <label>QA Video 1 (Main)</label>
                                <?php if (!empty($editingProduct['qa_video'])): ?>
                                    <small style="color:green; display:block;">Current:
                                        <?php echo htmlspecialchars($editingProduct['qa_video']); ?></small>
                                <?php endif; ?>
                                <input type="text" name="qa_video_url" placeholder="YouTube URL">
                            </div>

                            <div class="form-group">
                                <label>QA Video 2 (Optional)</label>
                                <?php if (!empty($editingProduct['qa_video_2'])): ?>
                                    <small style="color:green; display:block;">Current:
                                        <?php echo htmlspecialchars($editingProduct['qa_video_2']); ?></small>
                                <?php endif; ?>
                                <input type="text" name="qa_video_2" placeholder="YouTube URL"
                                    value="<?php echo htmlspecialchars($editingProduct['qa_video_2'] ?? ''); ?>">
                            </div>

                            <label style="margin-top: 15px; display: block;">QA Stress Test Images (Max 4)</label>
                            <div class="gallery-grid" style="grid-template-columns: repeat(2, 1fr);">
                                <?php for ($i = 0; $i < 4; $i++): ?>
                                    <div class="gallery-item">
                                        <label>QA Image <?php echo $i + 1; ?></label>
                                        <?php if (isset($editingProduct['qa_gallery'][$i])):
                                            $qItem = $editingProduct['qa_gallery'][$i];
                                            $qPath = is_array($qItem) ? ($qItem['path'] ?? '') : $qItem;
                                            $qHeight = is_array($qItem) ? ($qItem['height'] ?? '280px') : '280px';
                                            ?>
                                            <img src="<?php echo htmlspecialchars($qPath); ?>"
                                                style="width: 100%; height: 80px; object-fit: cover; margin: 5px 0;">
                                            <div style="margin-bottom: 5px; display: flex; align-items: center; gap: 5px;">
                                                <label style="font-size: 11px; color: #d00; margin-right: auto;">
                                                    <input type="checkbox" name="delete_qa_<?php echo $i; ?>" value="1"> Remove
                                                </label>

                                                <button type="button" onclick="adjustHeight('qa_height_<?php echo $i; ?>', -20)"
                                                    style="width: 20px; cursor: pointer;">-</button>
                                                <input type="text" id="qa_height_<?php echo $i; ?>"
                                                    name="qa_height_<?php echo $i; ?>"
                                                    value="<?php echo htmlspecialchars($qHeight); ?>" readonly
                                                    style="width: 45px; text-align: center; font-size: 11px; padding: 2px;">
                                                <button type="button" onclick="adjustHeight('qa_height_<?php echo $i; ?>', 20)"
                                                    style="width: 20px; cursor: pointer;">+</button>
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" name="qa_image_<?php echo $i; ?>">
                                    </div>
                                <?php endfor; ?>
                            </div>

                            <!-- DOWNLOADS -->
                            <div class="section-title">Downloads</div>
                            <div class="form-group">
                                <label>PDF Catalog</label>
                                <?php if (!empty($editingProduct['pdf'])): ?>
                                    <div style="margin-bottom: 5px;">
                                        <a href="<?php echo htmlspecialchars($editingProduct['pdf']); ?>" target="_blank"
                                            class="edit-preview-link">View PDF</a>
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="pdf_file" accept=".pdf">
                            </div>

                            <!-- ACTIONS -->
                            <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                <button type="submit" name="reset_product" value="1" class="btn btn-danger"
                                    onclick="return confirm('WARNING: Are you sure? This deletes ALL data.');"
                                    style="margin-left: 10px;">Reset Catalog</button>
                                <a href="catalog-editor.php" class="btn btn-outline" style="margin-left: 10px;">Cancel</a>
                            </div>
                    </form>
                </div>
            </div>

            <script>
                function adjustHeight(inputId, step) {
                    const input = document.getElementById(inputId);
                    if (!input) return;

                    let currentVal = input.value.toLowerCase().replace('px', '').trim();
                    let val = parseInt(currentVal);

                    if (isNaN(val)) val = 280; // Default fallback

                    val += step;

                    // Enforce limits
                    if (val < 50) val = 50;
                    if (val > 1000) val = 1000;

                    input.value = val + 'px';
                }
            </script>
        <?php else: ?>
            <!-- List Mode -->
            <div class="card">
                <div class="card-header">
                    <h2>Select a Catalog to Edit</h2>
                </div>
                <ul class="catalog-list">
                    <?php foreach ($data['products'] as $index => $product): ?>
                        <li class="catalog-item">
                            <div class="item-info">
                                <img src="<?php echo !empty($product['image']) ? $product['image'] : 'assets/images/logo.png'; ?>"
                                    class="item-thumb" alt="">
                                <div class="item-details">
                                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <p>ID: <?php echo htmlspecialchars($product['id']); ?></p>
                                </div>
                            </div>
                            <a href="?edit=<?php echo $index; ?>" class="btn btn-primary">Edit Catalog</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>

</body>

</html>