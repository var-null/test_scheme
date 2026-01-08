<?php

// neurotopic.ru/scheme/job_1/rect_create.php

$dir_create = 'rects';

if ($_GET['dir'] != '')
    $dir_create = $_GET['dir'];

$size = 30;
$grid = array();

for ($y = 0; $y < $size; $y++) {
    for ($x = 0; $x < $size; $x++) {
        $grid[$y][$x] = 0;
    }
}

$minSide = 5;
$maxSide = 22;

do {
    $w = rand($minSide, $maxSide);
    $h = rand($minSide, $maxSide);
} while ($w == $h);

if (rand(0, 1) == 1) {
    if ($w > $h) {
        $w += rand(1, 4);
    } else {
        $h += rand(1, 4);
    }
}

$halfW = floor($w / 2);
$halfH = floor($h / 2);

$cx = rand($halfW + 1, $size - $halfW - 2);
$cy = rand($halfH + 1, $size - $halfH - 2);

$x1 = $cx - $halfW;
$y1 = $cy - $halfH;
$x2 = $cx + $halfW;
$y2 = $cy + $halfH;

for ($x = $x1; $x <= $x2; $x++) {
    $grid[$y1][$x] = 1;
    $grid[$y2][$x] = 1;
}

for ($y = $y1; $y <= $y2; $y++) {
    $grid[$y][$x1] = 1;
    $grid[$y][$x2] = 1;
}

$data = array(
    'size' => $size,
    'grid' => $grid,
    'label' => 'not_square'
);

$file_name = 'rect_' . time() . '_' . rand(1000, 9999) . '.data';
$file = __DIR__ . '/' . $dir_create . '/' . $file_name;

file_put_contents($file, json_encode($data));

echo $file;

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$url = $scheme . '://' . $host . $path . '/rect_to_points.php?dir=' . $dir_create . '&file=' . $file_name;

echo ' -> ' . file_get_contents($url);
