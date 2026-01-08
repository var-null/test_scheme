<?php

// neurotopic.ru/scheme/job_1/kvs_paint.php

include_once(__DIR__ . '/menu.php');

$dir = __DIR__ . '/kvs';

if($_GET['dir'] != '')
	$dir = __DIR__ . '/' . $_GET['dir'];

$files = glob($dir . '/*.data');

$dataSet = array();

$max = 50;
$k = 0;

foreach ($files as $file) {
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    if ($data && $k < $max) {
        $dataSet[] = $data;
		$k++;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
.canvas-wrap {
    float: left;
    margin: 10px;
    border: 1px solid #ccc;
}
</style>
</head>
<body>

<br>
<?php
	echo '<b>Файлов</b>: ' . count($files) . '<br>';

 foreach ($dataSet as $i => $item): ?>
<div class="canvas-wrap">
    <canvas id="c<?= $i ?>" width="200" height="200"></canvas>
</div>
<?php endforeach; ?>

<script>
var dataSet = <?= json_encode($dataSet) ?>;

dataSet.forEach(function(item, i) {
    var canvas = document.getElementById('c' + i);
    var ctx = canvas.getContext('2d');

    var size = item.size;
    var grid = item.grid;
    var scale = canvas.width / size;

    ctx.fillStyle = '#fff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    ctx.fillStyle = '#000';

    for (var y = 0; y < size; y++) {
        for (var x = 0; x < size; x++) {
            if (grid[y][x] === 1) {
                ctx.fillRect(
                    x * scale,
                    y * scale,
                    scale,
                    scale
                );
            }
        }
    }
});
</script>

</body>