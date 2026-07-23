document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('.sidebar nav a');
    const iframe = document.getElementById('content-frame');
    const logoutBtn = document.getElementById('logout-btn');
    const mobileToggle = document.getElementById('mobile-toggle');
    const sidebar = document.querySelector('.sidebar');
    const container = document.body; // Target body for menu-open class
    const overlay = document.getElementById('sidebar-overlay');

    function closeMenu() {
        sidebar.classList.remove('open');
        container.classList.remove('menu-open');
    }

    // Mobile Toggle
    if(mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            container.classList.toggle('menu-open');
        });
    }

    // Close menu when clicking overlay
    if(overlay) {
        overlay.addEventListener('click', closeMenu);
    }

    links.forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.id === 'logout-btn') return; 

            e.preventDefault();
            const src = this.getAttribute('data-src');
            if(!src || src === '#') return;
            
            // SECURITY CHECK ON EVERY CLICK
            const check = new XMLHttpRequest();
            check.open('HEAD', '../pass.php', true);
            check.onreadystatechange = function() {
                if (check.readyState === 4) {
                    if (check.status === 423) {
                        window.top.location.href = '/admin/lockdown.php';
                    } else {
                        // Update Iframe only if safe
                        iframe.src = src;
                        links.forEach(l => l.classList.remove('active'));
                        link.classList.add('active');
                        
                        // Auto-close menu on mobile after selection
                        if (window.innerWidth <= 992) {
                            closeMenu();
                        }
                    }
                }
            };
            check.send();
        });
    });

    if(logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if(confirm("Are you sure you want to logout?")) {
                const x = new XMLHttpRequest();
                x.addEventListener('readystatechange', () => {
                    if( x.readyState === 4 && x.status === 200 ) {
                        window.top.location.href = '/admin/Login/';
                    }
                });
                x.open('DELETE', '../pass.php');
                x.send();
            }
        });
    }
});