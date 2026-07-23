<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Secure Login
 */
require_once '../critical/gatekeeper.php';

// Server-side check: If already logged in, redirect.
if (isset($_COOKIE['hash'])) {
    $payload = base64_encode(json_encode([
        'action' => 'verify',
        'token' => $_COOKIE['hash'],
        'last_seen' => $_COOKIE['last_seen'] ?? 0
    ]));
    
    $res = trim(shell_exec("/usr/bin/silvasystems auth_manager " . escapeshellarg($payload)));
    
    if (strpos($res, 'VALID:') === 0) {
        header('Location: /admin/frontend/');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
<title>login</title>
<link href="/css/pico.min.css" rel="stylesheet">
<style>
:root {
    --primary: #ff6600 !important;
    --primary-hover: #e65c00 !important;
    --primary-focus: rgba(255, 102, 0, 0.125) !important;
    --primary-inverse: #fff !important;
    /* Force Light Theme */
    --background-color: #fff !important;
    --color: #333 !important;
}
.alert {
  background-color: green;
  text-align: center;
  color: white;
  padding: .75em 0;
  position: fixed;
  margin: 0;
  bottom: 0;
  right: 0;
  left: 0;
}
.error {
  background-color: red !important;
}
/* Ensure code block stands out */
.code-inline { background:#1a202c; color:#4ade80; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-weight: bold; }
</style>
</head>
<body>
<main class="container" style="padding: 0">
  <article>
    <label for="password">Login</label>
    <input autofocus="" class="u-full-width" id="password" name="password" type="password" placeholder="password" pattern=".{3,25}" title="password must be between 3-25 characters" required="">

    <button class="primary" onclick="login();" style="width: 100%;">Login</button>
    <div style="text-align: center; margin: 15px 0;">
        <a href="javascript:void(0)" onclick="openSetup();" style="font-size: 0.85rem; color: #ff6600; text-decoration: none; font-weight: bold; display: block; margin-bottom: 8px;">First Time Setup?</a>
        <a href="forgot.php" style="font-size: 0.8rem; color: #666; text-decoration: none;">Forgot Password?</a>
    </div>
    <button class="secondary outline" onclick="location.assign('/');" type="button" style="width: 100%; border: none; font-size: 0.8rem;">Cancel</button>
  </article>

  <article style="font-size:0.85em; text-align:center; padding:15px 0; color: #666;">
    <div>Developed by Colton Silva</div>
    <div style="font-weight: bold; color: #ff6600;">Orange WiFi for SilvaSystems distro</div>
  </article>
</main>

<!-- Setup Modal (Moved outside of main article for high-priority rendering) -->
<dialog id="setup-modal">
  <article style="max-width: 500px; border-top: 5px solid #ff6600;">
    <header>
      <a href="javascript:void(0)" aria-label="Close" class="close" onclick="closeSetup()"></a>
      <strong style="text-transform: uppercase;">For First Time Setup</strong>
    </header>
    <p style="font-size: 0.9rem;">The default password is cryptographically tied to the developer's hardware serial. To reset it, you must first obtain your <strong>Orange Star ID</strong>:</p>
    
    <ul style="font-size: 0.85rem; text-align: left;">
        <li>Access the <strong>Physical Console</strong> (Monitor + Keyboard).</li>
        <li>Login with: User <span class="code-inline">ussr</span> | Password <span class="code-inline">ussr</span></li>
        <li>The <strong>Identification Menu</strong> will load automatically.</li>
        <li>Enter number 1 (Reveal Orange Star ID) to see the Orange Star ID.</li>
    </ul>

    <p style="font-size: 0.85rem; font-style: italic; color: #666;">
        Once obtained, return here and use <strong>Forgot Password?</strong> to perform an identity-verified reset.
    </p>
    
    <footer style="text-align: right; padding: 0;">
        <button class="secondary" onclick="closeSetup()" style="width: auto; margin: 0; padding: 8px 25px; font-size: 0.8rem; background: #1a202c; border: none; color: white;">I UNDERSTAND</button>
    </footer>
  </article>
</dialog>

<script>
function alert(t, c = 1) {
  let p = document.createElement('p');
  p.className = c ? 'alert' : 'alert error';
  p.innerText = t;
  p.addEventListener('click',()=>p.remove());
  document.body.appendChild(p);
  setTimeout(()=>p.remove(),3000);
}

function openSetup() {
    const modal = document.getElementById('setup-modal');
    if (typeof modal.showModal === "function") {
        modal.showModal();
    } else {
        modal.setAttribute('open', 'true');
    }
}

function closeSetup() {
    const modal = document.getElementById('setup-modal');
    if (typeof modal.close === "function") {
        modal.close();
    } else {
        modal.removeAttribute('open');
    }
}

function init() {
  const x = new XMLHttpRequest();
  x.addEventListener('readystatechange',()=> {
    if( x.readyState === 4 ) {
      if( x.status === 200 ) check();
      if( x.status === 201 ) window.location.assign('/admin/chpwd.html');
      if( x.status === 423 ) window.top.location.assign('/admin/lockdown.php');
    }
  });
  x.open('HEAD','/admin/pass.php');
  x.send();
}

function check() {
  const x = new XMLHttpRequest();
  x.addEventListener('readystatechange',()=> {
    if( x.readyState === 4 ) {
      if( x.status === 200 ) window.location.assign('/admin/frontend/');
      if( x.status === 401 ) alert('session has expired', 0);
    }
  });
  x.open('GET','/admin/pass.php');
  x.send();
}

function login() {
  const p = document.getElementById('password'), x = new XMLHttpRequest();
  if(!p.value) return alert('Enter password', 0);

  x.addEventListener('readystatechange',()=> {
    if( x.readyState === 4 ) {
      if( x.status === 200 ) {
        alert('Login Accepted');
        setTimeout(() => { window.location.assign('/admin/frontend/'); }, 500);
      } else {
        const msg = x.responseText.includes(':') ? x.responseText.split(':')[1] : 'Invalid Password';
        alert(msg, 0);
        p.value = ''; p.focus();
      }
    }
  });
  x.open('PUT','/admin/pass.php');
  x.send(btoa(p.value));
}

document.addEventListener('DOMContentLoaded', init);

document.addEventListener('keyup', (e)=> {
  if( e.keyCode === 13 ) login();
});
</script>
</body>
</html>