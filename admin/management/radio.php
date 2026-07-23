<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Radio Management
 */
require_once __DIR__ . '/../critical/gatekeeper.php';

$confFile = '/home/pi/orange-wifi/conf/radio_settings.json';
$currentMode = 'preset';
if (file_exists($confFile)) {
    $data = json_decode(file_get_contents($confFile), true);
    $currentMode = $data['mode'] ?? 'preset';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Radio Settings</title>
    <link rel="stylesheet" href="/css/pico.min.css">
    <style>
        .mode-card {
            border: 1px solid #e5e7eb;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .mode-card:hover { background: #f9fafb; }
        .mode-card.active { border-color: #ff6600; background: #fff7ed; box-shadow: 0 0 0 1px #ff6600; }
        .mode-title { font-weight: bold; display: block; margin-bottom: 0.5rem; }
        .mode-desc { font-size: 0.85rem; color: #6b7280; }
        .track-list { list-style: none; padding: 0; }
        .track-item { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 8px 12px; 
            background: #f9fafb; 
            border: 1px solid #eee; 
            border-radius: 6px; 
            margin-bottom: 5px;
            font-size: 0.85rem;
        }
        .btn-delete { 
            padding: 2px 8px !important; 
            font-size: 0.75rem !important; 
            background: #ef4444 !important; 
            border-color: #ef4444 !important;
            margin: 0 !important;
            width: auto !important;
        }
    </style>
</head>
<body>
    <div class="container" style="padding-top: 20px; max-width: 800px;">
        <h3>Background Music Settings</h3>
        
        <?php if (isset($_GET['success'])): ?>
            <article style="background-color: #d1fae5; color: #065f46; border-color: #10b981;">
                <?php 
                    $msg = "Settings Updated Successfully";
                    if($_GET['success'] == 'FileDeleted') $msg = "Track Deleted Successfully";
                    if($_GET['success'] == 'FileUploaded') $msg = "Track Uploaded Successfully";
                    echo $msg;
                ?>
            </article>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <article style="background-color: #fee2e2; color: #991b1b; border-color: #ef4444;">Error: <?php echo htmlspecialchars($_GET['error']); ?></article>
        <?php endif; ?>

        <div class="grid">
            <!-- Mode Selection -->
            <div>
                <h5>Playback Mode</h5>
                <form action="radio_action.php" method="POST">
                    
                    <label class="mode-card <?php echo $currentMode === 'off' ? 'active' : ''; ?>">
                        <input type="radio" name="mode" value="off" onchange="this.form.submit()" <?php echo $currentMode === 'off' ? 'checked' : ''; ?> style="display:none;">
                        <span class="mode-title">🔴 Radio Off</span>
                        <span class="mode-desc">Disable background music entirely.</span>
                    </label>

                    <label class="mode-card <?php echo $currentMode === 'preset' ? 'active' : ''; ?>">
                        <input type="radio" name="mode" value="preset" onchange="this.form.submit()" <?php echo $currentMode === 'preset' ? 'checked' : ''; ?> style="display:none;">
                        <span class="mode-title">🟠 Use Preset Radio</span>
                        <span class="mode-desc">Play default system tracks from the internal library.</span>
                    </label>

                    <label class="mode-card <?php echo $currentMode === 'custom' ? 'active' : ''; ?>">
                        <input type="radio" name="mode" value="custom" onchange="this.form.submit()" <?php echo $currentMode === 'custom' ? 'checked' : ''; ?> style="display:none;">
                        <span class="mode-title">🔵 Use Custom Files</span>
                        <span class="mode-desc">Play high-fidelity tracks uploaded by the administrator.</span>
                    </label>

                </form>
            </div>

            <!-- Upload Section (Only visible if Custom is selected) -->
            <div style="<?php echo $currentMode !== 'custom' ? 'opacity: 0.5; pointer-events: none;' : ''; ?>">
                <h5>Upload Music</h5>
                <p style="font-size: 0.8rem; color: #666;">Only .mp3 files are supported for stream compatibility.</p>
                
                <form action="radio_action.php" method="post" enctype="multipart/form-data">
                    <input type="file" name="music_file" accept=".mp3" required>
                    <button type="submit" class="secondary">Upload Track</button>
                </form>

                <hr>
                
                <h5>Custom Playlist</h5>
                <ul class="track-list">
                    <?php
                    $customDir = '/home/pi/orange-wifi/css/sfx/custom_radio/';
                    if (is_dir($customDir)) {
                        $files = glob($customDir . '*.mp3');
                        if (empty($files)) {
                            echo "<li>No custom tracks found.</li>";
                        } else {
                            foreach ($files as $f) {
                                $name = basename($f);
                                echo "<li class='track-item'>";
                                echo "<span>" . htmlspecialchars($name) . "</span>";
                                echo "<form action='radio_action.php' method='POST' style='margin:0;' onsubmit='return confirm(\"Delete this track?\")'>";
                                echo "<input type='hidden' name='delete_file' value='".htmlspecialchars($name)."'>";
                                echo "<button type='submit' class='btn-delete'>Delete</button>";
                                echo "</form>";
                                echo "</li>";
                            }
                        }
                    } else {
                        echo "<li>Custom directory not initialized.</li>";
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>