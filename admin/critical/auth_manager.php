<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Critical Auth & Recovery Logic

 */

$passwordFile = '/home/pi/orange-wifi/conf/password.sha256';
$initFlag = '/home/pi/orange-wifi/conf/.initialized';
$dbFile = '/home/pi/orange-wifi/conf/orange-wifi.db';
$burnedFlag = '/home/pi/orange-wifi/conf/.id_burned';
$initPassword = 'administrator123';

// 1. Get Identities from CLI Arguments
$correctOrangestarID = $argv[1] ?? 'UNKNOWN';
$pepper = $argv[2] ?? '';
$encodedData = $argv[3] ?? '';

// Fail-safe: If data is missing, terminate immediately
if (empty($encodedData)) { echo "INVALID"; exit; }

$input = json_decode(base64_decode($encodedData), true);
$action = $input['action'] ?? 'status';

// --- CONFIG ---
$idleTimeout = 3600;      // 1 Hour
$absoluteTimeout = 86400;  // 24 Hours

// --- HELPERS ---
function getDB($path) {
    try {
        $db = new SQLite3($path);
        $db->busyTimeout(5000);
        return $db;
    } catch (Exception $e) {
        echo "DB_ERROR"; exit;
    }
}

function generateSessionID() {
    return bin2hex(random_bytes(32));
}

// --- ACTION: STATUS ---
if ($action === 'status') {
    if (!file_exists($initFlag)) echo "SETUP";
    elseif (!file_exists($passwordFile)) echo "LOCKED";
    else echo "READY";
    exit;
}

// --- ACTION: LOGIN ---
if ($action === 'login') {
    $userPass = $input['password'] ?? '';
    if (empty($userPass)) { echo "INVALID"; exit; }

    $valid = false;

    if (!file_exists($initFlag)) {
        // First time login
        if ($userPass === $initPassword) $valid = true;
    } elseif (!file_exists($passwordFile)) {
        // Critical error state
        echo "LOCKDOWN"; exit;
    } else {
        // Standard login
        $storedHash = trim(file_get_contents($passwordFile));
        if (password_verify($userPass . $correctOrangestarID . $pepper, $storedHash)) $valid = true;
    }

    if ($valid) {
        $sid = generateSessionID();
        $now = time();
        $db = getDB($dbFile);
        
        $stmt = $db->prepare("INSERT INTO admin_sessions (session_id, created_at, expires_at) VALUES (:sid, :created, :expires)");
        $stmt->bindValue(':sid', $sid, SQLITE3_TEXT);
        $stmt->bindValue(':created', $now, SQLITE3_INTEGER);
        $stmt->bindValue(':expires', $now + $idleTimeout, SQLITE3_INTEGER);
        $stmt->execute();
        
        echo "SUCCESS:" . $sid . ":" . $now;
    } else {
        // Delay to prevent timing attacks
        usleep(300000); // 300ms
        echo "INVALID";
    }
    exit;
}

// --- ACTION: VERIFY SESSION ---
if ($action === 'verify') {
    $token = $input['token'] ?? '';
    if (empty($token)) { echo "INVALID"; exit; }

    $db = getDB($dbFile);
    $now = time();
    
    $stmt = $db->prepare("SELECT created_at, expires_at FROM admin_sessions WHERE session_id = :sid");
    $stmt->bindValue(':sid', $token, SQLITE3_TEXT);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);

    if ($row) {
        // 1. Check Absolute Limit
        if (($now - $row['created_at']) > $absoluteTimeout) {
            echo "INVALID"; exit;
        }
        // 2. Check Idle Limit
        if ($row['expires_at'] < $now) {
            echo "INVALID"; exit;
        }

        // 3. Roll the Idle Timeout
        $upd = $db->prepare("UPDATE admin_sessions SET expires_at = :exp WHERE session_id = :sid");
        $upd->bindValue(':exp', $now + $idleTimeout, SQLITE3_INTEGER);
        $upd->bindValue(':sid', $token, SQLITE3_TEXT);
        $upd->execute();
        
        echo "VALID:" . $now;
    } else {
        echo "INVALID";
    }
    exit;
}

// --- ACTION: LOGOUT ---
if ($action === 'logout') {
    $token = $input['token'] ?? '';
    if (!empty($token)) {
        $db = getDB($dbFile);
        $stmt = $db->prepare("DELETE FROM admin_sessions WHERE session_id = :sid");
        $stmt->bindValue(':sid', $token, SQLITE3_TEXT);
        $stmt->execute();
    }
    echo "LOGGED_OUT";
    exit;
}

// ... (Rest of ID and Recovery logic) ...
if ($action === 'get_id') {
    if (!file_exists($burnedFlag)) echo $correctOrangestarID;
    else echo substr($correctOrangestarID, 0, 8) . "********************************";
    exit;
}
if ($action === 'burn_id') {
    touch($burnedFlag); echo "BURNED"; exit;
}
if ($action === 'update_password') {
    $newPass = $input['new_password'] ?? '';
    $providedID = isset($input['orangestar_id']) ? trim($input['orangestar_id']) : null;
    
    if ($providedID !== $correctOrangestarID) { 
        usleep(500000);
        echo "WRONG_ID"; exit; 
    }
    
    if (empty($newPass) || strlen($newPass) < 3) {
        echo "TOO_WEAK"; exit;
    }

    $newHash = password_hash($newPass . $correctOrangestarID . $pepper, PASSWORD_ARGON2ID, ['memory_cost' => 1024*16, 'time_cost' => 4, 'threads' => 2]);
    
    if ($newHash === false) { echo "HASH_FAIL"; exit; }

    if (file_put_contents($passwordFile, $newHash) !== false) {
        touch($initFlag);
        $db = getDB($dbFile);
        $db->exec("DELETE FROM admin_sessions");
        echo "SUCCESS";
    } else { 
        echo "WRITE_FAIL"; 
    }
    exit;
}

if ($action === 'recovery') {
    $newPass = $input['new_password'] ?? '';
    $providedID = isset($input['orangestar_id']) ? trim($input['orangestar_id']) : null;
    
    if ($providedID !== $correctOrangestarID) { 
        usleep(500000); // Penalty for guessing
        echo "WRONG_ID"; exit; 
    }
    
    $newHash = password_hash($newPass . $correctOrangestarID . $pepper, PASSWORD_ARGON2ID, ['memory_cost' => 1024*16, 'time_cost' => 4, 'threads' => 2]);
    
    if ($newHash === false) { echo "FAILURE:HASH_FAILED"; exit; }

    if (file_put_contents($passwordFile, $newHash) !== false) {
        touch($initFlag);
        $db = getDB($dbFile);
        $db->exec("DELETE FROM admin_sessions");
        echo "RECOVERED:LOG_IN_NOW";
    } else { 
        $err = error_get_last();
        echo "FAILURE:WRITE_FAILED:" . ($err['message'] ?? 'Unknown'); 
    }
    exit;
}

// Default Deny
echo "INVALID";
exit;
?>