<?php

// neurotopic.ru/scheme/job_1/kv_create.php

$dir_create = 'kvs';

if($_GET['dir'] != '')
	$dir_create = $_GET['dir'];

$size = 30;
$grid = array();

for ($y = 0; $y < $size; $y++) {
    for ($x = 0; $x < $size; $x++) {
        $grid[$y][$x] = 0;
    }
}

$minSide = 6;
$maxSide = 20;
$side = rand($minSide, $maxSide);

$half = floor($side / 2);

$cx = rand($half + 1, $size - $half - 2);
$cy = rand($half + 1, $size - $half - 2);

$x1 = $cx - $half;
$y1 = $cy - $half;
$x2 = $cx + $half;
$y2 = $cy + $half;

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
    'label' => 'square'
);

$file_name = 'kv_' . time() . '_' . rand(1000,9999) . '.data';

$file = __DIR__ . '/' . $dir_create . '/' . $file_name;
file_put_contents($file, json_encode($data));

echo $file;

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST']; // neurotopic.ru
$path   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); // /scheme/job_1
$url    = $scheme . '://' . $host . $path . '/kv_to_points.php?dir=' . $dir_create . '&file=' . $file_name;

echo ' -> ' . file_get_contents($url);

