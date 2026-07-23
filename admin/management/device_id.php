<?php // [!] By Colton Silva (chinawaterstealers).
require_once __DIR__ . '/../critical/gatekeeper.php';

/**
 * Fetches the Device ID via the encrypted silvasystems logic module.
 */
function fetchEncryptedDeviceID() {
    $context = base64_encode(json_encode(["action" => "get_id"]));
    $res = trim((string)shell_exec("/usr/bin/silvasystems device_id_logic " . escapeshellarg($context)));
    return empty($res) ? "IDENTIFICATION_ERROR" : $res;
}

$deviceID = fetchEncryptedDeviceID();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device ID</title>
    <link rel="stylesheet" href="/css/pico.min.css">
    <style>
        :root { --orange-primary: #ff6600; }
        body { padding: 20px; background-color: #f4f7f6; font-family: 'Hiragino Kaku Gothic Pro', 'Meiryo', system-ui, sans-serif; }
        
        .dashboard-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 30px; 
            border-bottom: 3px solid var(--orange-primary); 
            padding-bottom: 15px; 
        }
        .dashboard-header h2 { 
            margin: 0; 
            color: #333; 
            font-weight: 800; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
        }

        .id-container {
            background: #fff;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            text-align: center;
            border: 1px solid #e2e8f0;
        }

        .id-label {
            font-size: 0.8rem;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.2em;
            margin-bottom: 1.5rem;
            display: block;
        }

        .kanji-display {
            font-size: 2.2rem;
            color: #1e293b;
            font-weight: 900;
            letter-spacing: 0.1em;
            word-break: break-all;
            line-height: 1.4;
            background: #f8fafc;
            padding: 2rem;
            border-radius: 10px;
            border: 2px dashed #cbd5e1;
            display: inline-block;
        }

        .security-note {
            margin-top: 2rem;
            font-size: 0.85rem;
            color: #64748b;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <main>
        <div class="dashboard-header">
            <h2>Identification</h2>
            <div style="font-size: 0.8rem; color: #666; font-style: italic;">Hardware Registry Active</div>
        </div>

        <div class="id-container">
            <span class="id-label">Device Serial (Kanji)</span>
            
            <div class="kanji-display">
                <?php echo htmlspecialchars($deviceID); ?>
            </div>

            <div class="security-note">
                This identifier is derived from your physical hardware and serves as your unique system serial for hardware identification purposes.
            </div>
        </div>
    </main>
</body>
</html>