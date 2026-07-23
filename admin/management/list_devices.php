<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Devices</title>
    <link rel="stylesheet" href="/css/pico.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { padding: 20px; background-color: #f4f7f6; }
        .header-box { margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 5px; }
        .header-box h2 { margin: 0; color: #1a202c; text-transform: uppercase; font-weight: 900; }

        .btn-group { display: flex; gap: 10px; margin-bottom: 25px; }
        .action-btn { width: auto; margin: 0; padding: 10px 20px; font-size: 0.8rem; font-weight: 800; border-radius: 10px; border: none; cursor: pointer; }
        .btn-refresh { background: #1a202c; color: white; }
        .btn-wipe { background: #fee2e2; color: #e53e3e; }
        .btn-wipe:hover { background: #fecaca; }
        
        .table-container { 
            background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); 
            overflow-x: auto !important; -webkit-overflow-scrolling: touch; 
            padding: 10px; margin-bottom: 20px;
        }
        
        table { margin-bottom: 0; min-width: 800px; }
        th { background: #fafafa; color: #666; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; border-bottom: 2px solid #eee; }
        td { font-size: 0.9rem; vertical-align: middle; padding: 12px 10px; border-bottom: 1px solid #f0f0f0; white-space: nowrap; }
        
        tr.dev-row:hover { background-color: #fffaf0 !important; cursor: pointer; }
        .mac-badge { font-family: monospace; background: #edf2f7; padding: 2px 8px; border-radius: 4px; color: #2d3748; font-weight: 600; font-size: 0.85rem; }
        .ip-text { color: #3182ce; font-weight: 700; font-family: monospace; }
        .host-text { font-weight: 600; color: #2d3436; }
        .time-text { color: #718096; font-size: 0.8rem; }
        
        .footer-actions { display: flex; justify-content: flex-end; }
        .count-badge { background: #ff6600; color: #fff; padding: 5px 15px; border-radius: 20px; font-weight: bold; font-size: 0.85rem; }
        
        .search-box { margin-bottom: 20px; }
        input[type="search"] { border-radius: 20px; border: 1px solid #ddd; padding-left: 40px; background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="gray" class="bi bi-search" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>'); background-repeat: no-repeat; background-position: 15px center; }
    </style>
    <script src="../frontend/menu/security_check.js"></script>
</head>
<body>
    <main>
        <div class="header-box">
            <h2>Device Inventory</h2>
        </div>

        <div class="btn-group">
            <button class="action-btn btn-refresh" onclick="loadDevices()"><i class="fas fa-sync"></i> Refresh List</button>
            <button class="action-btn btn-wipe" onclick="clearAllDevices()"><i class="fas fa-trash-alt"></i> Wipe Inventory</button>
        </div>

        <div class="search-box">
            <input type="search" id="dev-search" placeholder="Search Inventory..." onkeyup="filterDevices()">
        </div>

        <div class="table-container">
            <table role="grid">
                <thead>
                    <tr>
                        <th>Last Seen</th>
                        <th>Hostname</th>
                        <th>IP Address</th>
                        <th>MAC Address</th>
                    </tr>
                </thead>
                <tbody id="device-list">
                    <tr><td colspan="4" style="text-align:center;">Syncing Inventory...</td></tr>
                </tbody>
            </table>
        </div>

        <div class="footer-actions">
            <div class="count-badge" id="total-count">0 Devices Total</div>
        </div>
    </main>

    <script>
        let allDevices = [];

        function formatDate(ts) {
            if (!ts) return '-NA-';
            return new Date(parseInt(ts)).toLocaleString('en-PH');
        }

        function loadDevices() {
            fetch('x.php?dev=all')
                .then(r => r.json())
                .then(data => {
                    allDevices = data;
                    renderTable(data);
                })
                .catch(e => {
                    document.getElementById('device-list').innerHTML = `<tr><td colspan="4" style="text-align:center; color:red;">Connection error.</td></tr>`;
                });
        }

        function renderTable(data) {
            const tbody = document.getElementById('device-list');
            const countBadge = document.getElementById('total-count');
            tbody.innerHTML = '';
            
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Empty inventory.</td></tr>';
                countBadge.innerText = '0 Devices Total';
                return;
            }

            data.forEach(d => {
                const row = document.createElement('tr');
                row.className = 'dev-row';
                row.onclick = () => window.location.href = 'device.php?mac=' + d.mac;
                row.innerHTML = `
                    <td class="time-text">${formatDate(d.updated_at)}</td>
                    <td class="host-text">${d.host || '-NA-'}</td>
                    <td class="ip-text">${d.ip}</td>
                    <td><span class="mac-badge">${d.mac}</span></td>
                `;
                tbody.appendChild(row);
            });
            countBadge.innerText = `${data.length} Devices Total`;
        }

        function filterDevices() {
            const query = document.getElementById('dev-search').value.toLowerCase();
            const filtered = allDevices.filter(d => 
                (d.host && d.host.toLowerCase().includes(query)) || 
                d.ip.toLowerCase().includes(query) || 
                d.mac.toLowerCase().includes(query)
            );
            renderTable(filtered);
        }

        function clearAllDevices() {
            if (!confirm("Permanently delete all device records?")) return;
            fetch('x.php?dev=clear_all_devices')
                .then(r => r.json())
                .then(res => { if (res.status === 'OK') loadDevices(); });
        }

        document.addEventListener('DOMContentLoaded', loadDevices);
    </script>
</body>
</html>