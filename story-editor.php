<?php
// Story Editor - Admin Page
// Password protected (basic)

session_start();
$dataFile = 'data/story_content.json';
$message = '';

// Basic Authentication
if (!isset($_SESSION['is_admin']) && isset($_POST['password'])) {
    if ($_POST['password'] === 'apss2024') { // Simple password
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
        <title>Login - Story Editor</title>
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
            }
        </style>
    </head>

    <body>
        <div class="login-box">
            <h2>Story Editor Login</h2>
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
    header('Location: story-editor.php');
    exit;
}

// Load Data
$data = json_decode(file_get_contents($dataFile), true);

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_content'])) {

    // Header
    $data['header']['title'] = $_POST['header_title'];
    $data['header']['intro'] = $_POST['header_intro'];

    // Partners - Dynamic Loop
    if (isset($_POST['partners'])) {
        foreach ($_POST['partners'] as $i => $p) {
            $data['partners'][$i]['name'] = $p['name'];
            $data['partners'][$i]['title'] = $p['title'];
            $data['partners'][$i]['video_url'] = $p['video'];

            // Handle Image Upload
            if (isset($_FILES["partners"]["name"][$i]['image']) && $_FILES["partners"]["error"][$i]['image'] === 0) {
                $target_dir = "assets/images/story/";
                if (!file_exists($target_dir))
                    mkdir($target_dir, 0777, true);

                $filename = basename($_FILES["partners"]["name"][$i]['image']);
                $filename = preg_replace("/[^a-zA-Z0-9.]/", "_", $filename); // Clean filename
                $target_file = $target_dir . time() . "_" . $filename; // Unique name

                if (move_uploaded_file($_FILES["partners"]["tmp_name"][$i]['image'], $target_file)) {
                    $data['partners'][$i]['image'] = $target_file;
                }
            }
        }
    }

    // Testimonials - Dynamic Update & Delete
    $newTestimonials = [];
    if (isset($_POST['testimonials']) && is_array($_POST['testimonials'])) {
        foreach ($_POST['testimonials'] as $index => $t) {
            // content update
            if (isset($t['delete']) && $t['delete'] == '1') {
                continue; // Skip adding this to new array -> effectively deleted
            }

            // Allow cleaning up empty entries
            if (empty($t['author']) && empty($t['quote'])) {
                continue;
            }

            $newTestimonials[] = [
                'author' => $t['author'],
                'quote' => $t['quote'],
                'flag' => $t['flag'],
                'type' => $t['type'],
                'video_url' => $t['video']
            ];
        }
    }
    // Add New Testimonial (if filled)
    if (!empty($_POST['new_testimony_author']) && !empty($_POST['new_testimony_quote'])) {
        $newTestimonials[] = [
            'author' => $_POST['new_testimony_author'],
            'quote' => $_POST['new_testimony_quote'],
            'flag' => $_POST['new_testimony_flag'],
            'type' => $_POST['new_testimony_type'],
            'video_url' => $_POST['new_testimony_video']
        ];
    }

    $data['testimonials'] = $newTestimonials;

    // Products Section
    if (isset($_POST['prod_0_name'])) {
        $products = [];
        $count = 10; // Max check
        for ($i = 0; $i < $count; $i++) {
            if (isset($_POST["prod_{$i}_name"]) && !empty($_POST["prod_{$i}_name"])) {
                $p = [
                    'name' => $_POST["prod_{$i}_name"],
                    'description' => $_POST["prod_{$i}_desc"],
                    'id' => !empty($_POST["prod_{$i}_id"]) ? $_POST["prod_{$i}_id"] : strtolower(str_replace(' ', '-', $_POST["prod_{$i}_name"])),
                    'image' => (isset($data['products'][$i]['image']) ? $data['products'][$i]['image'] : 'assets/images/logo.png'), // Preserve existing image if no new upload logic here yet
                    'pdf' => (isset($data['products'][$i]['pdf']) ? $data['products'][$i]['pdf'] : '')
                ];

                // Handle PDF Upload
                if (isset($_FILES["prod_{$i}_pdf"]) && $_FILES["prod_{$i}_pdf"]['error'] === 0) {
                    $target_dir = "assets/documents/";
                    if (!file_exists($target_dir))
                        mkdir($target_dir, 0777, true);

                    $filename = basename($_FILES["prod_{$i}_pdf"]["name"]);
                    $filename = preg_replace("/[^a-zA-Z0-9.\-_]/", "_", $filename);
                    $target_file = $target_dir . "cat_" . time() . "_" . $filename;

                    if (move_uploaded_file($_FILES["prod_{$i}_pdf"]["tmp_name"], $target_file)) {
                        $p['pdf'] = $target_file;
                    }
                }

                $products[] = $p;
            }
        }
        $data['products'] = $products;
    }

    // Save JSON
    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
    $message = "Content Updated Successfully!";
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Story Page Editor</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f9f9f9;
            padding: 20px;
        }

        .container {
            max-width: 960px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        h1 {
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
        }

        .section-box {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            background: #fff;
        }

        .section-title {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 15px;
            color: #D98600;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-weight: 500;
            margin-bottom: 5px;
        }

        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        textarea {
            height: 80px;
        }

        .row {
            display: flex;
            gap: 20px;
        }

        .col {
            flex: 1;
        }

        .btn-save {
            background: #28a745;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        .btn-save:hover {
            background: #218838;
        }

        .alert {
            padding: 10px 15px;
            background: #d4edda;
            color: #155724;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .img-preview {
            max-width: 100px;
            max-height: 100px;
            display: block;
            margin-top: 5px;
            border: 1px solid #ddd;
        }

        .logout {
            float: right;
            color: #dc3545;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="container">
        <a href="?logout=1" class="logout">Logout</a>
        <h1>Story Page Editor</h1>
        <a href="story.php" target="_blank">View Live Page</a>
        <br><br>

        <?php if ($message)
            echo "<div class='alert'>$message</div>"; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="update_content" value="1">

            <!-- Header Section -->
            <div class="section-box">
                <div class="section-title">Header Section</div>
                <div class="form-group">
                    <label>Main Title</label>
                    <input type="text" name="header_title"
                        value="<?php echo htmlspecialchars($data['header']['title']); ?>">
                </div>
                <div class="form-group">
                    <label>Intro Text</label>
                    <textarea name="header_intro"><?php echo htmlspecialchars($data['header']['intro']); ?></textarea>
                </div>
            </div>

            <!-- Partners Section -->
            <div class="section-box">
                <div class="section-title">Partners (Meet the Pillars)</div>
                <?php foreach ($data['partners'] as $i => $partner): ?>
                    <div style="background: #f8f8f8; padding: 15px; margin-bottom: 15px; border-left: 4px solid #D98600;">
                        <strong>Partner
                            <?php echo $i + 1; ?>
                        </strong>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" name="partners[<?php echo $i; ?>][name]"
                                        value="<?php echo htmlspecialchars($partner['name']); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" name="partners[<?php echo $i; ?>][title]"
                                        value="<?php echo htmlspecialchars($partner['title']); ?>">
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label>Video URL (YouTube Embed)</label>
                                    <input type="text" name="partners[<?php echo $i; ?>][video]"
                                        value="<?php echo htmlspecialchars($partner['video_url']); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Change Photo</label>
                                    <!-- Note: PHP array file upload handling requires careful name attribute structure -->
                                    <input type="file" name="partners[<?php echo $i; ?>][image]">
                                    <?php if ($partner['image']): ?>
                                        <img src="<?php echo $partner['image']; ?>" class="img-preview">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Testimonials Section -->
            <div class="section-box">
                <div class="section-title">Wall of Trust (Testimonials)</div>
                <div style="max-height: 600px; overflow-y: auto; padding-right: 10px;">
                    <?php 
                    $testimonials = isset($data['testimonials']) ? $data['testimonials'] : [];
                    foreach ($testimonials as $i => $testimony): 
                    ?>
                        <div style="background: #fff; padding: 15px; margin-bottom: 15px; border: 1px solid #eee; border-radius: 6px; position: relative;">
                            
                            <!-- Delete Checkbox Top Right -->
                            <label style="position: absolute; top: 10px; right: 10px; background: #ffebee; color: #c62828; padding: 5px 10px; border-radius: 4px; cursor: pointer; border: 1px solid #efcdf0; font-size: 14px;">
                                <input type="checkbox" name="testimonials[<?php echo $i; ?>][delete]" value="1">
                                <i class="fas fa-trash"></i> Delete this Entry
                            </label>

                            <strong style="color: #555;">Entry #<?php echo $i + 1; ?> (<?php echo htmlspecialchars($testimony['author']); ?>)</strong>
                            
                            <div class="row" style="margin-top: 15px;">
                                <div class="col">
                                    <div class="form-group">
                                        <label>Author Name</label>
                                        <input type="text" name="testimonials[<?php echo $i; ?>][author]"
                                            value="<?php echo htmlspecialchars($testimony['author']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Country Flag (Emoji)</label>
                                        <input type="text" name="testimonials[<?php echo $i; ?>][flag]"
                                            value="<?php echo htmlspecialchars($testimony['flag']); ?>">
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label>Quote / Message</label>
                                        <textarea
                                            name="testimonials[<?php echo $i; ?>][quote]"><?php echo htmlspecialchars($testimony['quote']); ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label>Type</label>
                                        <select name="testimonials[<?php echo $i; ?>][type]">
                                            <option value="text" <?php echo (isset($testimony['type']) && $testimony['type'] == 'text') ? 'selected' : ''; ?>>
                                                Text Only</option>
                                            <option value="video" <?php echo (isset($testimony['type']) && $testimony['type'] == 'video') ? 'selected' : ''; ?>>
                                                Video</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label>Video URL (Optional)</label>
                                        <input type="text" name="testimonials[<?php echo $i; ?>][video]"
                                            value="<?php echo isset($testimony['video_url']) ? htmlspecialchars($testimony['video_url']) : ''; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Add New -->
                <div style="background: #e8f5e9; padding: 15px; margin-top: 20px; border-left: 4px solid #28a745;">
                    <strong style="color: #28a745;">+ Add New Manually</strong>
                    <div class="row" style="margin-top: 10px;">
                        <input type="text" name="new_testimony_author" placeholder="Author Name" style="width: 48%; margin-right: 2%;">
                        <input type="text" name="new_testimony_flag" placeholder="Flag Emoji (e.g. 🇮🇳)" style="width: 48%;">
                    </div>
                    <textarea name="new_testimony_quote" placeholder="Enter Quote..." style="margin-top: 10px; height: 60px;"></textarea>
                    <div class="row" style="margin-top: 10px;">
                        <select name="new_testimony_type" style="width: 48%; margin-right: 2%;">
                            <option value="text">Text Only</option>
                            <option value="video">Video</option>
                        </select>
                        <input type="text" name="new_testimony_video" placeholder="Video URL (Optional)" style="width: 48%;">
                    </div>
                </div>
            </div>

            <!-- Products Section -->
            <div class="section-box">
                <div class="section-title">Product Catalogs</div>
                <?php
                if (!isset($data['products']))
                    $data['products'] = [];
                // Ensure at least 5 slots or count existing
                $count = max(count($data['products']), 5);
                for ($i = 0; $i < $count; $i++):
                    $prod = isset($data['products'][$i]) ? $data['products'][$i] : ['id' => '', 'name' => '', 'description' => '', 'pdf' => ''];
                    ?>
                    <div style="background: #eef; padding: 15px; margin-bottom: 15px; border-left: 4px solid #0056b3;">
                        <strong>Product <?php echo $i + 1; ?></strong>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label>Product Name</label>
                                    <input type="text" name="prod_<?php echo $i; ?>_name"
                                        value="<?php echo htmlspecialchars($prod['name']); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="prod_<?php echo $i; ?>_desc"
                                        style="height: 60px;"><?php echo htmlspecialchars($prod['description']); ?></textarea>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label>Unique ID (slug)</label>
                                    <input type="text" name="prod_<?php echo $i; ?>_id"
                                        value="<?php echo htmlspecialchars($prod['id']); ?>"
                                        placeholder="e.g. areca-plates">
                                </div>
                                <div class="form-group">
                                    <label>Upload PDF Catalog</label>
                                    <input type="file" name="prod_<?php echo $i; ?>_pdf" accept=".pdf,.doc,.docx">
                                    <?php if (!empty($prod['pdf'])): ?>
                                        <p style="font-size: 0.8em; margin-top: 5px;">Current: <a
                                                href="<?php echo htmlspecialchars($prod['pdf']); ?>" target="_blank">View
                                                PDF</a></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>

            <button type="submit" class="btn-save">Save All Changes</button>
        </form>
    </div>

</body>

</html>