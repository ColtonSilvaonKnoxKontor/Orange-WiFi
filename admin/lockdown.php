<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Nuclear Lockdown Page
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SYSTEM LOCKED</title>
    <link rel="stylesheet" href="/css/pico.min.css">
    <style>
        body { background-color: #1a0000; color: #ff4444; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; font-family: 'Courier New', monospace; }
        .lockdown-card { background: #000; padding: 3rem; border: 5px solid #ff0000; border-radius: 1rem; width: 100%; max-width: 600px; text-align: center; box-shadow: 0 0 50px rgba(255,0,0,0.5); }
        h1 { font-size: 2.2rem; font-weight: 900; margin-bottom: 2rem; color: #ff0000; text-transform: uppercase; animation: blink 1s infinite; }
        @keyframes blink { 0% { opacity: 1; } 50% { opacity: 0.3; } 100% { opacity: 1; } }
        p { font-size: 1.1rem; margin-bottom: 1rem; font-weight: bold; line-height: 1.4; }
        .mockery { font-size: 0.95rem; color: #ff8888; font-style: italic; margin: 2rem 0; padding: 1rem; border-top: 1px solid #330000; border-bottom: 1px solid #330000; }
        input { background: #222 !important; color: #fff !important; border-color: #ff0000 !important; text-align: center; font-family: monospace; font-size: 1.2rem; }
        .recovery-btn { background: #ff0000 !important; border: none !important; color: #000 !important; font-weight: 900; font-size: 1.5rem; margin-top: 1rem; }
    </style>
</head>
<body>
    <div class="lockdown-card">
        <h1>SECURITY BREACH DETECTED</h1>
        <p>THE PASSWORD CONFIGURATION FILE HAS BEEN DELETED.</p>
        
        <div class="mockery">
            "If I delete the password file, it will reset to default!"<br>
            — Said the "hacker" right before realizing this system isn't that stupid.
        </div>

        <p>Instead of granting unauthorized access, the system has entered <strong>NUCLEAR LOCKDOWN</strong>.</p>
        <p>If you are the actual administrator (and not just a script kiddie who got lucky with SSH), prove ownership now.</p>
        
        <hr style="border-color: #330000;">
        
        <input type="text" id="star-id" placeholder="ENTER ORANGE STAR ID">
        <input type="password" id="new-pass" placeholder="SET NEW PASSWORD" style="margin-top: 10px;">
        
        <button class="recovery-btn" onclick="recover()">RECOVER SYSTEM</button>
    </div>

    <script>
        function recover() {
            const id = document.getElementById('star-id').value;
            const pass = document.getElementById('new-pass').value;
            
            if(!id || !pass) { alert("You're not trying very hard, are you? Fill in the boxes."); return; }

            const payload = JSON.stringify({
                action: 'recovery',
                orangestar_id: id,
                new_password: pass
            });

            fetch('/admin/critical/auth.php', {
                method: 'POST',
                body: payload
            })
            .then(r => r.text())
            .then(res => {
                if (res.includes('RECOVERED:')) {
                    alert("SYSTEM RECOVERED! Access Granted.\nTry not to break it again.");
                    window.location.href = '/admin/';
                } else {
                    alert("ACCESS DENIED.\nNice try, but that ID is wrong.");
                }
            });
        }
    </script>
</body>
</html>