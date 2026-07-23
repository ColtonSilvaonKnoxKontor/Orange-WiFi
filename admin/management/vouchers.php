<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher Management</title>
    <link rel="stylesheet" href="/css/pico.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary: #ff6600; }
        body { padding: 20px; background-color: #f8fafc; min-height: 100vh; }
        .header-box { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 3px solid #ff6600; padding-bottom: 15px; }
        .header-box h2 { margin: 0; color: #1a202c; text-transform: uppercase; font-weight: 900; letter-spacing: 1px; }

        .btn-group { display: flex; gap: 10px; margin-bottom: 20px; }
        .gen-btn { background: #1a202c; border: none; border-radius: 10px; font-weight: 700; font-size: 0.8rem; padding: 8px 15px; color: white; cursor: pointer; }
        .print-btn { background: #3182ce; border: none; border-radius: 10px; font-weight: 700; font-size: 0.8rem; padding: 8px 15px; color: white; cursor: pointer; }
        .wipe-btn { background: #fee2e2; border: none; border-radius: 10px; font-weight: 700; font-size: 0.8rem; padding: 8px 15px; color: #991b1b; cursor: pointer; }

        /* TABLE SCROLL SUPPORT */
        .table-container { 
            background: #fff; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
            overflow-x: auto !important; -webkit-overflow-scrolling: touch; 
            padding: 10px; 
        }
        
        table { margin: 0; min-width: 700px; border-collapse: collapse; }
        table[role="grid"] tbody tr:nth-of-type(odd), table[role="grid"] tbody tr:nth-of-type(even) { background-color: transparent !important; }
        tbody tr { border-bottom: 1px solid #f1f5f9; transition: background 0.2s; }
        tbody tr:hover { background-color: #fffaf0 !important; }
        th { font-size: 0.65rem; color: #64748b; text-transform: uppercase; font-weight: 800; padding: 15px 10px; border-bottom: 2px solid #f1f5f9; }
        td { font-size: 0.85rem; vertical-align: middle; padding: 15px 10px; white-space: nowrap; }
        
        .code-pill { font-family: monospace; background: #fff5f0; color: #ff6600; padding: 4px 10px; border-radius: 6px; font-weight: 800; border: 1px solid rgba(255, 102, 0, 0.15); }
        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; }
        .status-unused { background: #ecfdf5; color: #059669; border: 1px solid #10b981; }
        .status-used { background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e0; }
        
        .del-icon { color: #e53e3e; cursor: pointer; opacity: 0.6; }

        /* PRINT ENGINE */
        #print-view { display: none; }
        @media print {
            body * { visibility: hidden; }
            #print-view, #print-view * { visibility: visible; }
            #print-view { display: grid !important; grid-template-columns: repeat(3, 1fr); gap: 10px; position: absolute; left: 0; top: 0; width: 100%; }
            .v-card { border: 2px dashed #333; padding: 10px; border-radius: 8px; text-align: center; background: white !important; -webkit-print-color-adjust: exact; }
            .v-card .v-code { font-family: monospace; font-size: 1.2rem; font-weight: 900; display: block; margin-bottom: 5px; }
            .v-card .v-details { font-size: 0.7rem; color: #555; font-weight: bold; }
            .v-card .v-brand { font-size: 0.6rem; color: #ff6600; text-transform: uppercase; font-weight: 900; margin-top: 5px; }
        }

        /* MODAL GLASS */
        dialog { backdrop-filter: blur(10px); background-color: rgba(255, 255, 255, 0.3); }
        dialog article { max-width: 450px; width: 95%; border-radius: 20px; box-shadow: 0 25px 50px rgba(0,0,0,0.2); background: white; padding: 0; overflow: hidden; border: none; }
        .modal-head { background: linear-gradient(135deg, #ff6600 0%, #e65c00 100%); padding: 20px; color: white; display: flex; justify-content: space-between; align-items: center; }
        .modal-head h3 { margin: 0; font-size: 1rem; font-weight: 800; }
        .modal-body { padding: 25px; }
        .submit-btn { background: #ff6600; border: none; border-radius: 12px; padding: 15px; font-weight: 900; color: white; width: 100%; }
    </style>
    <script src="../frontend/menu/security_check.js"></script>
</head>
<body>
    <main>
        <div class="header-box">
            <h2>Voucher Inventory</h2>
        </div>

        <div class="btn-group">
            <button class="gen-btn" onclick="openModal()"><i class="fas fa-plus"></i> GENERATE</button>
            <button class="print-btn" onclick="printVouchers()"><i class="fas fa-print"></i> PRINT LIST</button>
            <button class="gen-btn" style="background: #4a5568;" onclick="loadVouchers()"><i class="fas fa-sync"></i> REFRESH</button>
            <button class="wipe-btn" onclick="clearAll()"><i class="fas fa-trash-alt"></i> WIPE ALL</button>
        </div>

        <div id="print-view"></div>

        <dialog id="gen-modal">
            <article>
                <div class="modal-head"><h3><i class="fas fa-ticket-alt"></i> NEW BATCH</h3><i class="fas fa-times" style="cursor:pointer" onclick="closeModal()"></i></div>
                <div class="modal-body">
                    <form id="gen-form">
                        <label>Prefix <input type="text" id="prefix" placeholder="VIP-"></label>
                        <div class="grid">
                            <div><label>Days <input type="number" id="days" value="0"></label></div>
                            <div><label>Hrs <input type="number" id="hrs" value="1"></label></div>
                            <div><label>Mins <input type="number" id="mins" value="0"></label></div>
                        </div>
                        <div class="grid">
                            <div><label>Data (MB) <input type="number" id="data_limit" value="0"></label></div>
                            <div><label>Price <input type="number" id="price" value="10"></label></div>
                            <div><label>Qty <input type="number" id="count" value="10"></label></div>
                        </div>
                        <button type="button" onclick="generateVouchers()" id="btn-gen" class="submit-btn">START GENERATION</button>
                    </form>
                </div>
            </article>
        </dialog>

        <div class="table-container">
            <table role="grid">
                <thead>
                    <tr>
                        <th>Voucher Code</th>
                        <th>Price</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody id="voucher-list">
                    <tr><td colspan="5" style="text-align:center;">Syncing...</td></tr>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        const modal = document.getElementById('gen-modal');
        let currentData = [];

        function openModal() { modal.setAttribute('open', 'true'); }
        function closeModal() { modal.removeAttribute('open'); }

        function loadVouchers() {
            fetch('vouchers_api.php?action=list')
                .then(r => r.json())
                .then(data => {
                    currentData = data;
                    const tbody = document.getElementById('voucher-list');
                    tbody.innerHTML = '';
                    if (data.length === 0) { tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No records.</td></tr>'; return; }
                    data.forEach(v => {
                        const dur = v.duration_min >= 1440 ? (v.duration_min / 1440).toFixed(1) + 'd' : 
                                   (v.duration_min >= 60 ? (v.duration_min / 60).toFixed(1) + 'h' : v.duration_min + 'm');
                        tbody.innerHTML += `
                            <tr>
                                <td><span class="code-pill">${v.code}</span></td>
                                <td style="font-weight:bold;">₱${v.price}</td>
                                <td>${dur}</td>
                                <td><span class="status-badge status-${v.status.toLowerCase()}">${v.status}</span></td>
                                <td style="text-align:center;"><i class="fas fa-times-circle del-icon" onclick="deleteVoucher(${v.id})"></i></td>
                            </tr>
                        `;
                    });
                });
        }

        function generateVouchers() {
            const btn = document.getElementById('btn-gen');
            btn.setAttribute('aria-busy', 'true'); btn.disabled = true;
            const payload = {
                action: 'generate', prefix: document.getElementById('prefix').value, type: 'mixed', price: document.getElementById('price').value, count: document.getElementById('count').value,
                days: document.getElementById('days').value, hrs: document.getElementById('hrs').value, mins: document.getElementById('mins').value, data_limit: document.getElementById('data_limit').value
            };
            fetch('vouchers_api.php', { method: 'POST', body: JSON.stringify({ post: payload }) })
            .then(r => r.json()).then(res => { btn.removeAttribute('aria-busy'); btn.disabled = false; if(res.status === 'SUCCESS') { closeModal(); loadVouchers(); } });
        }

        function deleteVoucher(id) {
            if (!confirm("Delete this voucher?")) return;
            fetch(`vouchers_api.php?action=delete&id=${id}`).then(r => r.json()).then(res => { if(res.status === 'SUCCESS') loadVouchers(); });
        }

        function clearAll() {
            if (!confirm("CRITICAL: Permanently delete ALL vouchers?")) return;
            fetch('vouchers_api.php?action=clear_all').then(r => r.json()).then(res => { if(res.status === 'SUCCESS') loadVouchers(); });
        }

        function printVouchers() {
            const printView = document.getElementById('print-view');
            printView.innerHTML = '';
            currentData.filter(v => v.status === 'UNUSED').forEach(v => {
                const dur = v.duration_min >= 1440 ? (v.duration_min / 1440).toFixed(1) + 'd' : 
                           (v.duration_min >= 60 ? (v.duration_min / 60).toFixed(1) + 'h' : v.duration_min + 'm');
                printView.innerHTML += `<div class="v-card"><div class="v-code">${v.code}</div><div class="v-details">PRICE: ₱${v.price} | TIME: ${dur}</div><div class="v-brand">SILVASYSTEMS ORANGE WIFI</div></div>`;
            });
            if (printView.innerHTML === '') { alert("No unused vouchers to print."); return; }
            window.print();
        }

        document.addEventListener('DOMContentLoaded', loadVouchers);
    </script>
</body>
</html>