<?php

if (!isset($_GET['file'])) {
    exit;
}

$dir_create = 'kvs';
if($_GET['dir'] != '')
    $dir_create = $_GET['dir'];

$file = basename($_GET['file']);
$path = __DIR__ . '/' . $dir_create . '/' . $file;

if (!file_exists($path)) {
    exit;
}

$data = json_decode(file_get_contents($path), true);
$grid = $data['grid'];
$size = $data['size'];

$dirs = array(
    array(1, 0),
    array(0, 1),
    array(-1, 0),
    array(0, -1)
);

$start = null;

for ($y = 0; $y < $size; $y++) {
    for ($x = 0; $x < $size; $x++) {
        if ($grid[$y][$x] == 1) {
            $start = array($x, $y);
            break 2;
        }
    }
}

if (!$start) {
    exit;
}

$dir = 0;
$x = $start[0];
$y = $start[1];

$points = array();
$order = 0;

$points[] = array(
    'x' => $x,
    'y' => $y,
    'order' => $order
);

$visited = array();
$visited[$y . '_' . $x . '_' . $dir] = true;

while (true) {
    $moved = false;

    for ($i = 0; $i < 4; $i++) {
        $ndir = ($dir + 3 + $i) % 4;
        $nx = $x + $dirs[$ndir][0];
        $ny = $y + $dirs[$ndir][1];

        if (isset($grid[$ny][$nx]) && $grid[$ny][$nx] == 1) {
            $x = $nx;
            $y = $ny;
            $dir = $ndir;

            $key = $y . '_' . $x . '_' . $dir;
            if (isset($visited[$key])) {
                break 2;
            }

            $visited[$key] = true;
            $order++;

            $points[] = array(
                'x' => $x,
                'y' => $y,
                'order' => $order
            );

            $moved = true;
            break;
        }
    }

    if (!$moved) {
        break;
    }
}

$out = array(
    'closed' => true,
    'points' => $points
);

$outFile = str_replace('.data', '.points', $file);
file_put_contents(__DIR__ . '/' . $dir_create . '/' . $outFile, json_encode($out));

echo $outFile;
