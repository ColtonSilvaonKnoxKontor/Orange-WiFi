<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Device Control Center</title>
<link href="/css/pico.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    body { padding: 20px; background-color: #f4f7f6; }
    .header-box { margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 5px; }
    .header-box h2 { margin: 0; color: #333; text-transform: uppercase; font-weight: 900; }
    
    .btn-group { display: flex; gap: 10px; margin-bottom: 25px; }
    .action-btn-top { width: auto; margin: 0; padding: 8px 15px; font-size: 0.8rem; font-weight: 800; border-radius: 10px; border: none; cursor: pointer; }
    .btn-refresh { background: #4a5568; color: white; }
    .btn-back { background: transparent; border: 1px solid #cbd5e0; color: #4a5568; }

    .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); margin-bottom: 20px; border-top: 4px solid #ff6600; }
    .card h5 { margin-bottom: 20px; color: #4a5568; font-weight: 800; text-transform: uppercase; font-size: 0.9rem; display: flex; align-items: center; gap: 10px; }
    
    table { margin-bottom: 0; }
    table tr td, table tr th { padding: 12px 0; border-bottom: 1px solid #f0f0f0; font-size: 0.9rem; }
    table tr:last-child td, table tr:last-child th { border-bottom: none; }
    th { color: #718096; width: 45%; text-align: left; }
    td { font-weight: 700; color: #2d3748; text-align: right; }

    .action-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px; margin-top: 10px; }
    .action-btn { font-size: 0.75rem; font-weight: 800; margin: 0; padding: 10px; border-radius: 10px; border: none; }
    .btn-pause { background: #ecc94b; color: #744210; }
    .btn-reset { background: #e53e3e; color: #fff; }
    .btn-add { background: #38a169; color: #fff; }
    
    .status-pill { padding: 2px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 800; }
    .status-online { background: #d1fae5; color: #065f46; }
    .status-offline { background: #fee2e2; color: #991b1b; }

    .data-input-group { background: #f8fafc; padding: 15px; border-radius: 10px; border: 1px solid #e2e8f0; }
</style>
<script src="../frontend/menu/security_check.js"></script>
</head>
<body>
<main class="container">
    <div class="header-box">
        <h2>Device Control</h2>
    </div>

    <div class="btn-group">
        <button class="action-btn-top btn-refresh" onclick="loadDeviceInfo()"><i class="fas fa-sync"></i> Refresh Status</button>
        <button class="action-btn-top btn-back" onclick="window.history.back()"><i class="fas fa-arrow-left"></i> Back to List</button>
    </div>

    <div class="grid">
        <section>
            <div class="card">
                <h5><i class="fas fa-info-circle"></i> Device Information</h5>
                <table>
                    <tr><th>Hostname</th><td id="hostname">---</td></tr>
                    <tr><th>Manufacturer</th><td id="manufacturer" style="color: #ff6600;">---</td></tr>
                    <tr><th>IP Address</th><td id="ip_addr">---</td></tr>
                    <tr><th>MAC Address</th><td id="mac_addr">---</td></tr>
                    <tr><th>Last Active</th><td id="active_at">---</td></tr>
                    <tr><th>Status</th><td id="conn_status">---</td></tr>
                </table>
            </div>

            <div class="card" style="border-top-color: #3182ce;">
                <h5><i class="fas fa-tools"></i> Quick Actions</h5>
                <div class="action-grid" id="action-btns">
                    <button class="action-btn btn-pause" onclick="pauseTime()"><i class="fas fa-pause"></i> PAUSE TIME</button>
                    <button class="action-btn btn-reset" onclick="resetTime()"><i class="fas fa-sync-alt"></i> RESET TIME</button>
                    <button class="action-btn" id="block-btn" style="background: #1a202c; color:#fff; font-size:0.75rem; font-weight:800; border-radius:10px; border:none; padding:10px;"></button>
                </div>
            </div>
        </section>

        <section>
            <div class="card" style="border-top-color: #38a169;">
                <h5><i class="fas fa-chart-pie"></i> Usage Summary</h5>
                <table>
                    <tr><th>Total Time</th><td id="time_limit">---</td></tr>
                    <tr><th>Time Used</th><td id="time_used">---</td></tr>
                    <tr><th>Time Remaining</th><td id="time_left" style="color:#38a169;">---</td></tr>
                    <tr><th>Data Limit</th><td id="mb_limit_val">---</td></tr>
                    <tr><th>Data Used</th><td id="mb_used_val">---</td></tr>
                    <tr><th>Data Remaining</th><td id="mb_free_val" style="color:#3182ce;">---</td></tr>
                    <tr><th>Total Cost</th><td id="mb_cost" style="color:#ff6600;">₱0.00</td></tr>
                </table>
            </div>

            <div class="card" style="border-top-color: #805ad5;">
                <h5><i class="fas fa-plus-circle"></i> Add Session Manually</h5>
                <div class="data-input-group">
                    <div class="grid">
                        <div><label style="font-size:0.7rem;">Mins</label><input type="number" id="add_mins" value="0"></div>
                        <div><label style="font-size:0.7rem;">Data (MB)</label><input type="number" id="add_mb" value="0"></div>
                    </div>
                    <button class="action-btn btn-add" style="width:100%; margin-top:10px;" onclick="addManualSession()"><i class="fas fa-plus"></i> ADD TO ACCOUNT</button>
                </div>
            </div>
        </section>
    </div>

    <div class="card" style="border-top-color: #718096;">
        <h5><i class="fas fa-history"></i> Session History</h5>
        <div style="overflow-x: auto;">
            <table id="tx_table">
                <thead>
                    <tr><th>Start Time</th><th>Type</th><th>Data</th><th>Duration</th><th>Action</th></tr>
                </thead>
                <tbody id="tx_body"></tbody>
            </table>
        </div>
    </div>
</main>

<script src="/js/a.js"></script>
<script>
const urlParams = new URLSearchParams(window.location.search);
const mac = urlParams.get('mac');

function formatDate(ts) { if (!ts) return '-NA-'; return new Date(parseInt(ts)).toLocaleString('en-PH'); }
function format_time(mins) {
    if (mins <= 0) return '0m';
    if (mins >= 1440) return (mins / 1440).toFixed(1) + 'd';
    if (mins >= 60) return Math.floor(mins / 60) + 'h ' + (mins % 60) + 'm';
    return mins + 'm';
}

function loadDeviceInfo() {
    fetch('x.php?dev=get_session&mac=' + mac)
        .then(r => r.json())
        .then(d => {
            document.getElementById('hostname').innerText = d.host;
            document.getElementById('manufacturer').innerText = d.manufacturer;
            document.getElementById('ip_addr').innerText = d.ip;
            document.getElementById('mac_addr').innerText = d.mac;
            document.getElementById('active_at').innerText = formatDate(d.active_at);
            const statusEl = document.getElementById('conn_status');
            statusEl.innerHTML = d.connected ? '<span class="status-pill status-online">ONLINE</span>' : '<span class="status-pill status-offline">OFFLINE</span>';
            
            // Handle BLOCK/UNBLOCK toggle
            const blockBtn = document.getElementById('block-btn');
            if (d.restricted) {
                blockBtn.innerHTML = '<i class="fas fa-unlock"></i> UNBLOCK MAC';
                blockBtn.style.background = "#38a169"; // Success Green for restoration
                blockBtn.onclick = unblockDevice;
            } else {
                blockBtn.innerHTML = '<i class="fas fa-ban"></i> BLOCK MAC';
                blockBtn.style.background = "#1a202c"; // Obsidian for blockade
                blockBtn.onclick = blockDevice;
            }

            document.getElementById('mb_limit_val').innerText = format_mb(d.mb_limit);
            document.getElementById('mb_used_val').innerText = format_mb(d.mb_used);
            document.getElementById('mb_free_val').innerText = format_mb(Math.max(0, d.mb_limit - d.mb_used));
            document.getElementById('time_limit').innerText = format_time(d.time_limit);
            document.getElementById('time_used').innerText = format_time(d.time_used);
            document.getElementById('time_left').innerText = format_time(d.time_remaining);
            loadHistory();
        });
}

function loadHistory() {
    fetch('x.php?dev=get_txn&mac=' + mac).then(r => r.json()).then(data => {
        const tbody = document.getElementById('tx_body');
        tbody.innerHTML = '';
        let totalCost = 0;
        data.forEach(tx => {
            totalCost += tx.amt;
            tbody.innerHTML += `<tr><td>${formatDate(tx.ts)}</td><td>${tx.time_limit_min > 0 ? 'VOUCHER' : 'COIN'}</td><td>${format_mb(tx.mb_limit)}</td><td>${tx.time_limit_min} min</td><td><i class="fas fa-trash" style="color:#e53e3e; cursor:pointer;" onclick="deleteTx(${tx.id})"></i></td></tr>`;
        });
        document.getElementById('mb_cost').innerText = peso.format(totalCost);
    });
}

function pauseTime() { if(confirm("Pause connection?")) fetch('x.php?dev=pause_session&mac=' + mac).then(() => loadDeviceInfo()); }
function resetTime() { if(confirm("Reset balance?")) fetch('x.php?dev=clear_mb&mac=' + mac).then(() => loadDeviceInfo()); }
function blockDevice() { if(confirm("Block MAC?")) fetch('block.php?mac=' + mac).then(() => loadDeviceInfo()); }
function unblockDevice() { if(confirm("Restore network access for this device?")) fetch('unblock.php?mac=' + mac).then(() => loadDeviceInfo()); }
function addManualSession() {
    const mins = document.getElementById('add_mins').value, mb = document.getElementById('add_mb').value;
    if(mins == 0 && mb == 0) return;
    fetch(`x.php?dev=add_session&mac=${mac}&limit=${mb}&mins=${mins}`).then(() => { document.getElementById('add_mins').value = 0; document.getElementById('add_mb').value = 0; loadDeviceInfo(); });
}
function deleteTx(id) { if(confirm("Delete record?")) fetch('x.php?dev=del_txn&sid=' + id).then(() => loadDeviceInfo()); }

document.addEventListener('DOMContentLoaded', loadDeviceInfo);
</script>
</body>
</html>