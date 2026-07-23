<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Secure Password Recovery
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Recovery</title>
<link href="/css/pico.min.css" rel="stylesheet">
<style>
:root {
    --primary: #d97706 !important; /* Orange-Red for critical action */
    --primary-hover: #b45309 !important;
    --primary-focus: rgba(217, 119, 6, 0.125) !important;
    --primary-inverse: #fff !important;
}
body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: #f4f4f4; padding: 20px; }
.card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
h3 { margin-bottom: 20px; color: #333; text-align: center; }
.alert { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 0.9rem; display: none; text-align: center; }
.success { background: #d1fae5; color: #065f46; }
</style>
</head>
<body>
    <div class="card">
        <h3>System Recovery</h3>
        <div id="msg-box" class="alert"></div>
        
        <p style="font-size: 0.85rem; color: #666; margin-bottom: 20px;">
            To reset your admin password, you must verify your identity using the hardware-bound <strong>Orange Star ID</strong>.
        </p>

        <label for="osid">Orange Star ID</label>
        <input type="text" id="osid" placeholder="Paste your 64-char ID here" required>

        <label for="new_pass">New Password</label>
        <input type="password" id="new_pass" placeholder="Minimum 3 characters" required>

        <button onclick="recover()" id="btn-recover">Reset Password</button>
        <button class="secondary outline" onclick="location.href='index.php'" style="width: 100%; border: none;">Back to Login</button>
    </div>

<script>
function recover() {
    const osid = document.getElementById('osid').value.trim();
    const pass = document.getElementById('new_pass').value.trim();
    const msg = document.getElementById('msg-box');
    const btn = document.getElementById('btn-recover');

    if (!osid || !pass) {
        msg.innerText = "Please fill in all fields.";
        msg.style.display = "block";
        return;
    }

    btn.setAttribute('aria-busy', 'true');
    btn.disabled = true;

    // Send to Secure Bridge
    const payload = JSON.stringify({
        action: 'recovery',
        orangestar_id: osid,
        new_password: pass
    });

    fetch('../pass.php', {
        method: 'PUT',
        body: payload
    })
    .then(r => r.text())
    .then(res => {
        btn.removeAttribute('aria-busy');
        btn.disabled = false;

        if (res.includes('RECOVERED')) {
            msg.className = "alert success";
            msg.innerText = "Success! Redirecting...";
            msg.style.display = "block";
            setTimeout(() => location.href = 'index.php', 2000);
        } else {
            msg.className = "alert";
            if (res.includes('WRONG_ID')) msg.innerText = "Invalid Orange Star ID.";
            else if (res.includes('TOO_SHORT')) msg.innerText = "Password too short.";
            else msg.innerText = "Recovery Failed: " + res;
            msg.style.display = "block";
        }
    })
    .catch(() => {
        btn.removeAttribute('aria-busy');
        btn.disabled = false;
        msg.innerText = "Network Error.";
        msg.style.display = "block";
    });
}
</script>
</body>
</html>