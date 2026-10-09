<?php
// create_logo_hd.php - Buat logo aslab HD

$dir = __DIR__ . '/assets/img/';
if (!file_exists($dir)) {
    mkdir($dir, 0777, true);
}

// Buat gambar 500x500 (HD)
$img = imagecreatetruecolor(500, 500);

// Warna
$bg = imagecolorallocate($img, 10, 46, 92);      // Biru tua
$gold = imagecolorallocate($img, 201, 168, 76);   // Emas
$white = imagecolorallocate($img, 255, 255, 255); // Putih
$light_gold = imagecolorallocate($img, 230, 210, 150);

// Background
imagefill($img, 0, 0, $bg);

// Lingkaran luar (border emas tebal)
for ($i = 0; $i < 8; $i++) {
    imageellipse($img, 250, 250, 420 - $i, 420 - $i, $gold);
}

// Lingkaran dalam (biru)
imagefilledellipse($img, 250, 250, 380, 380, $bg);

// Lingkaran dalam (border emas)
imageellipse($img, 250, 250, 370, 370, $gold);

// Teks "ASLAB" besar
$font = __DIR__ . '/arial.ttf';
if (file_exists($font)) {
    imagettftext($img, 80, 0, 135, 270, $gold, $font, 'ASLAB');
    imagettftext($img, 22, 0, 125, 330, $white, $font, 'Asisten Laboratorium');
    imagettftext($img, 18, 0, 140, 365, $light_gold, $font, 'UPI YPTK Padang');
} else {
    // Tanpa font
    imagestring($img, 5, 180, 220, 'ASLAB', $gold);
    imagestring($img, 3, 160, 280, 'Asisten Laboratorium', $white);
    imagestring($img, 3, 170, 310, 'UPI YPTK Padang', $light_gold);
}

// Simpan
imagepng($img, $dir . 'logo-aslab.png');
imagedestroy($img);

echo "✅ Logo Aslab HD berhasil dibuat!<br>";
echo "<img src='assets/img/logo-aslab.png' style='max-height: 200px; border-radius: 50%; border: 3px solid #c9a84c;'><br>";
echo "<br><a href='index.php'>Kembali ke Beranda</a>";
?>