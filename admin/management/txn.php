<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Financial Records
 */
require_once '/home/pi/orange-wifi/lib/database.php';
$db = new Database();
$sum = [];
$q = $db->query("SELECT IFNULL(SUM(piso_count),0) FROM session WHERE DATE(created_at,'+8 hours') = DATE('now','+8 hours')");
$sum['day'] = $q->fetchArray(SQLITE3_NUM)[0];
$q = $db->query("SELECT IFNULL(SUM(piso_count),0) FROM session WHERE DATE(created_at,'+8 hours') = DATE('now','+8 hours','-1 day');");
$sum['last_day'] = $q->fetchArray(SQLITE3_NUM)[0];
$q = $db->query("SELECT IFNULL(SUM(piso_count),0) FROM session WHERE DATE(created_at,'+8 hours') >= DATE('now','+8 hours','weekday 0','-7 days')");
$sum['week'] = $q->fetchArray(SQLITE3_NUM)[0];
$q = $db->query("SELECT IFNULL(SUM(piso_count),0) FROM session WHERE DATE(created_at,'+8 hours') >= DATE('now','+8 hours','start of month')");
$sum['month'] = $q->fetchArray(SQLITE3_NUM)[0];
$q = $db->query("SELECT IFNULL(SUM(piso_count),0) FROM session WHERE DATE(created_at,'+8 hours') >= DATE('now','+8 hours','start of year')");
$sum['year'] = $q->fetchArray(SQLITE3_NUM)[0];
$q = $db->query("SELECT IFNULL(SUM(piso_count),0) FROM session WHERE DATE(created_at,'+8 hours') BETWEEN DATE('now','+8 hours','start of year','-1 year') AND DATE('now','start of year','-1 day')");
$sum['last_year'] = $q->fetchArray(SQLITE3_NUM)[0];

$q = $db->query("SELECT IFNULL(SUM(piso_count),0) FROM session WHERE DATE(created_at,'+8 hours') BETWEEN DATE('now','+8 hours','weekday 0','-14 days') AND DATE('now','+8 hours','weekday 0','-8 days')");
$sum['last_week'] = $q->fetchArray(SQLITE3_NUM)[0];
$q = $db->query("SELECT IFNULL(SUM(piso_count),0) FROM session WHERE DATE(created_at,'+8 hours') BETWEEN DATE('now','+8 hours','start of month','-1 month') AND DATE('now','+8 hours','start of month','-1 day')");
$sum['last_month'] = $q->fetchArray(SQLITE3_NUM)[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Records | SILVASYSTEMS</title>
    <link rel="stylesheet" href="/css/pico.min.css">
    <style>
        :root { --primary: #ff6600; }
        body { padding: 20px; background-color: #f8fafc; min-height: 100vh; font-family: system-ui, -apple-system, sans-serif; }
        .header-box { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 3px solid #ff6600; padding-bottom: 15px; }
        .header-box h2 { margin: 0; color: #1a202c; text-transform: uppercase; font-weight: 900; letter-spacing: 1px; }
        .table-container { background: #fff; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow-x: auto; padding: 25px; margin-bottom: 30px; border: 1px solid #edf2f7; }
        h3 { font-size: 0.85rem; text-transform: uppercase; font-weight: 800; color: #64748b; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        th { font-size: 0.65rem; color: #64748b; text-transform: uppercase; font-weight: 800; padding: 15px 10px; border-bottom: 2px solid #f1f5f9; text-align: left; }
        td { font-size: 0.85rem; padding: 15px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .val-now { color: #ff6600; font-family: monospace; font-weight: 800; }
        .val-last { color: #64748b; font-family: monospace; }
        .mac-badge { font-family: monospace; font-weight: 800; color: #64748b; background: #f8fafc; padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; border: 1px solid #edf2f7; }
        .btn-more { width: 100%; background: #1a202c !important; color: white !important; border: none; border-radius: 12px; font-weight: 800; padding: 12px; text-transform: uppercase; cursor: pointer; transition: all 0.2s; margin-top: 20px; }
    </style>
    <script src="../frontend/menu/security_check.js"></script>
</head>
<body>
    <main>
        <div class="header-box"><h2>Financial Records</h2></div>
        <div class="table-container">
            <h3>Earnings Summary</h3>
            <table>
                <thead><tr><th>Period</th><th>Current</th><th>Previous</th></tr></thead>
                <tbody>
                    <tr><td>Daily</td><td class="val-now">₱<?php echo number_format($sum['day'], 2); ?></td><td class="val-last">₱<?php echo number_format($sum['last_day'], 2); ?></td></tr>
                    <tr><td>Weekly</td><td class="val-now">₱<?php echo number_format($sum['week'], 2); ?></td><td class="val-last">₱<?php echo number_format($sum['last_week'], 2); ?></td></tr>
                    <tr><td>Monthly</td><td class="val-now">₱<?php echo number_format($sum['month'], 2); ?></td><td class="val-last">₱<?php echo number_format($sum['last_month'], 2); ?></td></tr>
                    <tr><td>Yearly</td><td class="val-now">₱<?php echo number_format($sum['year'], 2); ?></td><td class="val-last">₱<?php echo number_format($sum['last_year'], 2); ?></td></tr>
                </tbody>
            </table>
        </div>
        <div class="table-container">
            <h3>Transaction History</h3>
            <table>
                <thead><tr><th>Timestamp</th><th>Amount</th><th>Data / Time</th><th>Device</th><th style="text-align:right">Cumulative</th></tr></thead>
                <tbody id="tx-list"><tr><td colspan="5" style="text-align:center; padding: 40px; color:#94a3b8;">Loading data...</td></tr></tbody>
            </table>
            <button class="btn-more" id="load-more-btn" style="display:none" onclick="loadTxn()">Load More Records</button>
        </div>
    </main>
    <script src="/js/a.js"></script>
    <script>
        let subtotal = 0, limit = 25, offset = 0;
        
        function loadTxn() {
            const tbody = document.getElementById('tx-list');
            const btn = document.getElementById('load-more-btn');
            
            fetch(`x.php?txn=get_all&offset=${offset}&limit=${limit}`)
                .then(r => r.json())
                .then(data => {
                    if (offset === 0) tbody.innerHTML = '';
                    
                    if (!data || data.length === 0) {
                        if (offset === 0) tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 40px; color:#94a3b8;">No record existed!</td></tr>';
                        btn.style.display = 'none';
                        return;
                    }

                    data.forEach(t => {
                        subtotal += t.amt;
                        const amtDisplay = t.amt === 0 ? '<span style="color:#38a169;font-weight:900">FREE</span>' : peso.format(t.amt);
                        const valDisplay = (t.time_limit_min > 0) ? t.time_limit_min + 'm' : format_mb(t.mb_limit);
                        
                        tbody.innerHTML += `
                            <tr>
                                <td style="color: #64748b; font-weight: 600;">${utc_to_local(t.ts)}</td>
                                <td style="font-weight:800">${amtDisplay}</td>
                                <td style="color:#ff6600;font-weight:800;font-family:monospace">${valDisplay}</td>
                                <td>
                                    <div style="font-weight:700;color:#1a202c;">${t.host || '-NA-'}</div>
                                    <div class="mac-badge">${t.mac}</div>
                                </td>
                                <td style="text-align:right;font-weight:800;color:#64748b">${peso.format(subtotal)}</td>
                            </tr>
                        `;
                    });

                    if (data.length === limit) {
                        offset += limit;
                        btn.style.display = 'block';
                    } else {
                        btn.style.display = 'none';
                    }
                })
                .catch(() => {
                    if (offset === 0) tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 40px; color:#94a3b8;">No record existed!</td></tr>';
                });
        }
        document.addEventListener('DOMContentLoaded', loadTxn);
    </script>
</body>
</html>