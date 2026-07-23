<?php // [!] By Colton Silva (chinawaterstealers).
require_once __DIR__ . '/../critical/gatekeeper.php';

function getIntegrityStatus() {
    $sockPath = "/tmp/integrity_" . bin2hex(random_bytes(4)) . ".sock";
    if (file_exists($sockPath)) unlink($sockPath);

    $socket = @socket_create(AF_UNIX, SOCK_STREAM, 0);
    if (!$socket) return "ERROR";
    
    if (!@socket_bind($socket, $sockPath)) return "ERROR";
    if (!@socket_listen($socket, 1)) return "ERROR";
    
    chmod($sockPath, 0666);
    exec("/usr/bin/integrity --socket " . escapeshellarg($sockPath) . " > /dev/null 2>&1 &");

    $conn = @socket_accept($socket);
    if (!$conn) {
        @socket_close($socket);
        @unlink($sockPath);
        return "TIMEOUT";
    }

    $data = "";
    while ($buf = @socket_read($conn, 1024)) { $data .= $buf; }

    @socket_close($conn);
    @socket_close($socket);
    @unlink($sockPath);

    if (empty(trim($data))) return "UNKNOWN";

    $lines = explode("\n", trim($data));
    foreach ($lines as $line) {
        if (strpos($line, ":TAMPERED") !== false) return "TAMPERED";
    }
    return "CLEAN";
}

$fileIntegrity = getIntegrityStatus();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Bulletin</title>
    <link rel="stylesheet" href="/css/pico.min.css">
    <style>
        :root {
            --cyber-green: #10b981;
            --cyber-red: #ef4444;
            --cyber-dark: #0f172a;
            --orange-primary: #ff6600;
        }
        body { padding: 20px; background-color: #f4f7f6; font-family: system-ui, -apple-system, sans-serif; margin: 0; }
        
        /* Fixed Dashboard Header - Strictly Orange */
        .dashboard-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 30px; 
            border-bottom: 3px solid var(--orange-primary) !important; 
            padding-bottom: 15px; 
        }
        .dashboard-header h2 { 
            margin: 0; 
            color: #333; 
            font-weight: 800; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            font-size: 1.5rem;
        }
        .last-audit { font-size: 0.8rem; color: #666; font-style: italic; }

        /* Full-Width Security Audit Container */
        .security-audit-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            width: 100%;
        }

        table[role="grid"] { margin-bottom: 0; width: 100%; border-collapse: collapse; }
        
        thead th {
            background: var(--cyber-dark) !important;
            color: #94a3b8 !important;
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            padding: 1rem 1.5rem !important;
            border: none !important;
        }

        tbody td {
            padding: 1.25rem 1.5rem !important;
            vertical-align: middle !important;
            border-bottom: 1px solid #f1f5f9;
        }

        .param-name { font-weight: 700; color: #334155; display: flex; align-items: center; gap: 10px; }
        .param-name svg { width: 18px; height: 18px; color: #64748b; }

        .status-cell { font-family: monospace; font-weight: 800; font-size: 0.95rem; }
        
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 6px;
            text-transform: uppercase;
        }

        .clean-state { color: var(--cyber-green); background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); }
        .tampered-state { 
            color: var(--cyber-red); background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2);
            box-shadow: 0 0 10px rgba(239, 68, 68, 0.2);
            animation: alert-glow 2s infinite;
        }

        @keyframes alert-glow { 0% { opacity: 1; } 50% { opacity: 0.7; } 100% { opacity: 1; } }

        .observation-text { color: #64748b; font-size: 0.85rem; line-height: 1.5; }

        .audit-footer {
            background: #f8fafc;
            padding: 1rem 1.5rem;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.75rem;
            color: #94a3b8;
        }
        .audit-footer span { display: flex; align-items: center; gap: 6px; }
    </style>
</head>
<body>
    <main>
        <div class="dashboard-header">
            <h2>Security Bulletin</h2>
            <div class="last-audit">Audit Status: Active</div>
        </div>

        <article class="security-audit-container">
            <table role="grid">
                <thead>
                    <tr>
                        <th style="width: 30%">Security Parameter</th>
                        <th style="width: 25%">System Status</th>
                        <th>Observation & Protocol</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="param-name">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                File Integrity
                            </div>
                        </td>
                        <td class="status-cell">
                            <div class="status-pill <?php echo $fileIntegrity === 'CLEAN' ? 'clean-state' : 'tampered-state'; ?>">
                                <?php if ($fileIntegrity === 'CLEAN'): ?>
                                    <svg style="width:14px;height:14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                <?php else: ?>
                                    <svg style="width:14px;height:14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                <?php endif; ?>
                                <?php echo $fileIntegrity; ?>
                            </div>
                        </td>
                        <td class="observation-text">
                            File integrity verification for backend files for any tampering.
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="audit-footer">
                <span>
                    <svg style="width:14px;height:14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    Real-time Hardware Monitoring Active
                </span>
            </div>
        </article>
    </main>
</body>
</html>