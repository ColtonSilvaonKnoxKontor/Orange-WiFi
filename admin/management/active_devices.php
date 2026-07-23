<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Devices</title>
    <link rel="stylesheet" href="/css/pico.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { padding: 20px; background-color: #f4f7f6; }
        .header-box { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 3px solid #ff6600; padding-bottom: 15px; }
        .header-box h2 { margin: 0; color: #333; text-transform: uppercase; font-weight: 800; letter-spacing: 1px; }
        
        .table-container { background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); overflow: hidden; padding: 10px; }
        table { margin-bottom: 0; width: 100%; }
        th { background: #fafafa; color: #666; font-size: 0.7rem; text-transform: uppercase; font-weight: 800; border-bottom: 2px solid #eee; }
        td { font-size: 0.85rem; vertical-align: middle; padding: 12px 10px; border-bottom: 1px solid #f0f0f0; }
        
        tr.dev-row:hover { background-color: #fffaf0 !important; cursor: pointer; }
        
        .mac-badge { font-family: monospace; background: #edf2f7; padding: 2px 8px; border-radius: 4px; color: #2d3748; font-weight: 600; }
        .ip-text { color: #3182ce; font-weight: 700; font-family: monospace; }
        .host-text { font-weight: 600; color: #2d3436; }
        .session-info { font-size: 0.75rem; color: #718096; }
        
        .footer-actions { margin-top: 25px; display: flex; justify-content: space-between; align-items: center; }
        .count-badge { background: #ff6600; color: #fff; padding: 5px 15px; border-radius: 20px; font-weight: bold; font-size: 0.85rem; }
        
        .live-indicator { height: 10px; width: 100%; border-radius: 5px; background: #e2e8f0; margin-top: 5px; overflow: hidden; }
        .live-progress { height: 100%; background: #ff6600; transition: width 1s; }
    </style>
    <script src="../frontend/menu/security_check.js"></script>
</head>
<body>
    <main>
        <div class="header-box">
            <h2>Active Sessions</h2>
        </div>

        <div class="table-container">
            <table role="grid">
                <thead>
                    <tr>
                        <th>Last Activity</th>
                        <th>Device Identity</th>
                        <th>IP Address</th>
                        <th>Session Detail</th>
                        <th>MAC Address</th>
                    </tr>
                </thead>
                <tbody id="device-list">
                    <tr><td colspan="5" style="text-align:center;">Scanning network...</td></tr>
                </tbody>
            </table>
        </div>

        <div class="footer-actions">
            <div class="count-badge" id="total-count">0 Active Devices</div>
            <button class="secondary" onclick="loadActiveDevices()" style="width: auto; margin: 0;"><i class="fas fa-sync-alt"></i> Refresh</button>
        </div>
    </main>

    <script src="/js/a.js"></script>
    <script>
        function formatDate(ts) {
            if (!ts) return '-NA-';
            const date = new Date(parseInt(ts));
            return date.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }

        function loadActiveDevices() {
            fetch('x.php?dev=active')
                .then(r => r.json())
                .then(data => {
                    const tbody = document.getElementById('device-list');
                    const countBadge = document.getElementById('total-count');
                    tbody.innerHTML = '';
                    
                    if (data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 30px;">No active sessions detected in the last 5 minutes.</td></tr>';
                        countBadge.innerText = '0 Active Devices';
                        return;
                    }

                    data.forEach(d => {
                        const row = document.createElement('tr');
                        row.className = 'dev-row';
                        row.onclick = () => window.location.href = 'device.php?mac=' + d.mac;
                        
                        // Time calculation for session info if needed
                        const info = d.type === 'VOUCHER' ? '<i class="fas fa-ticket-alt"></i> Voucher' : '<i class="fas fa-coins"></i> Coins';

                        row.innerHTML = `
                            <td class="session-info"><i class="far fa-clock"></i> ${formatDate(d.updated_at)}</td>
                            <td class="host-text">${d.host}</td>
                            <td class="ip-text">${d.ip}</td>
                            <td class="session-info">${info}</td>
                            <td><span class="mac-badge">${d.mac}</span></td>
                        `;
                        tbody.appendChild(row);
                    });

                    countBadge.innerText = `${data.length} Active Devices`;
                })
                .catch(e => {
                    console.error(e);
                    document.getElementById('device-list').innerHTML = `<tr><td colspan="5" style="text-align:center; color:red;">Failed to retrieve active session data.</td></tr>`;
                });
        }

        // Auto-refresh every 15 seconds
        setInterval(loadActiveDevices, 15000);
        document.addEventListener('DOMContentLoaded', loadActiveDevices);
    </script>
</body>
</html>