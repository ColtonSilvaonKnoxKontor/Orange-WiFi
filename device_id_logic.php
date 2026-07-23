<?php // [!] By Colton Silva (chinawaterstealers).
/**
 * SilvaSystems Encrypted Identity Derivation Logic
 * Executed via silvasystems binary
 */

// Context passed from binary: $argv[1]=OS_ID, $argv[2]=Pepper, $argv[3]=ContextBase64
$contextRaw = isset($argv[3]) ? base64_decode($argv[3]) : '{}';
$context = json_decode($contextRaw, true);

function generateJapaneseID() {
    // Fetch CPU Serial directly (available to binary-spanned process)
    $serial = trim((string)shell_exec('/usr/bin/silvasystems cpu'));
    if (empty($serial) || $serial === 'UNKNOWN') return "SYSTEM_ERROR";

    $key = "orangewifideviceid";
    $hash = hash_hmac('sha256', $serial, $key, true);

    $hiragana = "あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよらりるれろわをん";
    $katakana = "アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワヲン";
    
    $kanji = "一右雨円王音下火花貝学気九休玉金銀空月犬見五口校左三山子四糸字耳七車手十出女小上森人水正生青夕石赤千川先早足村大男竹中虫町天田土二日入年白八八百文木本名目力林六引羽雲園遠何科夏家歌画回会海絵外角楽活間丸岩顔汽記帰弓牛魚京強教近兄形計元言原戸古午後語工公広交光考行高黄合谷国黒今才細作算止市矢姉思紙寺自時室社弱首秋週春書少場色食心新親図数西声星晴切雪船線前組走多太体台地池知茶昼長鳥朝直通弟店点電刀冬当東答頭同道読内南肉馬売買麦半番父風分聞米歩母方北毎妹万明鳴門木問夜野矢友用曜羊葉陽養力林立礼話和亜哀愛悪握圧扱安案暗以意易為異移維緯胃衣違遺医井域育壱逸稲飲院陰隠韻右宇烏羽迂雨卯鵜臼渦嘘唄欝嘘欝嘘欝嘘欝嘘欝嘘欝嘘欝嘘欝嘘欝嘘欝"; 

    $hLen = mb_strlen($hiragana, 'UTF-8');
    $kLen = mb_strlen($katakana, 'UTF-8');
    $kjLen = mb_strlen($kanji, 'UTF-8');

    $finalID = "";

    // Segment 1: 2x Hiragana + 2x Katakana (HIKA)
    for($i=0; $i<2; $i++) {
        $val = (ord($hash[$i*2]) << 8) | ord($hash[$i*2+1]);
        $finalID .= mb_substr($hiragana, $val % $hLen, 1, 'UTF-8');
    }
    for($i=2; $i<4; $i++) {
        $val = (ord($hash[$i*2]) << 8) | ord($hash[$i*2+1]);
        $finalID .= mb_substr($katakana, $val % $kLen, 1, 'UTF-8');
    }
    $finalID .= " . ";

    // Segments 2, 3, 4: 4x Kanji each
    for($s=1; $s<4; $s++) {
        for($i=0; $i<4; $i++) {
            $idx = ($s * 4) + $i;
            $val = (ord($hash[$idx*2]) << 8) | ord($hash[$idx*2+1]);
            $finalID .= mb_substr($kanji, $val % $kjLen, 1, 'UTF-8');
        }
        if($s < 3) $finalID .= " . ";
    }

    return $finalID;
}

echo generateJapaneseID();
?>