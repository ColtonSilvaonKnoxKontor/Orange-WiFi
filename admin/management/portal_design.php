<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Design</title>
    <link rel="stylesheet" href="/css/pico.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { padding: 20px; background-color: #f8fafc; min-height: 100vh; }
        .header-box { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 3px solid #ff6600; padding-bottom: 15px; }
        .header-box h2 { margin: 0; color: #1a202c; text-transform: uppercase; font-weight: 900; letter-spacing: 1px; }
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .card h5 { margin-bottom: 20px; color: #4a5568; font-weight: 800; text-transform: uppercase; font-size: 0.9rem; display: flex; align-items: center; gap: 10px; }
        
        .upload-zone { border: 2px dashed #cbd5e0; border-radius: 15px; padding: 30px; text-align: center; transition: all 0.3s; background: #f8fafc; cursor: pointer; position: relative; }
        .upload-zone:hover { border-color: #ff6600; background: #fffaf0; }
        .upload-zone i { font-size: 2rem; color: #a0aec0; margin-bottom: 10px; display: block; }
        #file-input { position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer; }
        
        .preview-box { margin-top: 20px; border-radius: 10px; overflow: hidden; display: none; border: 1px solid #eee; }
        #img-preview { width: 100%; height: auto; display: block; }
        
        .status-msg { margin-top: 15px; font-size: 0.85rem; font-weight: 600; padding: 10px; border-radius: 8px; display: none; }
        .status-success { background: #d1fae5; color: #065f46; display: block; }
        .status-error { background: #fee2e2; color: #991b1b; display: block; }

        .font-sample { padding: 15px; background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0; font-size: 1.2rem; margin-top: 10px; min-height: 100px; display: flex; align-items: center; justify-content: center; text-align: center; }
        .badge-size { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
        .warning-text { font-size: 0.7rem; color: #e53e3e; margin-top: 5px; font-weight: 700; display: none; }
    </style>
    <script src="../frontend/menu/security_check.js"></script>
</head>
<body>
    <main>
        <div class="header-box"><h2>Portal Design</h2></div>

        <div class="grid">
            <section>
                <div class="card">
                    <h5><i class="fas fa-image"></i> Background Image</h5>
                    
                    <div id="current-bg-section" style="margin-bottom: 15px; display: none;">
                        <label style="font-size: 0.75rem; color: #64748b;">Current Active Background:</label>
                        <div style="border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; background: #f1f5f9; margin-top: 5px;">
                            <img id="current-bg-img" src="" style="width: 100%; height: auto; max-height: 200px; object-fit: contain; display: block;">
                        </div>
                    </div>

                    <div class="upload-zone" id="drop-zone">
                        <i class="fas fa-cloud-upload-alt"></i> Click or Drag Background Image
                        <input type="file" id="file-input" accept="image/jpeg,image/png">
                    </div>
                    <div class="preview-box" id="preview-container"><img id="img-preview" src="#"></div>
                    <button id="upload-btn" class="primary" style="width: 100%; margin-top: 20px; display: none;" onclick="performUpload()">APPLY BACKGROUND</button>
                </div>
            </section>

            <section>
                <div class="card">
                    <h5><i class="fas fa-font"></i> Portal Typography</h5>
                    <label>Select System Font</label>
                    <select id="font-select" onchange="previewFont()">
                        <option value="">Standard System Font</option>
                    </select>
                    <div id="size-info" style="margin-bottom: 10px;"></div>
                    <div id="size-warning" class="warning-text"><i class="fas fa-exclamation-triangle"></i> THIS FONT IS HEAVY! May cause slow loading on Android.</div>
                    
                    <div id="font-preview-box" style="display:none;">
                        <label>Multi-Language Preview:</label>
                        <div class="font-sample" id="font-sample-text">
                            <div>
                                ABC abc 123<br>
                                <span style="font-size: 0.9rem; color: #666; display: block; margin-top: 10px;">
                                    こんにちは (JP) | 你好 (CN) | مرحبا (AR)
                                </span>
                            </div>
                        </div>
                        <button class="secondary" style="width:100%; margin-top:15px;" onclick="saveFont()">APPLY TYPOGRAPHY</button>
                    </div>
                </div>
            </section>
        </div>
        <div id="status" class="status-msg"></div>
    </main>

    <script>
        const fontSelect = document.getElementById('font-select');
        const sampleText = document.getElementById('font-sample-text');
        const fontPreviewBox = document.getElementById('font-preview-box');
        const sizeInfo = document.getElementById('size-info');
        const sizeWarning = document.getElementById('size-warning');
        let fontData = [];

        function loadInitial() {
            fetch('portal_design_api.php')
                .then(r => r.json())
                .then(data => {
                    fontData = data.fonts;
                    data.fonts.forEach(f => {
                        const opt = document.createElement('option');
                        opt.value = f.file;
                        opt.innerText = f.name.replace(/-/g, ' ');
                        fontSelect.appendChild(opt);
                    });
                    if (data.current.font) {
                        fontSelect.value = data.current.font;
                        previewFont();
                    }
                    if (data.current.background) {
                        document.getElementById('current-bg-img').src = data.current.background + '?t=' + new Date().getTime();
                        document.getElementById('current-bg-section').style.display = 'block';
                    }
                });
        }

        function previewFont() {
            const file = fontSelect.value;
            fontPreviewBox.style.display = 'block';

            if (!file) {
                sampleText.style.fontFamily = 'system-ui, -apple-system, sans-serif';
                sizeInfo.innerHTML = '';
                sizeWarning.style.display = 'none';
                return;
            }
            
            const font = fontData.find(f => f.file === file);
            sizeInfo.innerHTML = `<span class="badge badge-size">${font.size}</span>`;
            
            // Show warning if font > 2MB
            sizeWarning.style.display = parseFloat(font.size) > 2 ? 'block' : 'none';

            const fontName = 'DynamicFont';
            const newStyle = document.createElement('style');
            newStyle.appendChild(document.createTextNode(`@font-face { font-family: '${fontName}'; src: url('/fonts/${file}'); font-display: swap; }`));
            document.head.appendChild(newStyle);
            
            sampleText.style.fontFamily = `'${fontName}', sans-serif`;
        }

        function saveFont() {
            const font = fontSelect.value;
            fetch('portal_design_api.php?action=save_font', {
                method: 'POST',
                body: JSON.stringify({ post: { font: font } })
            }).then(r => r.json()).then(res => {
                showStatus(res.msg || res.error, res.status === 'SUCCESS' ? 'success' : 'error');
            });
        }

        const fileInput = document.getElementById('file-input');
        fileInput.addEventListener('change', e => { if(e.target.files[0]) handleFile(e.target.files[0]); });
        function handleFile(file) {
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('img-preview').src = e.target.result;
                document.getElementById('preview-container').style.display = 'block';
                document.getElementById('upload-btn').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
        function performUpload() {
            const formData = new FormData();
            formData.append('background', fileInput.files[0]);
            fetch('portal_design_api.php', { method: 'POST', body: formData })
            .then(r => r.json()).then(res => {
                showStatus(res.msg || res.error, res.status === 'SUCCESS' ? 'success' : 'error');
                if(res.status === 'SUCCESS' && res.path) {
                    document.getElementById('current-bg-img').src = res.path + '?t=' + new Date().getTime();
                    document.getElementById('current-bg-section').style.display = 'block';
                    document.getElementById('preview-container').style.display = 'none';
                    document.getElementById('upload-btn').style.display = 'none';
                }
            });
        }
        function showStatus(msg, type) {
            const s = document.getElementById('status');
            s.innerText = msg; 
            s.className = 'status-msg status-' + type; 
            s.style.display = 'block';
            s.style.opacity = '1';
            s.style.transition = 'none';

            // Auto-fade after 3 seconds
            setTimeout(() => {
                s.style.transition = 'opacity 1s ease';
                s.style.opacity = '0';
                setTimeout(() => { s.style.display = 'none'; }, 1000);
            }, 3000);
        }

        document.addEventListener('DOMContentLoaded', loadInitial);
    </script>
</body>
</html>