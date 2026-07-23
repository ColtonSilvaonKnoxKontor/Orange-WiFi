<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overview</title>
    <link rel="stylesheet" href="/css/pico.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { padding: 20px; background-color: #f4f7f6; }
        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 3px solid #ff6600; padding-bottom: 15px; }
        .dashboard-header h2 { margin: 0; color: #333; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }
        .last-update { font-size: 0.8rem; color: #666; font-style: italic; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 40px; }
        
        .card { 
            background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); 
            text-align: left; border-left: 5px solid #ff6600; position: relative; overflow: hidden;
        }
        .card i { position: absolute; right: 20px; top: 20px; font-size: 2.5rem; color: rgba(255, 102, 0, 0.1); }
        .card h3 { margin-bottom: 5px; font-size: 0.85rem; color: #888; text-transform: uppercase; font-weight: 700; }
        .card .value { font-size: 1.8rem; font-weight: 800; color: #2d3436; margin: 10px 0; }
        .card .sub { font-size: 0.8rem; color: #a0aec0; font-weight: 500; }

        .section-title { font-size: 1.1rem; font-weight: 700; color: #4a5568; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .section-title i { color: #ff6600; }

        .graph-container { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .graph-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; }
        canvas { max-height: 200px; }
        .total-badge { font-size: 0.75rem; background: #edf2f7; padding: 4px 10px; border-radius: 20px; color: #4a5568; font-weight: 600; }
        
        .eth1-summary { 
            background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); 
            display: flex; justify-content: space-between; align-items: center; margin-top: -10px; margin-bottom: 40px;
            border-top: 2px dashed #eee;
        }
        .eth1-data { display: flex; gap: 30px; }
        .eth1-data .item { text-align: center; }
        .eth1-data .item label { font-size: 0.7rem; color: #888; text-transform: uppercase; font-weight: 700; display: block; }
        .eth1-data .item span { font-size: 1.2rem; font-weight: 800; color: #2d3436; }
        
        .reset-btn { width: auto; font-size: 0.7rem; padding: 5px 15px; margin: 0; background: #cbd5e0 !important; color: #4a5568 !important; border: none !important; }
        .reset-btn:hover { background: #a0aec0 !important; }
        
        .res-cpu { border-left-color: #e53e3e; }
        .res-ram { border-left-color: #38a169; }
        .res-storage { border-left-color: #805ad5; }
        .res-node { border-left-color: #3182ce; }
    </style>
    <script src="../frontend/menu/security_check.js"></script>
</head>
<body>
    <main>
        <div class="dashboard-header">
            <h2>Overview</h2>
            <div class="last-update" id="update-ts">Last updated: Just now</div>
        </div>
        
        <div class="section-title"><i class="fas fa-coins"></i> Financial Performance</div>
        <div class="stats-grid">
            <div class="card">
                <i class="fas fa-calendar-day"></i><h3>Today's Earnings</h3><div class="value" id="day-val">₱0.00</div>
                <div class="sub" id="last-day-val">Yesterday: ₱0.00</div>
            </div>
            <div class="card">
                <i class="fas fa-calendar-week"></i><h3>Weekly Total</h3><div class="value" id="week-val">₱0.00</div>
                <div class="sub" id="last-week-val">Last Week: ₱0.00</div>
            </div>
            <div class="card">
                <i class="fas fa-calendar-alt"></i><h3>Monthly Revenue</h3><div class="value" id="month-val">₱0.00</div>
                <div class="sub" id="last-week-val">Last Month: ₱0.00</div>
            </div>
            <div class="card">
                <i class="fas fa-piggy-bank"></i><h3>Annual Growth</h3><div class="value" id="year-val">₱0.00</div>
                <div class="sub" id="last-year-val">Previous Year: ₱0.00</div>
            </div>
        </div>

        <div class="section-title"><i class="fas fa-network-wired"></i> Network Throughput (Daily)</div>
        <div class="graph-grid">
            <div class="graph-container">
                <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <strong style="color:#ff6600">eth0 (WAN/Internet)</strong>
                    <div style="display:flex; flex-direction: column; align-items: flex-end; gap: 2px;">
                        <span id="eth0-totals" class="total-badge">DL: 0 MB | UL: 0 MB</span>
                        <div style="display:flex; gap: 10px;">
                            <span id="eth0-speed-rx" style="font-size: 0.75rem; color:#ff6600; font-weight: bold;">RX: 0 KB/s</span>
                            <span id="eth0-speed-tx" style="font-size: 0.75rem; color:#3182ce; font-weight: bold;">TX: 0 KB/s</span>
                        </div>
                    </div>
                </div>
                <canvas id="eth0Chart"></canvas>
            </div>
            <div class="graph-container">
                <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <strong style="color:#3182ce">eth1 (LAN/Clients)</strong>
                    <div style="display:flex; flex-direction: column; align-items: flex-end; gap: 2px;">
                        <span id="eth1-totals" class="total-badge">DL: 0 MB | UL: 0 MB</span>
                        <div style="display:flex; gap: 10px;">
                            <span id="eth1-speed-rx" style="font-size: 0.75rem; color:#ff6600; font-weight: bold;">RX: 0 KB/s</span>
                            <span id="eth1-speed-tx" style="font-size: 0.75rem; color:#3182ce; font-weight: bold;">TX: 0 KB/s</span>
                        </div>
                    </div>
                </div>
                <canvas id="eth1Chart"></canvas>
            </div>
        </div>

        <div class="eth1-summary">
            <div>
                <div style="font-weight: 800; color: #4a5568; font-size: 0.9rem;">
                    <i class="fas fa-wifi" style="color: #3182ce"></i> Client Traffic Detail (eth1)
                </div>
                <div style="font-size: 0.7rem; color: #888;">Cumulative total since last reset/midnight</div>
            </div>
            <div class="eth1-data">
                <div class="item">
                    <label>Download</label>
                    <span id="eth1-dl-total">0 MB</span>
                </div>
                <div class="item">
                    <label>Upload</label>
                    <span id="eth1-ul-total">0 MB</span>
                </div>
                <div class="item">
                    <label>Total Used</label>
                    <span id="eth1-combined-total" style="color: #3182ce">0 MB</span>
                </div>
            </div>
            <button class="reset-btn" onclick="resetNetStats()"><i class="fas fa-sync-alt"></i> Reset Stats</button>
        </div>

        <div class="section-title"><i class="fas fa-microchip"></i> System Overview</div>
        <div class="stats-grid">
            <div class="card res-cpu">
                <i class="fas fa-microchip"></i><h3>Overall CPU</h3><div class="value" id="cpu-usage">0%</div>
                <div class="sub" id="cpu-temp-sub">Temp: 0.0°C</div>
            </div>
            <div class="card res-ram">
                <i class="fas fa-memory"></i><h3>RAM Usage</h3><div class="value" id="ram-used">0 MB</div>
                <div class="sub" id="ram-total-sub">Total: 0 MB</div>
            </div>
            <div class="card res-storage">
                <i class="fas fa-hdd"></i><h3>Storage</h3><div class="value" id="disk-used">0 GB</div>
                <div class="sub" id="disk-total-sub">Total: 0 GB</div>
            </div>
            <div class="card res-node">
                <i class="fas fa-clock"></i><h3>Node Status</h3><div class="value" id="active-users">0</div>
                <div class="sub" id="uptime-sub">Uptime: Loading...</div>
            </div>
        </div>
    </main>

    <script>
        const peso = new Intl.NumberFormat('en-PH', {style: 'currency', currency: 'PHP'});
        function formatBytes(bytes, d = 2) {
            if (bytes === 0) return '0 B';
            const k = 1024, dm = d < 0 ? 0 : d, sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }

        function fetchEarnings() {
            fetch('earnings_summary.php').then(r => r.json()).then(data => {
                document.getElementById('day-val').innerText = peso.format(data.day);
                document.getElementById('last-day-val').innerText = 'Yesterday: ' + peso.format(data.last_day);
                document.getElementById('week-val').innerText = peso.format(data.week);
                document.getElementById('month-val').innerText = peso.format(data.month);
                document.getElementById('year-val').innerText = peso.format(data.year);
                document.getElementById('last-year-val').innerText = 'Prev. Year: ' + peso.format(data.last_year);
            });
        }

        function fetchSystemStats() {
            fetch('/api/cpu_temp.php').then(r => r.text()).then(t => { if(t) document.getElementById('cpu-temp-sub').innerText = 'Temp: ' + parseFloat(t).toFixed(1) + '°C'; });
            fetch('/api/uptime.php').then(r => r.text()).then(t => { if(t) document.getElementById('uptime-sub').innerText = 'Uptime: ' + t; });
            fetch('x.php?dev=active').then(r => r.json()).then(u => { if(u) document.getElementById('active-users').innerText = u.length; });

            fetch('../shell_exec/sys_stats.php').then(r => r.json()).then(res => {
                let avg = 0; let count = 0;
                for(let i in res.cpus) { avg += res.cpus[i]; count++; }
                document.getElementById('cpu-usage').innerText = (avg / count).toFixed(1) + '%';
                document.getElementById('ram-used').innerText = Math.round(res.ram.used) + ' MB';
                document.getElementById('ram-total-sub').innerText = `Total: ${Math.round(res.ram.total)} MB`;
                document.getElementById('disk-used').innerText = formatBytes(res.disk.used);
                document.getElementById('disk-total-sub').innerText = `Total: ${formatBytes(res.disk.total)}`;
            });
            document.getElementById('update-ts').innerText = 'Last updated: ' + new Date().toLocaleTimeString();
        }

        function createNetChart(id) {
            return new Chart(document.getElementById(id), {
                type: 'line', 
                data: { 
                    labels: Array(20).fill(''), 
                    datasets: [
                        { 
                            label: 'RX', 
                            data: Array(20).fill(0), 
                            borderColor: '#ff6600', 
                            backgroundColor: '#ff660022', 
                            fill: true, 
                            tension: 0.4 
                        },
                        { 
                            label: 'TX', 
                            data: Array(20).fill(0), 
                            borderColor: '#3182ce', 
                            backgroundColor: '#3182ce22', 
                            fill: true, 
                            tension: 0.4 
                        }
                    ] 
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    plugins: { legend: { display: false } }, 
                    scales: { 
                        y: { 
                            beginAtZero: true, 
                            ticks: { callback: v => v + ' KB/s' } 
                        }, 
                        x: { display: false } 
                    }, 
                    animation: false 
                }
            });
        }
        const eth0Chart = createNetChart('eth0Chart'), eth1Chart = createNetChart('eth1Chart');
        let prevStats = null;

        function fetchNetStats() {
            fetch('../shell_exec/net_stats.php').then(r => r.json()).then(stats => {
                if (prevStats) {
                    updateChart(eth0Chart, stats.eth0, prevStats.eth0, 'eth0-speed-rx', 'eth0-speed-tx', 'eth0-totals');
                    updateChart(eth1Chart, stats.eth1, prevStats.eth1, 'eth1-speed-rx', 'eth1-speed-tx', 'eth1-totals');
                    if (stats.eth1) {
                        document.getElementById('eth1-dl-total').innerText = formatBytes(stats.eth1.rx);
                        document.getElementById('eth1-ul-total').innerText = formatBytes(stats.eth1.tx);
                        document.getElementById('eth1-combined-total').innerText = formatBytes(stats.eth1.rx + stats.eth1.tx);
                    }
                }
                prevStats = stats;
            });
        }

        function updateChart(chart, current, prev, rxId, txId, totalId) {
            if (!current || !prev) return;
            const rxSpeed = Math.max(0, (current.rx - prev.rx) / 1024 / 2);
            const txSpeed = Math.max(0, (current.tx - prev.tx) / 1024 / 2);
            
            document.getElementById(rxId).innerText = `RX: ${rxSpeed.toFixed(1)} KB/s`;
            document.getElementById(txId).innerText = `TX: ${txSpeed.toFixed(1)} KB/s`;
            document.getElementById(totalId).innerText = `DL: ${formatBytes(current.rx)} | UL: ${formatBytes(current.tx)}`;
            
            chart.data.datasets[0].data.shift(); 
            chart.data.datasets[0].data.push(rxSpeed);
            
            chart.data.datasets[1].data.shift(); 
            chart.data.datasets[1].data.push(txSpeed);
            
            chart.update();
        }

        function resetNetStats() {
            if (!confirm("Reset network statistics to zero?")) return;
            fetch('../shell_exec/net_reset.php').then(r => r.text()).then(res => {
                if (res === "SUCCESS") { prevStats = null; fetchNetStats(); }
            });
        }

        fetchEarnings(); fetchSystemStats(); fetchNetStats();
        setInterval(fetchEarnings, 30000); setInterval(fetchSystemStats, 10000); setInterval(fetchNetStats, 2000);
    </script>
</body>
</html>