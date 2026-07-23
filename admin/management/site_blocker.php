<?php // [!] By Colton Silva (chinawaterstealers).
require_once __DIR__ . '/../critical/gatekeeper.php';

$confFile = '/home/pi/orange-wifi/conf/site_blocker.json';
$config = [
    'porn_blocker' => false,
    'selected_list' => 'lite.txt'
];

if (file_exists($confFile)) {
    $config = array_merge($config, json_decode(file_get_contents($confFile), true));
}

$listDir = '/home/pi/orange-wifi/admin/thrdparty/site-list/porn/';
$availableLists = [];
if (is_dir($listDir)) {
    $availableLists = array_diff(scandir($listDir), ['.', '..']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Blocker</title>
    <link rel="stylesheet" href="/css/pico.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --orange-primary: #ff6600; }
        body { padding: 20px; background-color: #f4f7f6; font-family: system-ui, -apple-system, sans-serif; }
        
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

        .blocker-card {
            background: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            margin-bottom: 2rem;
        }

        .control-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 26px; width: 26px;
            left: 4px; bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider { background-color: var(--orange-primary); }
        input:checked + .slider:before { transform: translateX(26px); }

        .list-selector { margin-top: 1rem; }
        .info-text { font-size: 0.85rem; color: #64748b; line-height: 1.5; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <main>
        <div class="dashboard-header">
            <h2>Site Blocker</h2>
            <div style="font-size: 0.8rem; color: #666; font-style: italic;">Network Content Filter</div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <article style="background-color: #d1fae5; color: #065f46; border-color: #10b981;">Settings Saved Successfully</article>
        <?php endif; ?>

        <div class="blocker-card">
            <div class="control-row">
                <div>
                    <h4 style="margin:0;"><i class="fas fa-user-shield" style="color:var(--orange-primary);"></i> Porn Site Blocker</h4>
                    <p class="info-text" style="margin-top:5px;">Restrict access to adult content across the entire network.</p>
                </div>
                <label class="switch">
                    <input type="checkbox" id="porn_toggle" <?php echo $config['porn_blocker'] ? 'checked' : ''; ?> onchange="saveSettings()">
                    <span class="slider"></span>
                </label>
            </div>

            <div class="list-selector">
                <label for="list_select"><strong>Select Curated Blocklist</strong></label>
                <select id="list_select" onchange="saveSettings()">
                    <?php foreach ($availableLists as $list): ?>
                        <option value="<?php echo htmlspecialchars($list); ?>" <?php echo $config['selected_list'] === $list ? 'selected' : ''; ?>>
                            <?php echo strtoupper(basename($list, '.txt')); ?> Edition
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="info-text">
                    <strong>Lite:</strong> Blocks top 5,000 domains. <br>
                    <strong>Heavy:</strong> Comprehensive blocklist (may impact low-end device performance).
                </p>
            </div>
        </div>
    </main>

    <script>
        function saveSettings() {
            const active = document.getElementById('porn_toggle').checked;
            const list = document.getElementById('list_select').value;
            
            const formData = new FormData();
            formData.append('porn_blocker', active ? '1' : '0');
            formData.append('selected_list', list);

            fetch('site_blocker_action.php', {
                method: 'POST',
                body: formData
            }).then(r => {
                if(r.ok) {
                    // Optional: show small toast or feedback
                }
            });
        }
    </script>
</body>
</html>