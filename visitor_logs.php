<?php
// Visitor Logs - Admin View
// Only accessible to authenticated admins

session_start();
$logFile = 'data/visitors.json';

// --- Authentication (Reusing Catalog Editor Password for convenience) ---
if (!isset($_SESSION['is_admin']) && isset($_POST['password'])) {
    if ($_POST['password'] === 'apss2024') {
        $_SESSION['is_admin'] = true;
    }
}

if (!isset($_SESSION['is_admin'])) {
    ?>
    <!DOCTYPE html>
    <html>

    <head>
        <title>Login - Visitor Logs</title>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Montserrat', sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                background: #fdfdfd;
                margin: 0;
            }

            .login-box {
                background: white;
                padding: 40px;
                border-radius: 12px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                width: 100%;
                max-width: 320px;
                text-align: center;
            }

            h2 {
                color: #2E7D32;
                margin-bottom: 20px;
            }

            input {
                width: 100%;
                padding: 12px;
                margin-bottom: 15px;
                border: 1px solid #ddd;
                border-radius: 6px;
                box-sizing: border-box;
            }

            button {
                width: 100%;
                padding: 12px;
                background: #D98600;
                color: white;
                border: none;
                border-radius: 6px;
                font-weight: 600;
                cursor: pointer;
            }

            button:hover {
                background: #b77100;
            }
        </style>
    </head>

    <body>
        <div class="login-box">
            <h2>Visitor Logs Login</h2>
            <form method="post">
                <input type="password" name="password" placeholder="Enter Admin Password" required>
                <button type="submit">Access Logs</button>
            </form>
        </div>
    </body>

    </html>
    <?php
    exit;
}

// Fetch logs
$logs = [];

if (file_exists($logFile)) {
    $jsonContent = file_get_contents($logFile);
    $logs = json_decode($jsonContent, true);
    if (!is_array($logs)) {
        $logs = [];
    }
} else {
    // No logs yet
    $logs = [];
}

// Convert UTC timestamp to readable local time (generic approach)
function formatDate($dateStr)
{
    if (!$dateStr)
        return '';
    $date = new DateTime($dateStr, new DateTimeZone('UTC'));
    $date->setTimezone(new DateTimeZone('Asia/Kolkata')); // Adjust to IST primarily
    return $date->format('d M Y, h:i A');
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Logs - APSS</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: #f5f7fa;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .header {
            background: #2E7D32;
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }

        .header-actions a {
            color: white;
            text-decoration: none;
            font-size: 14px;
            margin-left: 20px;
            opacity: 0.9;
        }

        .header-actions a:hover {
            opacity: 1;
            text-decoration: underline;
        }

        .stats-bar {
            display: flex;
            padding: 20px 30px;
            border-bottom: 1px solid #eee;
            background: #fff;
            gap: 40px;
        }

        .stat-item {
            flex: 1;
        }

        .stat-label {
            font-size: 12px;
            color: #777;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .stat-value {
            font-size: 28px;
            color: #D98600;
            font-weight: 700;
            margin-top: 5px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        th,
        td {
            padding: 15px 30px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        th {
            background: #f9f9f9;
            color: #555;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            font-size: 14px;
            color: #444;
        }

        tr:hover {
            background: #fdfdfd;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            background: #eee;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #888;
            font-size: 14px;
            margin-right: 10px;
            float: left;
        }

        .location-badge {
            display: inline-block;
            padding: 4px 10px;
            background: #e8f5e9;
            color: #2E7D32;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .empty-state {
            padding: 60px;
            text-align: center;
            color: #888;
        }

        .empty-state i {
            font-size: 40px;
            margin-bottom: 20px;
            color: #ddd;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header">
            <h1>Visitor Logbook</h1>
            <div class="header-actions">
                <a href="story.php"><i class="fas fa-external-link-alt"></i> Go to Site</a>
                <a href="catalog-editor.php">Catalog Editor</a>
            </div>
        </div>

        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-label">Total Visitors</div>
                <div class="stat-value">
                    <?php echo count($logs); ?>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Last Visit</div>
                <div class="stat-value" style="font-size: 18px; line-height: 40px;">
                    <?php echo !empty($logs) ? formatDate($logs[0]['visit_date']) : 'N/A'; ?>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th width="30%">Visitor Name</th>
                        <th width="35%">Location</th>
                        <th width="35%">Date & Time (IST)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>
                                    <div class="user-avatar"><i class="fas fa-user"></i></div>
                                    <div style="padding-top: 8px; font-weight: 500;">
                                        <?php echo htmlspecialchars($log['name']); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($log['location'] && $log['location'] !== 'Unknown Location'): ?>
                                        <span class="location-badge"><i class="fas fa-map-marker-alt"></i>
                                            <?php echo htmlspecialchars($log['location']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #999; font-style: italic;">Unknown</span>
                                    <?php endif; ?>
                                </td>
                                <td style="color: #666;">
                                    <?php echo formatDate($log['visit_date']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">
                                <div class="empty-state">
                                    <i class="fas fa-clipboard-list"></i>
                                    <p>No visitors have signed in yet.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>