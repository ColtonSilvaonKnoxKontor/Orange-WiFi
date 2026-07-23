<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems General Settings
 */
require_once __DIR__ . '/../critical/gatekeeper.php';

$confFile = '/home/pi/orange-wifi/conf/general_settings.json';
$vendoName = "SILVASYSTEMS"; // Default
$customMsg = ""; // Default

if (file_exists($confFile)) {
    $data = json_decode(file_get_contents($confFile), true);
    $vendoName = $data['vendo_name'] ?? "SILVASYSTEMS";
    $customMsg = $data['custom_msg'] ?? "";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>General Settings | SILVASYSTEMS</title>
    <link rel="stylesheet" href="/css/pico.min.css">
    <style>
        :root { --primary: #ff6600; }
        body { padding: 20px; background-color: #f8fafc; min-height: 100vh; font-family: system-ui, -apple-system, sans-serif; }
        
        .header-box { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 3px solid #ff6600; padding-bottom: 15px; }
        .header-box h2 { margin: 0; color: #1a202c; text-transform: uppercase; font-weight: 900; letter-spacing: 1px; }

        .settings-card {
            border: 1px solid #e5e7eb;
            padding: 2rem;
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .info-text { font-size: 0.8rem; color: #6b7280; margin-top: 0.5rem; font-weight: 600; }
    </style>
    <script src="../frontend/menu/security_check.js"></script>
</head>
<body>
    <main>
        <div class="header-box">
            <h2>General Settings</h2>
        </div>
        
        <div class="container" style="max-width: 700px; padding: 0;">
            <?php if (isset($_GET['success'])): ?>
                <article style="background-color: #ecfdf5; color: #065f46; border: 1px solid #10b981; border-radius: 10px; font-weight: 800; text-transform: uppercase; font-size: 0.8rem; margin-bottom: 20px;">Settings saved!</article>
            <?php endif; ?>

            <div class="settings-card">
            <form action="general_settings_action.php" method="POST">
                <label for="vendo_name">
                    <strong style="text-transform: uppercase; font-size: 0.75rem; color: #4a5568;">Vendo Identity Name</strong>
                    <input type="text" id="vendo_name" name="vendo_name" placeholder="e.g. Singh's Piso WiFi" value="<?php echo htmlspecialchars($vendoName); ?>" required style="border-radius: 10px; font-weight: 700;">
                </label>
                <p class="info-text">Displayed at the top of your portal as your unique vendo name.</p>
                
                <div style="margin-top: 25px;">
                    <label for="custom_msg">
                        <strong style="text-transform: uppercase; font-size: 0.75rem; color: #4a5568;">Your Custom Message to Customer</strong>
                        <textarea id="custom_msg" name="custom_msg" rows="4" placeholder="e.g. Welcome! Please insert coins to begin surfing." style="border-radius: 10px; font-weight: 600;"><?php echo htmlspecialchars($customMsg); ?></textarea>
                    </label>
                    <p class="info-text">Announcements or instructions physically rendered on the portal dashboard.</p>
                </div>

                <hr style="margin: 30px 0;">
                
                <button type="submit" style="background: #ff6600; border: none; border-radius: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px;">Save Configuration</button>
                            </form>
                        </div>
                    </div>
                </main>
            </body>
            </html>