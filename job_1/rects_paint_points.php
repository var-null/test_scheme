<?php

// neurotopic.ru/scheme/job_1/rects_paint_points.php

include_once(__DIR__ . '/menu.php');

$dir = __DIR__ . '/rects';

if($_GET['dir'] != '')
	$dir = __DIR__ . '/' . $_GET['dir'];

$files = glob($dir . '/*.points');

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

    var points = item.points;
    if (!points || points.length === 0) return;

    var maxX = 0;
    var maxY = 0;

    points.forEach(function(p) {
        if (p[0] > maxX) maxX = p[0];
        if (p[1] > maxY) maxY = p[1];
    });

    var size = Math.max(maxX + 1, maxY + 1);
    var scale = canvas.width / size;

    ctx.fillStyle = '#fff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    ctx.strokeStyle = '#000';
    ctx.lineWidth = scale / 2;
    ctx.lineCap = 'round';

    ctx.beginPath();
    ctx.moveTo(
        points[0][0] * scale + scale / 2,
        points[0][1] * scale + scale / 2
    );

    for (var i2 = 1; i2 < points.length; i2++) {
        ctx.lineTo(
            points[i2][0] * scale + scale / 2,
            points[i2][1] * scale + scale / 2
        );
    }

    if (item.closed) {
        ctx.lineTo(
            points[0][0] * scale + scale / 2,
            points[0][1] * scale + scale / 2
        );
    }

    ctx.stroke();
});
</script>

</body>
</html>
