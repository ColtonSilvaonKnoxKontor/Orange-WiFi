<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Sarcastic Barrier - Multi-Persona Edition
 */
http_response_code(403);

$quotes = [
    [
        'msg' => "Watch this guys! I just opened Termux on my phone and pasted a script I found on a shady Telegram group! I'm gonna hack the admin settings and generate free vouchers for everyone! My mom and my friends think I'm a tech genius!",
        'author' => 'Akira Suzuki, The Termux Skid (Age 12)'
    ],
    [
        'msg' => "I've been leaning against this wall all day with my bottle of gin and a pack of cheap cigarettes. My parents are absolutely fed up with my jobless life, but who cares? If I can just bypass this WiFi logic, I'll have enough data to scroll TikTok while I smoke this weed. I'm basically a street legend.",
        'author' => 'Li Qiang, Certified Tambay (Professional Loafer)'
    ],
    [
        'msg' => "Bakit ang mahal? Piso para sa ilang minuto lang? Sa amin sa probinsya, sampung piso maghapon na! Ma-hack nga itong dashboard na ito, baka sakaling maging unli ang internet ko. Madali lang 'to, napanood ko sa TikTok kagabi.",
        'author' => 'Sanjay Patel, The Frugal Complainer'
    ],
    [
        'msg' => "I am a Computer Science student, do not underestimate my power. I know how to use 'Inspect Element' to change the time remaining. I am currently attempting to inject a reverse shell into the SSID field. Any minute now, I will own this Orange Pi!",
        'author' => 'Amitabh Bachchan, Aspiring CS Grad'
    ],
    [
        'msg' => "One peso for 10 minutes? That is daylight robbery! I'll just use my cracked Android phone to spoof the MAC address and piggyback on someone else's session. I'm a high-tier exploiter, I don't pay for bits and bytes.",
        'author' => 'Yuki Tanaka, High School Hacker'
    ],
    [
        'msg' => "Kuya, pwedeng makahingi ng free voucher? Wala kasi akong pera pang-hulog eh. Pero kung ayaw mo, hahack-in ko na lang itong admin panel gamit ang luma kong tablet. Expert ako sa Minecraft, kaya ko rin 'to!",
        'author' => 'Rohan Singh, The Grade School Genius'
    ],
    [
        'msg' => "I have calculated that if I send exactly 1,000,000 requests to the login page per second, the server will crash and default to 'Always Open' mode. My hobbyist intuition is never wrong. This is how real geeks do it.",
        'author' => 'Hiroshi Yamamoto, The Hobbyist Geek'
    ],
    [
        'msg' => "Ang hirap maging tambay, walang pang-load. Pero sabi ni idol sa YouTube, burahin ko lang daw yung password file sa server, magiging admin na ako. Nasaan na ba yung 'Delete' button dito sa browser?",
        'author' => 'Chen Wei, Professional Street Sleeper'
    ],
    [
        'msg' => "Hala, bakit blocked na ako? Sabi ng friend ko dati, alam niya daw yung default admin password kaya unli internet kami habang buhay. Bakit ngayon kailangan na ng hulog? Ang daya naman ng owner ng wifi na 'to!",
        'author' => 'Mei Ling, Expecting Unli-Wifi'
    ],
    [
        'msg' => "Yawa man ini! Isang piso, ten minutes lang? Kadako ba sang kawat! Gusto ko lang naman mag-FB, bakit kailangan pa magbayad? I-hack ko na lang itong admin, madali lang 'yan sa mga katulad kong gwapo.",
        'author' => 'Rajesh Koothrappali, The Angry Bisaya'
    ],
    [
        'msg' => "Easy lang 'yan guys. Kung kaya ko ngang i-hack yung Mobile Legends para mag-God Mode at No-Recoil sa PUBG gamit ang Lucky Patcher, sisiw lang sa akin itong Piso WiFi dashboard. Mapapalitan ko ang rates nito sa 0 pesos per year!",
        'author' => 'Zhang Wei, The MLBB Script Kiddy'
    ],
    [
        'msg' => "Alam ko 'to, nabasa ko sa PHCorner! Ang default password niyan 'admin' or '12345' lang. Pag nakapasok ako, ibubulong ko sa inyo yung config para unli net na tayo habambuhay. Galing ko talaga, 'di ba?",
        'author' => 'Takeshi Sato, Feeling Cool Hackerist'
    ],
    [
        'msg' => "Naku! Ang mahal naman nito! Isang piso para sa ilang minuto lang? Sobrang mahal talaga! Mabuti pa sigurong matulog na lang ako kaysa mag-hulog ng barya sa wifi na ito. I-hack ko na lang itong admin dashboard, baka sakaling makalusot ako. Napaka-kuripot talaga ng may-ari nito!",
        'author' => 'Kalingga Kumar, The Frugal Customer'
    ],
    [
        'msg' => "I have cross-referenced the HTML source code with my knowledge of Grade 9 ICT. I am currently injecting a SQL command into your CSS file. Prepare to be pwned! Or maybe I'll just cry because the 'Login' button didn't work.",
        'author' => 'Rahul Sharma, The ICT Student'
    ]
];

shuffle($quotes);
$displayQuotes = array_slice($quotes, 0, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ACCESS DENIED, BRAINIAC</title>
    <link rel="stylesheet" href="/css/pico.min.css">
    <style>
        body { background-color: #050505; color: #ff6600; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; font-family: 'Courier New', monospace; }
        .denied-card { background: #111; padding: 3rem; border: 4px dashed #ff6600; border-radius: 1rem; width: 100%; max-width: 700px; text-align: center; }
        h1 { font-size: 2.2rem; color: #ff0000; text-transform: uppercase; margin-bottom: 1rem; }
        .funny-msg { font-size: 1.1rem; color: #eee; margin-bottom: 2rem; }
        .quote-box { margin: 1.5rem 0; padding: 1.2rem; background: #222; border-radius: 8px; text-align: left; font-style: italic; color: #ffa500; border: 1px solid #444; position: relative; }
        .quote-author { display: block; text-align: right; margin-top: 8px; font-size: 0.85rem; color: #777; font-style: normal; }
        .footer-snark { font-size: 0.8rem; color: #555; margin-top: 2rem; border-top: 1px solid #333; padding-top: 1rem; }
    </style>
</head>
<body>
    <div class="denied-card">
        <h1>🚨 OH NO! A HACKER! 🚨</h1>
        <p class="funny-msg">Wait, do you actually think that by guessing the password or clicking around like a maniac, you'll magically get a <strong>Free Voucher</strong> or <strong>Unlimited Internet</strong>?</p>
        
        <?php foreach($displayQuotes as $q): ?>
        <div class="quote-box">
            "<?php echo htmlspecialchars($q['msg']); ?>"
            <span class="quote-author">— <?php echo htmlspecialchars($q['author']); ?></span>
        </div>
        <?php endforeach; ?>

        <p style="color: #ff4444; font-weight: bold; margin-top: 2rem;">REALITY CHECK: ACCESS DENIED.</p>
        <p>Tinkering with SilvaSystems isn't a replacement for having a job or actual skills. Go find a coin or a productive hobby.</p>

        <button class="primary" onclick="window.location.href='/'">Return to your sad reality</button>
        
        <div class="footer-snark">
            SilvaSystems Anti-Stupidity Shield: ACTIVE<br>
            Delusion of Competence: 100% | Actual Hacking Ability: 0%
        </div>
    </div>
</body>
</html>