<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Secure Dashboard Container
 */
require_once __DIR__ . '/../critical/gatekeeper.php';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SILVASYSTEMS Admin</title>
    <link rel="stylesheet" href="/css/pico.min.css">
    <link rel="stylesheet" href="/admin/frontend/css/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Mobile Header -->
        <div class="mobile-header">
            <h3>SILVASYSTEMS</h3>
        </div>

        <!-- Hamburger Button -->
        <button id="mobile-toggle" class="mobile-toggle">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <div id="sidebar-overlay" class="overlay"></div>

        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="brand">
                <h3>SILVASYSTEMS</h3>
            </div>
            <nav>
                <ul>
                    <li><a href="#" data-src="../management/overview.php" class="active"><i class="fas fa-home"></i> Overview</a></li>
                    <li><a href="#" data-src="../management/security_bulletin.php"><i class="fas fa-file-shield"></i> Security Bulletin</a></li>
                    
                    <li style="margin-top: 15px; padding: 0 20px; font-size: 0.8em; text-transform: uppercase; color: #666; font-weight: bold;">Piso WiFi Essential</li>
                    <li><a href="#" data-src="../management/vouchers.php"><i class="fas fa-ticket-alt"></i> Vouchers</a></li>
                    <li><a href="#" data-src="../management/txn.php"><i class="fas fa-history"></i> Transactions</a></li>
                    <li><a href="#" data-src="../management/active_devices.php"><i class="fas fa-wifi"></i> Active Devices</a></li>
                    <li><a href="#" data-src="../management/list_devices.php"><i class="fas fa-list"></i> All Devices</a></li>
                    <li><a href="#" data-src="../management/restricted_devices.php"><i class="fas fa-user-slash"></i> Restricted</a></li>
                    <li><a href="#" data-src="../management/rates_ui.php"><i class="fas fa-tags"></i> Rates</a></li>
                    <li><a href="#" data-src="../management/plugins.php"><i class="fas fa-plug"></i> Plugins</a></li>

                    <li style="margin-top: 15px; padding: 0 20px; font-size: 0.8em; text-transform: uppercase; color: #666; font-weight: bold;">Settings</li>
                    <li><a href="#" data-src="../management/general_settings.php"><i class="fas fa-cog"></i> General Settings</a></li>
                    <li><a href="#" data-src="../management/site_blocker.php"><i class="fas fa-ban"></i> Site Blocker</a></li>
                    <li><a href="#" data-src="../management/radio.php"><i class="fas fa-music"></i> Background Music</a></li>
                    <li><a href="#" data-src="../management/portal_design.php"><i class="fas fa-paint-brush"></i> Portal Design</a></li>
                    <li><a href="#" data-src="menu/chpwd.html"><i class="fas fa-key"></i> Change Password</a></li>
                    <li><a href="#" data-src="menu/admin_restrict.html"><i class="fas fa-shield-alt"></i> Admin Restriction</a></li>
                    <li><a href="#" data-src="menu/help.html?topic=settings_help"><i class="fas fa-question-circle"></i> Help</a></li>
                    
                    <li style="margin-top: 15px; padding: 0 20px; font-size: 0.8em; text-transform: uppercase; color: #666; font-weight: bold;">Networking</li>
                    <li><a href="#" data-src="menu/dos_defense.html"><i class="fas fa-shield-virus"></i> DoS Defense</a></li>
                    <li><a href="#" data-src="menu/speedtest.html"><i class="fas fa-gauge-high"></i> Network Diagnostic</a></li>
                    <li><a href="#" data-src="menu/bandwidth_control.html"><i class="fas fa-tachometer-alt"></i> Bandwidth Control</a></li>
                    <li><a href="#" data-src="menu/net_info.html"><i class="fas fa-network-wired"></i> Information</a></li>
                    <li><a href="#" data-src="menu/net_access_log.html"><i class="fas fa-file-invoice"></i> Access Log</a></li>
                    <li><a href="#" data-src="menu/net_error_log.html"><i class="fas fa-exclamation-circle"></i> Error Log</a></li>
                    <li><a href="#" data-src="menu/net_iptables_nat.html"><i class="fas fa-route"></i> iptables NAT</a></li>
                    <li><a href="#" data-src="menu/help.html?topic=net_help"><i class="fas fa-question-circle"></i> Help</a></li>

                    <li style="margin-top: 15px; padding: 0 20px; font-size: 0.8em; text-transform: uppercase; color: #666; font-weight: bold;">Tools</li>
                    <li><a href="#" data-src="menu/tools_terminal.html"><i class="fas fa-terminal"></i> Terminal</a></li>
                    
                    <li style="margin-top: 15px; padding: 0 20px; font-size: 0.8em; text-transform: uppercase; color: #666; font-weight: bold;">System Logs</li>
                    <li><a href="#" data-src="menu/syslogs_kernel.html"><i class="fas fa-microchip"></i> Kernel Log</a></li>
                    <li><a href="#" data-src="menu/syslogs_auth.html"><i class="fas fa-user-shield"></i> Auth Log</a></li>
                    <li><a href="#" data-src="menu/syslogs_tasks.html"><i class="fas fa-tasks"></i> Task Viewer</a></li>
                    <li><a href="#" data-src="menu/help.html?topic=syslogs_help"><i class="fas fa-question-circle"></i> Help</a></li>

                    <li style="margin-top: 15px; padding: 0 20px; font-size: 0.8em; text-transform: uppercase; color: #666; font-weight: bold;">SSH Access</li>
                    <li><a href="#" data-src="menu/ssh_logs.html"><i class="fas fa-door-open"></i> SSH Logs</a></li>
                    <li><a href="#" data-src="menu/ssh_restrict.html"><i class="fas fa-lock"></i> SSH Restriction</a></li>
                    <li><a href="#" data-src="menu/help.html?topic=ssh_help"><i class="fas fa-question-circle"></i> Help</a></li>

                    <li style="margin-top: 15px; padding: 0 20px; font-size: 0.8em; text-transform: uppercase; color: #666; font-weight: bold;">Identification</li>
                    <li><a href="#" data-src="../management/device_id.php"><i class="fas fa-microchip"></i> Device ID</a></li>
                    <li><a href="#" data-src="menu/licensing_id.html"><i class="fas fa-star"></i> Orange Star ID</a></li>
                    <li><a href="#" data-src="menu/help.html?topic=id_help"><i class="fas fa-question-circle"></i> Help</a></li>

                    <li style="margin-top: 15px; padding: 0 20px; font-size: 0.8em; text-transform: uppercase; color: #666; font-weight: bold;">Actions</li>
                    <li><a href="#" data-src="menu/sys_management.html"><i class="fas fa-cogs"></i> System Management</a></li>
                    <li><a href="#" data-src="menu/developer_key.html"><i class="fas fa-key"></i> Developer Key</a></li>
                    <li><a href="#" data-src="menu/system_updates.html"><i class="fas fa-sync"></i> System Updates</a></li>
                    <li><a href="#" data-src="menu/help.html?topic=action_help"><i class="fas fa-question-circle"></i> Help</a></li>

                    <li style="margin-top: 30px;"><a href="#" data-src="menu/help.html?topic=welcome"><i class="fas fa-info-circle"></i> Global Help</a></li>
                    <li><a href="#" data-src="menu/about.html"><i class="fas fa-circle-info"></i> About</a></li>
                    <li><a href="#" id="logout-btn" style="color: #e53e3e;"><i class="fas fa-power-off"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Window -->
        <main class="main-window">
            <iframe id="content-frame" src="../management/overview.php" frameborder="0"></iframe>
        </main>
    </div>

    <script src="/admin/frontend/js/dashboard.js?v=<?php echo time(); ?>"></script>
    <script>
        function heartbeat() {
            const x = new XMLHttpRequest();
            x.open('HEAD', '../pass.php', true);
            x.onreadystatechange = function() {
                if (x.readyState === 4) {
                    if (x.status === 423) window.top.location.href = '/admin/lockdown.php';
                    else if (x.status === 401) window.top.location.href = '/admin/Login/';
                }
            };
            x.send();
        }
        setInterval(heartbeat, 15000);
    </script>
</body>
</html>