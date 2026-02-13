<?php
// APSS TradeSphere - The Story Page (Dynamic)
// Standalone Page - Not linked in global navigation
session_start();

// Gatekeeper Check: Redirect to Welcome page if name not set
if (!isset($_SESSION['visitor_name'])) {
    header("Location: welcome.php");
    exit;
}

// Load Data
$dataFile = 'data/story_content.json';
if (file_exists($dataFile)) {
    $data = json_decode(file_get_contents($dataFile), true);
} else {
    // Fallback defaults if json is missing
    $data = [
        "header" => [
            "title" => "Founded in Bangalore, Fueled by Passion.",
            "intro" => "We are 4 partners with one goal: To export India's finest agro-products with zero compromise on quality."
        ],
        "partners" => [ /* Default data would go here but we rely on JSON */],
        "testimonials" => [],
        "upload_section" => [
            "title" => "Upload Your Story",
            "description" => "Are you a happy customer? Share your video feedback with us and get featured on our Wall of Trust."
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-17928610423"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());

        gtag('config', 'AW-17928610423');
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The APSS Story - Pillars of Passion | APSS TradeSphere</title>
    <meta name="description"
        content="Meet the 4 partners behind APSS TradeSphere. Discover our journey from Bangalore to the world, fueled by a passion for quality agro-exports.">
    <meta name="robots" content="noindex, nofollow">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Gilda+Display&display=swap"
        rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/story.css">
    <link rel="stylesheet" href="assets/css/responsive.css">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
</head>

<body>

    <!-- Header -->
    <header class="story-header">
        <div class="container">
            <div class="text-content">
                <img src="assets/images/logo.png" alt="APSS Logo" style="height: 80px; margin-bottom: 20px;">
                <h1 class="story-title"><?php echo htmlspecialchars($data['header']['title']); ?></h1>
                <p class="story-intro"><?php echo htmlspecialchars($data['header']['intro']); ?></p>
            </div>
        </div>
    </header>

    <!-- Meet the Pillars -->
    <section class="section section-cream" id="partners">
        <div class="container">
            <!-- New Owner Section -->
            <div class="owner-section" style="margin-bottom: 60px; text-align: center;">
                <h2 class="section-title text-center" style="margin-bottom: 30px;">Our Founding Vision</h2>

                <div class="owner-card"
                    style="background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); max-width: 800px; margin: 0 auto; display: flex; flex-direction: column; align-items: center; gap: 20px;">
                    <div class="owner-photo-large"
                        style="width: 250px; height: 250px; border-radius: 50%; overflow: hidden; border: 6px solid #D98600;">
                        <img src="assets/images/founder.jpeg" alt="Moni Singh"
                            style="width: 100%; height: 100%; object-fit: cover; object-position: center; transform: scale(2.0) translate(17px, 20px);">
                    </div>

                    <div class="owner-content">
                        <i class="fas fa-quote-left"
                            style="font-size: 24px; color: #D98600; margin-bottom: 15px; display: block;"></i>
                        <p <p
                            style="font-size: 18px; font-style: italic; color: #555; line-height: 1.6; margin-bottom: 20px;">
                            "We started APSS TradeSphere with a simple yet powerful promise: to bring the authentic
                            taste and quality of India to the world. Every product we export carries not just our brand,
                            but the trust and hard work of our farmers. We don't just trade; we build lasting
                            partnerships rooted in integrity. <strong>We invite you to collaborate with us and become a
                                part of our global family.</strong>"
                        </p>
                        <h3 style="color: #2E7D32; margin-bottom: 5px;">Moni Singh & Ayodhaya Singh</h3>
                        <span
                            style="font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 1px;">Founding
                            Partners</span>
                    </div>
                </div>
            </div>

            <h2 class="section-title text-center">Meet the Pillars</h2>
            <p class="section-subtitle text-center mb-20">EXPERTS, NOT JUST TRADERS</p>

            <div class="partners-grid">
                <?php foreach ($data['partners'] as $partner): ?>
                    <div class="partner-card">
                        <div class="partner-photo-wrapper">
                            <img src="<?php echo htmlspecialchars($partner['image']); ?>"
                                alt="<?php echo htmlspecialchars($partner['name']); ?>" class="partner-photo">
                            <?php if (!empty($partner['video_url'])): ?>
                                <button class="play-btn"
                                    onclick="openVideo('<?php echo htmlspecialchars($partner['video_url']); ?>')"><i
                                        class="fas fa-play"></i></button>
                            <?php endif; ?>
                        </div>
                        <div class="partner-info">
                            <h3><?php echo htmlspecialchars($partner['name']); ?></h3>
                            <p><?php echo htmlspecialchars($partner['title']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Wall of Trust -->
    <section class="section section-white" id="testimonials">
        <div class="container">
            <h2 class="section-title text-center">Wall of Trust</h2>
            <p class="section-subtitle text-center mb-20">WHAT OUR PARTNERS SAY</p>

            <div class="masonry-grid">
                <?php foreach ($data['testimonials'] as $testimonial): ?>
                    <div class="testimonial-item">
                        <?php if (isset($testimonial['type']) && $testimonial['type'] === 'video' && !empty($testimonial['video_url'])): ?>
                            <div class="video-wrapper">
                                <iframe src="<?php echo htmlspecialchars($testimonial['video_url']); ?>" frameborder="0"
                                    allowfullscreen></iframe>
                            </div>
                        <?php endif; ?>

                        <div class="customer-quote">
                            <span class="country-flag"><?php echo htmlspecialchars($testimonial['flag']); ?></span>
                            "<?php echo htmlspecialchars($testimonial['quote']); ?>"
                        </div>
                        <div class="customer-name">- <?php echo htmlspecialchars($testimonial['author']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Product Catalogs Section -->
    <?php if (isset($data['products']) && !empty($data['products'])): ?>
        <section class="section section-gold-light" id="products" style="background-color: #fdf6e6; padding: 60px 0;">
            <div class="container text-center">
                <h2 class="section-title text-gold" style="font-size: 36px;">Our Product Catalogs</h2>
                <p class="section-subtitle" style="margin-bottom: 40px;">CLICK TO VIEW DETAILS & DOWNLOAD PDF</p>

                <div class="partners-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                    <?php foreach ($data['products'] as $product):
                        if (isset($product['hidden']) && $product['hidden'])
                            continue;
                        ?>
                        <a href="story-details.php?id=<?php echo htmlspecialchars($product['id']); ?>"
                            style="text-decoration: none; color: inherit;">
                            <div class=" partner-card" style="transition: transform 0.3s;">
                                <div class="partner-photo-wrapper" style="height: 200px;">
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>"
                                        alt="<?php echo htmlspecialchars($product['name']); ?>" class="partner-photo">
                                </div>
                                <div class="partner-info">
                                    <h3 style="font-size: 18px; color: #D98600;">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </h3>
                                    <p style="font-size: 13px; color: #666; font-weight: normal;">
                                        <?php echo htmlspecialchars($product['description']); ?>
                                    </p>
                                    <span class=" btn btn-secondary"
                                        style="margin-top: 10px; padding: 5px 15px; font-size: 12px; display: inline-block;">View
                                        Catalog</span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Upload Story -->
    <section class="section section-black text-center" id="upload">
        <div class="container">
            <h2 class="section-title text-gold"><?php echo htmlspecialchars($data['upload_section']['title']); ?></h2>
            <p class="text-white mb-20" style="max-width: 600px; margin: 0 auto 30px;">
                <?php echo htmlspecialchars($data['upload_section']['description']); ?>
            </p>

            <div id="uploadStatus" style="display:none; margin-top:15px; color:#2E7D32; font-weight:bold;"></div>
            <form class="upload-form" id="storyForm" onsubmit="uploadStory(event)">
                <div class="form-group">
                    <input type="text" name="name" placeholder="Your Name" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Your Email" required>
                </div>
                <div class="form-group">
                    <input type="text" name="country" placeholder="Your Country (e.g. India, USA)" required>
                </div>
                <div class="form-group">
                    <label for="message"
                        style="display:block; text-align:left; color:#ccc; margin-bottom:5px; font-size:14px;">Your
                        Story / Feedback</label>
                    <textarea name="message" id="message" rows="4"
                        placeholder="Share your experience working with us..." required
                        style="width: 100%; padding: 10px; border-radius: 5px; border: none; font-family: inherit;"></textarea>
                </div>
                <button type="submit" id="uploadBtn" class="btn btn-primary" style="width:100%;">Post to Wall of
                    Trust</button>
            </form>
            <script>
                function uploadStory(e) {
                    e.preventDefault();
                    const form = document.getElementById('storyForm');
                    const btn = document.getElementById('uploadBtn');
                    const status = document.getElementById('uploadStatus');

                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';
                    btn.disabled = true;

                    const formData = new FormData(form);

                    fetch('handle_story_upload.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                status.style.display = 'block';
                                status.innerHTML = data.message;
                                status.style.color = '#2E7D32';
                                form.reset();
                                btn.innerHTML = 'Post Another Story';

                                // Auto reload to show the new story
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);
                            } else {
                                status.style.display = 'block';
                                status.innerHTML = 'Error: ' + data.message;
                                status.style.color = '#d32f2f';
                                btn.innerHTML = 'Try Again';
                            }
                            btn.disabled = false;
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            status.style.display = 'block';
                            status.innerHTML = 'Failed to post. Please check your connection.';
                            status.style.color = '#d32f2f';
                            btn.innerHTML = 'Post to Wall of Trust';
                            btn.disabled = false;
                        });
                }
            </script>
        </div>
    </section>

    <!-- Footer Simple -->
    <footer class="site-footer text-center">
        <div class="container">
            <p>&copy;
                <?php echo date('Y'); ?> APSS TradeSphere. All Rights Reserved.
            </p>
            <div style="font-size: 10px; margin-top: 10px;">
                <a href="catalog-editor.php" style="color: #444; text-decoration: none;">Admin Login</a>
            </div>
        </div>
    </footer>

    <!-- Video Modal -->
    <div id="videoModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <div class="modal-video-container" id="modalVideoContainer">
                <!-- Iframe injected here -->
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        const modal = document.getElementById('videoModal');
        const container = document.getElementById('modalVideoContainer');

        function openVideo(url) {
            // Auto-play enable
            const autoplayUrl = url + (url.includes('?') ? '&' : '?') + 'autoplay=1';
            container.innerHTML = `<iframe src="${autoplayUrl}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden'; // Stop scrolling
        }

        function closeModal() {
            modal.style.display = 'none';
            container.innerHTML = ''; // Stop video
            document.body.style.overflow = 'auto'; // Resume scrolling
        }

        // Close on outside click
        window.onclick = function (event) {
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
    <!-- Chat Assistant -->
    <script src="assets/js/chat-widget.js"></script>
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