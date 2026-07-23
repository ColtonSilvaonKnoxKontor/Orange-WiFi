/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Global Security Observer
 */
(function() {
    function verifyStatus() {
        const x = new XMLHttpRequest();
        x.open('GET', '/admin/pass.php', true);
        x.onreadystatechange = function() {
            if (x.readyState === 4) {
                if (x.responseURL && x.responseURL.includes('access_denied.php')) {
                    window.top.location.href = '/admin/access_denied.php';
                    return;
                }
                if (x.responseURL && x.responseURL.includes('lockdown.php')) {
                    window.top.location.href = '/admin/lockdown.php';
                    return;
                }
                if (x.status === 403) window.top.location.href = '/admin/access_denied.php';
                if (x.status === 423) window.top.location.href = '/admin/lockdown.php';
                if (x.status === 401) window.top.location.href = '/admin/Login/';
            }
        };
        x.send();
    }
    verifyStatus();
    setInterval(verifyStatus, 15000);
})();