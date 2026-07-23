<?php // [!] By Colton Silva (chinawaterstealers).
/**
 * SilvaSystems Content Filter Block Page
 * Night Club / Cybersecurity Aesthetic (Professional Sarcasm)
 */

$quotes = [
    "Akala mo siguro walang nakakakita sa 'yo no? Bantay-sarado ang network, pre.",
    "Bawasan ang panonood ng malaswa, dagdagan ang pag-aaral. Sayang ang data mo.",
    "Huwag mong ubusin ang lakas mo sa walang kwentang bagay. Matulog ka na lang.",
    "Ang dumi ng isip mo, kasing dumi ng baha sa Manila. Mag-isip ka nga.",
    "Mag-vouchers ka na lang, mas may pakinabang pa sa 'yo kaysa sa kahibangan mo.",
    "Walang himala, may firewall lang talaga. Subukan mo ulit sa panaginip mo.",
    "Hindi sapat ang 'Incognito' para itago ang kalokohan mo. Kitang-kita dito.",
    "Pagod ka na ba? Magpahinga ka na, huwag mag-browse ng kung ano-anong dumi.",
    "Ang internet ay para sa kaalaman, hindi para sa iyong maruming pantasya.",
    "Mahiya ka naman sa katabi mo, kitang-kita sa screen mo ang ginagawa mo."
];

$randomQuote = $quotes[array_rand($quotes)];
$blockedSite = htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'Unknown Destination');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ACCESS DENIED | SilvaSystems</title>
    <style>
        :root {
            --neon-pink: #ff007f;
            --neon-blue: #00f2ff;
            --night-bg: #0a0a0c;
        }
        body {
            background-color: var(--night-bg);
            color: #fff;
            font-family: system-ui, -apple-system, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            overflow: hidden;
        }
        .container {
            text-align: center;
            padding: 3rem;
            border: 2px solid var(--neon-pink);
            border-radius: 24px;
            background: rgba(255, 0, 127, 0.03);
            box-shadow: 0 0 30px rgba(255, 0, 127, 0.1), inset 0 0 20px rgba(255, 0, 127, 0.05);
            max-width: 550px;
            width: 90%;
            position: relative;
            backdrop-filter: blur(15px);
            z-index: 5;
        }
        h1 {
            font-size: 3.5rem;
            margin: 0;
            color: var(--neon-pink);
            text-shadow: 0 0 10px var(--neon-pink);
            text-transform: uppercase;
            letter-spacing: 8px;
            font-weight: 900;
        }
        .insult {
            font-size: 1.1rem;
            margin: 1.5rem 0;
            color: var(--neon-blue);
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .quote-box {
            background: rgba(0, 0, 0, 0.6);
            padding: 1.5rem;
            border-radius: 12px;
            border-left: 4px solid var(--neon-blue);
            margin: 2rem 0;
            font-style: italic;
            font-size: 1.1rem;
            color: #e2e8f0;
            line-height: 1.5;
        }
        .site-info {
            font-size: 0.75rem;
            color: #475569;
            margin-top: 2rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .site-value {
            color: #64748b;
            font-family: monospace;
            font-size: 0.9rem;
        }
        .scanline {
            width: 100%;
            height: 100%;
            z-index: 1;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.06), rgba(0, 255, 0, 0.02), rgba(0, 0, 255, 0.06));
            background-size: 100% 4px, 3px 100%;
            position: absolute;
            top: 0; left: 0;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="scanline"></div>
    <div class="container">
        <svg style="width:64px; height:64px; color:var(--neon-pink); margin-bottom: 1.5rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        <h1>HALT!</h1>
        <div class="insult">
            Trying to get a little "extra" service tonight? Nice try.
        </div>
        <p style="color: #94a3b8; font-size: 0.95rem;">Your questionable desires have been intercepted by the system firewall.</p>
        
        <div class="quote-box">
            "<?php echo $randomQuote; ?>"
        </div>

        <p style="font-size: 0.85rem; color: #64748b;">
            Access to this sector is restricted. Return to the portal and use the internet for something useful.
        </p>

        <div class="site-info">Blocked Destination</div>
        <div class="site-value"><?php echo $blockedSite; ?></div>
    </div>
</body>
</html>