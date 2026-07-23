<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Restricted Devices Management
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restricted Devices | SILVASYSTEMS</title>
    <link rel="stylesheet" href="/css/pico.min.css">
    <style>
        :root { --primary: #ff6600; }
        body { padding: 20px; background-color: #f8fafc; min-height: 100vh; font-family: system-ui, -apple-system, sans-serif; }
        
        .header-box { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 3px solid #ff6600; padding-bottom: 15px; }
        .header-box h2 { margin: 0; color: #1a202c; text-transform: uppercase; font-weight: 900; letter-spacing: 1px; }

        .table-container { 
            background: #fff; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
            overflow-x: auto; padding: 25px; margin-bottom: 30px; border: 1px solid #edf2f7;
        }

        table { width: 100%; border-collapse: collapse; }
        th { font-size: 0.65rem; color: #64748b; text-transform: uppercase; font-weight: 800; padding: 15px 10px; border-bottom: 2px solid #f1f5f9; text-align: left; }
        td { font-size: 0.85rem; padding: 15px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        
        .mac-badge { font-family: monospace; font-weight: 800; color: #1a202c; background: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; border: 1px solid #e2e8f0; }
        .ip-text { color: #64748b; font-weight: 600; font-size: 0.8rem; }
        .host-text { font-weight: 700; color: #1a202c; }
        
        .status-pill { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; }
        .pill-restricted { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        .btn-view { 
            background: #1a202c !important; color: white !important; border: none; border-radius: 8px; 
            font-size: 0.7rem; font-weight: 800; padding: 6px 12px; text-transform: uppercase; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s;
        }
        .btn-view:hover { background: #4a5568 !important; transform: translateY(-1px); }

        .stats-bar { display: flex; gap: 20px; margin-bottom: 25px; }
        .stat-card { background: #fff; padding: 15px 25px; border-radius: 12px; border-left: 4px solid #ff6600; box-shadow: 0 4px 10px rgba(0,0,0,0.03); flex: 1; }
        .stat-label { font-size: 0.65rem; text-transform: uppercase; color: #64748b; font-weight: 800; display: block; }
        .stat-value { font-size: 1.5rem; font-weight: 900; color: #1a202c; }

        .empty-state { text-align: center; padding: 60px; color: #94a3b8; }
        .icon { width: 1.2em; height: 1.2em; vertical-align: middle; fill: currentColor; }
    </style>
    <script src="../frontend/menu/security_check.js"></script>
</head>
<body>
    <main>
        <div class="header-box">
            <h2>Restricted Devices</h2>
        </div>

        <div class="stats-bar">
            <div class="stat-card">
                <span class="stat-label">Blacklisted Devices</span>
                <div class="stat-value" id="restricted-count">0</div>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Device Identity</th>
                        <th>Network Address</th>
                        <th>Last Activity</th>
                        <th>State</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody id="restricted-list">
                    <tr><td colspan="5" class="empty-state">Synchronizing blacklist...</td></tr>
                </tbody>
            </table>
        </div>
    </main>

    <script src="/js/a.js"></script>
    <script>
        function loadRestricted() {
            fetch('x.php?dev=restricted')
                .then(r => r.json())
                .then(data => {
                    const tbody = document.getElementById('restricted-list');
                    const countDisplay = document.getElementById('restricted-count');
                    tbody.innerHTML = '';
                    countDisplay.innerText = data.length;

                    if (data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="empty-state">No devices currently in restricted state.</td></tr>';
                        return;
                    }

                    data.forEach(d => {
                        tbody.innerHTML += `
                            <tr>
                                <td>
                                    <div class="host-text">${d.host || '-NA-'}</div>
                                    <div class="mac-badge">${d.mac}</div>
                                </td>
                                <td>
                                    <div class="ip-text">${d.ip}</div>
                                </td>
                                <td style="color: #64748b; font-size: 0.8rem; font-weight: 600;">
                                    ${utc_to_local(d.active_at)}
                                </td>
                                <td>
                                    <span class="status-pill pill-restricted">Blacklisted</span>
                                </td>
                                <td style="text-align: right;">
                                    <button class="btn-view" onclick="viewDevice('${d.mac}')">
                                        <svg class="icon" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                                        Manage
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                })
                .catch(e => {
                    document.getElementById('restricted-list').innerHTML = '<tr><td colspan="5" class="empty-state" style="color:#e53e3e;">Sync failure. Retrying...</td></tr>';
                });
        }

        function viewDevice(mac) {
            // Use path relative to current management directory
            window.location.href = 'device.php?mac=' + mac;
        }

        loadRestricted();
        setInterval(loadRestricted, 30000);
    </script>
</body>
</html>