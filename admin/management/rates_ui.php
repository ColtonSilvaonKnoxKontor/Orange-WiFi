<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rates Configuration | SILVASYSTEMS</title>
    <link rel="stylesheet" href="/css/pico.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary: #ff6600; }
        body { padding: 30px; background-color: #f8fafc; font-family: system-ui, -apple-system, sans-serif; }
        .header-box { margin-bottom: 30px; border-bottom: 4px solid #ff6600; padding-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
        .header-box h2 { margin: 0; color: #1a202c; text-transform: uppercase; font-weight: 900; letter-spacing: 1px; }
        
        .settings-card { background: #fff; padding: 20px; border-radius: 15px; margin-bottom: 25px; border: 1px solid #edf2f7; box-shadow: 0 4px 10px rgba(0,0,0,0.02); }
        .settings-card label { font-weight: 800; font-size: 0.8rem; text-transform: uppercase; color: #64748b; }

        .rates-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .rate-card { background: #fff; padding: 20px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; position: relative; }
        
        .rate-card label { font-size: 0.65rem; font-weight: 800; color: #718096; text-transform: uppercase; margin-bottom: 5px; display: block; }
        .rate-card input { margin-bottom: 12px !important; font-weight: 700; border-radius: 10px; border: 2px solid #edf2f7; width: 100%; font-size: 0.9rem; }
        
        .card-summary { background: #fff5f0; color: #ff6600; padding: 12px; border-radius: 10px; text-align: center; font-weight: 900; font-family: monospace; font-size: 1rem; border: 1px solid rgba(255, 102, 0, 0.1); }
        
        .remove-btn { 
            position: absolute; top: -10px; right: -10px; width: 30px; height: 30px; 
            background: #1a202c; color: white; border: none; border-radius: 50%; cursor: pointer;
            display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.7rem;
        }

        .controls { display: flex; gap: 15px; position: sticky; bottom: 20px; background: rgba(255,255,255,0.95); padding: 20px; border-radius: 20px; backdrop-filter: blur(10px); box-shadow: 0 -10px 30px rgba(0,0,0,0.05); border: 1px solid #eee; z-index: 100; }
        .btn-add { background: #1a202c !important; color: white !important; border: none; font-weight: 800; border-radius: 12px; flex: 1; cursor: pointer; padding: 12px; }
        .btn-save { background: #ff6600 !important; color: white !important; border: none; font-weight: 800; border-radius: 12px; flex: 2; cursor: pointer; padding: 12px; }
        
        .empty-state { grid-column: 1 / -1; text-align: center; padding: 60px; color: #a0aec0; }
        #toast { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background: #1a202c; color: white; padding: 12px 40px; border-radius: 40px; font-weight: 800; display: none; box-shadow: 0 10px 20px rgba(0,0,0,0.2); z-index: 1000; border: 2px solid #ff6600; }
    </style>
</head>
<body>
    <div id="toast">SAVED!</div>
    <main class="container">
        <div class="header-box"><h2>Rates Management</h2></div>

        <div class="settings-card">
            <label>Portal Display Mode</label>
            <div class="grid" style="margin-top: 10px;">
                <label><input type="radio" name="display-mode" value="data" checked onchange="refreshSummaries()"> Show Data (MB/GB)</label>
                <label><input type="radio" name="display-mode" value="time" onchange="refreshSummaries()"> Show Time (Duration)</label>
            </div>
        </div>

        <div id="rates-grid" class="rates-container"><div class="empty-state">Synchronizing...</div></div>
        
        <div class="controls">
            <button class="btn-add" id="add-rate-btn">+ ADD RATE</button>
            <button class="btn-save" id="save-rate-btn">SAVE ALL CHANGES</button>
        </div>
    </main>

    <script>
        const peso = new Intl.NumberFormat('en-PH', { style:'currency', currency:'PHP' });

        function formatMB(size) {
            size = parseFloat(size) || 0;
            if(size < 1) return '0MB';
            let base = Math.floor(Math.log(size) / Math.log(1024));
            let unit = ['MB','GB','TB'];
            let val = size / Math.pow(1024, base);
            return (Number.isInteger(val) ? val : val.toFixed(1)) + unit[base];
        }

        function createCard(grid, amt, mb, time) {
            const card = document.createElement('div');
            card.className = 'rate-card';
            card.innerHTML = `
                <button class="remove-btn">X</button>
                <label>Amount (PHP)</label>
                <input type="number" class="inp-amt" value="${amt}">
                <div class="grid">
                    <div>
                        <label>Data (MB)</label>
                        <input type="number" class="inp-mb" value="${mb}">
                    </div>
                    <div>
                        <label>Time (e.g. 1h, 30m, 1d)</label>
                        <input type="text" class="inp-time" value="${time || '0m'}" placeholder="smhdwmy">
                    </div>
                </div>
                <div class="card-summary"></div>
            `;
            
            card.querySelector('.remove-btn').onclick = () => { card.remove(); checkEmpty(grid); };

            const update = () => {
                const a = card.querySelector('.inp-amt').value || 0;
                const m = card.querySelector('.inp-mb').value || 0;
                const t = card.querySelector('.inp-time').value || '0m';
                const mode = document.querySelector('input[name="display-mode"]:checked').value;
                
                const valDisplay = (mode === 'data') ? formatMB(m) : t;
                card.querySelector('.card-summary').innerText = `${peso.format(a)} = ${valDisplay}`;
            };

            card.querySelectorAll('input').forEach(i => i.oninput = update);
            grid.appendChild(card);
            update();
        }

        function refreshSummaries() {
            document.querySelectorAll('.rate-card').forEach(c => {
                const updateEvent = new Event('input');
                c.querySelector('.inp-amt').dispatchEvent(updateEvent);
            });
        }

        function checkEmpty(grid) {
            if (grid.querySelectorAll('.rate-card').length === 0) {
                grid.innerHTML = '<div class="empty-state">No rates defined. Click Add to begin.</div>';
            }
        }

        window.onload = () => {
            const grid = document.getElementById('rates-grid');
            const addBtn = document.getElementById('add-rate-btn');
            const saveBtn = document.getElementById('save-rate-btn');

            addBtn.onclick = () => {
                const empty = grid.querySelector('.empty-state');
                if (empty) grid.innerHTML = '';
                createCard(grid, 1, 0, '0m');
            };

            saveBtn.onclick = () => {
                const cards = document.querySelectorAll('.rate-card');
                const ratesData = [];
                cards.forEach(c => {
                    const a = c.querySelector('.inp-amt').value;
                    const m = c.querySelector('.inp-mb').value;
                    const t = c.querySelector('.inp-time').value;
                    if (a) ratesData.push(`${a}:${m}:${t}`);
                });

                const payload = {
                    settings: { display: document.querySelector('input[name="display-mode"]:checked').value },
                    rates: ratesData
                };

                saveBtn.innerText = "SYNCHRONIZING..."; saveBtn.disabled = true;

                fetch('rates_api.php', { method: 'PUT', body: JSON.stringify(payload) })
                .then(r => r.text()).then(res => {
                    saveBtn.innerText = "SAVE ALL CHANGES"; saveBtn.disabled = false;
                    const t = document.getElementById('toast');
                    t.innerText = res.includes('SUCCESS') ? 'SYSTEM UPDATED!' : res;
                    t.style.display = 'block'; setTimeout(() => t.style.display = 'none', 3000);
                });
            };

            fetch('rates_api.php?v=' + Date.now())
                .then(r => r.json())
                .then(data => {
                    grid.innerHTML = '';
                    if (data.settings && data.settings.display) {
                        document.querySelector(`input[value="${data.settings.display}"]`).checked = true;
                    }
                    const entries = Object.entries(data.rates || {});
                    if (entries.length === 0) checkEmpty(grid);
                    else entries.forEach(([amt, val]) => createCard(grid, amt, val.mb, val.time));
                });
        };
    </script>
</body>
</html>