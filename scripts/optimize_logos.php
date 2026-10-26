<?php

function optimizeImage($src, $dstPng, $dstWebp, $maxDim = 180) {
    if (!file_exists($src)) {
        echo "Source file $src not found.\n";
        return false;
    }
    list($w, $h, $type) = getimagesize($src);
    if ($type == IMAGETYPE_PNG) {
        $im = imagecreatefrompng($src);
    } elseif ($type == IMAGETYPE_JPEG) {
        $im = imagecreatefromjpeg($src);
    } else {
        return false;
    }

    $scale = min($maxDim / $w, $maxDim / $h, 1.0);
    $newW = (int)round($w * $scale);
    $newH = (int)round($h * $scale);

    $dst = imagecreatetruecolor($newW, $newH);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
    imagefilledrectangle($dst, 0, 0, $newW, $newH, $transparent);

    imagecopyresampled($dst, $im, 0, 0, 0, 0, $newW, $newH, $w, $h);

    imagepng($dst, $dstPng, 9);
    imagewebp($dst, $dstWebp, 82);

    imagedestroy($im);
    imagedestroy($dst);
    return true;
}

echo "Optimizing logos...\n";
optimizeImage('public/images/logo-jayaraya.png', 'public/images/logo-jayaraya.png', 'public/images/logo-jayaraya.webp', 160);
optimizeImage('public/images/logo-smkn20.png', 'public/images/logo-smkn20.png', 'public/images/logo-smkn20.webp', 160);

// Sync to frontend/images as well
if (!is_dir('frontend/images')) {
    mkdir('frontend/images', 0755, true);
}
copy('public/images/logo-jayaraya.png', 'frontend/images/logo-jayaraya.png');
copy('public/images/logo-jayaraya.webp', 'frontend/images/logo-jayaraya.webp');
copy('public/images/logo-smkn20.png', 'frontend/images/logo-smkn20.png');
copy('public/images/logo-smkn20.webp', 'frontend/images/logo-smkn20.webp');

echo "SUCCESS! Images optimized and synced.\n";
