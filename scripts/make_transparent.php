<?php

function makeTransparentAndSave($srcPath, $dstPng, $dstWebp, $maxDim = 180, $bgThreshold = 230)
{
    if (! file_exists($srcPath)) {
        echo "File $srcPath not found\n";

        return false;
    }

    $src = imagecreatefrompng($srcPath);
    $w = imagesx($src);
    $h = imagesy($src);

    // BFS Flood Fill from all 4 borders
    $visited = array_fill(0, $h, array_fill(0, $w, false));
    $isOutside = array_fill(0, $h, array_fill(0, $w, false));

    $queue = [];

    // Push all border pixels that are near white/grey
    for ($x = 0; $x < $w; $x++) {
        foreach ([0, $h - 1] as $y) {
            $c = imagecolorsforindex($src, imagecolorat($src, $x, $y));
            if ($c['red'] >= $bgThreshold && $c['green'] >= $bgThreshold && $c['blue'] >= $bgThreshold) {
                $queue[] = [$x, $y];
                $visited[$y][$x] = true;
                $isOutside[$y][$x] = true;
            }
        }
    }

    for ($y = 0; $y < $h; $y++) {
        foreach ([0, $w - 1] as $x) {
            if (! $visited[$y][$x]) {
                $c = imagecolorsforindex($src, imagecolorat($src, $x, $y));
                if ($c['red'] >= $bgThreshold && $c['green'] >= $bgThreshold && $c['blue'] >= $bgThreshold) {
                    $queue[] = [$x, $y];
                    $visited[$y][$x] = true;
                    $isOutside[$y][$x] = true;
                }
            }
        }
    }

    $head = 0;
    $dx = [1, -1, 0, 0];
    $dy = [0, 0, 1, -1];

    while ($head < count($queue)) {
        [$cx, $cy] = $queue[$head++];

        for ($i = 0; $i < 4; $i++) {
            $nx = $cx + $dx[$i];
            $ny = $cy + $dy[$i];

            if ($nx >= 0 && $nx < $w && $ny >= 0 && $ny < $h && ! $visited[$ny][$nx]) {
                $visited[$ny][$nx] = true;
                $c = imagecolorsforindex($src, imagecolorat($src, $nx, $ny));
                if ($c['red'] >= $bgThreshold && $c['green'] >= $bgThreshold && $c['blue'] >= $bgThreshold) {
                    $isOutside[$ny][$nx] = true;
                    $queue[] = [$nx, $ny];
                }
            }
        }
    }

    // Now create a truecolor transparent image
    $scale = min($maxDim / $w, $maxDim / $h, 1.0);
    $newW = (int) round($w * $scale);
    $newH = (int) round($h * $scale);

    $fullTrans = imagecreatetruecolor($w, $h);
    imagealphablending($fullTrans, false);
    imagesavealpha($fullTrans, true);
    $transparent = imagecolorallocatealpha($fullTrans, 255, 255, 255, 127);
    imagefilledrectangle($fullTrans, 0, 0, $w, $h, $transparent);

    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            if ($isOutside[$y][$x]) {
                imagesetpixel($fullTrans, $x, $y, $transparent);
            } else {
                $c = imagecolorat($src, $x, $y);
                imagesetpixel($fullTrans, $x, $y, $c);
            }
        }
    }

    // Resize with anti-aliasing
    $dst = imagecreatetruecolor($newW, $newH);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    imagefilledrectangle($dst, 0, 0, $newW, $newH, $transparent);

    imagecopyresampled($dst, $fullTrans, 0, 0, 0, 0, $newW, $newH, $w, $h);

    imagepng($dst, $dstPng, 9);
    imagewebp($dst, $dstWebp, 85);

    imagedestroy($src);
    imagedestroy($fullTrans);
    imagedestroy($dst);

    echo "Saved $dstPng (".filesize($dstPng)." B) and $dstWebp (".filesize($dstWebp)." B)\n";

    return true;
}

$srcM1 = 'C:/Users/usER/.gemini/antigravity-ide/brain/48a5ea22-964e-4900-b2dc-928827c0f2f5/.user_uploaded/media_1793010168578.png';
$srcM2 = 'C:/Users/usER/.gemini/antigravity-ide/brain/48a5ea22-964e-4900-b2dc-928827c0f2f5/.user_uploaded/media_1793010291518.png';

echo "Processing SMKN 20...\n";
makeTransparentAndSave($srcM1, 'public/images/logo-smkn20.png', 'public/images/logo-smkn20.webp', 160, 240);

echo "Processing Jaya Raya...\n";
makeTransparentAndSave($srcM2, 'public/images/logo-jayaraya.png', 'public/images/logo-jayaraya.webp', 160, 240);

// Sync to frontend/images and public/frontend/images
foreach (['frontend/images', 'public/frontend/images'] as $dir) {
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    copy('public/images/logo-smkn20.png', "$dir/logo-smkn20.png");
    copy('public/images/logo-smkn20.webp', "$dir/logo-smkn20.webp");
    copy('public/images/logo-jayaraya.png', "$dir/logo-jayaraya.png");
    copy('public/images/logo-jayaraya.webp', "$dir/logo-jayaraya.webp");
}
echo "ALL SYNCED!\n";
