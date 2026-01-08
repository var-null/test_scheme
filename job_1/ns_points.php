<?php

// neurotopic.ru/scheme/job_1/ns_points.php

set_time_limit(2400);//$long_time + 10
ini_set('max_execution_time', 2400);//$long_time + 10
ini_set('max_input_time', 2400);
ini_set('session.gc_maxlifetime', 2400);

include_once(__DIR__ . '/menu.php');

function sigmoid($x) { return 1 / (1 + exp(-$x)); }
function dsigmoid($y) { return $y * (1 - $y); }

/* ===============================
   ПАРАМЕТРЫ СЕТИ
=============================== */

$input_len     = 60;
$hidden_count  = 8;
$output_count  = 1;
$lr            = 0.2;
$epochs        = 2000;

/* ===============================
   ИНИЦИАЛИЗАЦИЯ ВЕСОВ
=============================== */

$w1 = array(); 
$b1 = array();
for ($i = 0; $i < $hidden_count; $i++) {
    $b1[$i] = mt_rand() / mt_getrandmax() - 0.5;
    for ($j = 0; $j < $input_len; $j++) {
        $w1[$i][$j] = mt_rand() / mt_getrandmax() - 0.5;
    }
}

$w2 = array(); 
$b2 = array();
for ($i = 0; $i < $output_count; $i++) {
    $b2[$i] = mt_rand() / mt_getrandmax() - 0.5;
    for ($j = 0; $j < $hidden_count; $j++) {
        $w2[$i][$j] = mt_rand() / mt_getrandmax() - 0.5;
    }
}

/* ===============================
   ЗАГРУЗКА ДАННЫХ
=============================== */

function load_dataset_from_dir($dir, $label) {
    $files = glob($dir . '/*.points');
    $dataset = array();

    foreach ($files as $file) {
        $json = file_get_contents($file);
        $d = json_decode($json, true);
        if (!$d || !isset($d['points'])) continue;

        $pts = $d['points'];
        $vec = array();
        $max_pts = 30;

        for ($i = 0; $i < $max_pts; $i++) {
            if (isset($pts[$i])) {
                $vec[] = $pts[$i][0] / 30;
                $vec[] = $pts[$i][1] / 30;
            } else {
                $last = end($vec);
                $vec[] = $last;
                $vec[] = $last;
            }
        }

        $dataset[] = array(
            'input'  => $vec,
            'target' => $label
        );
    }

    return $dataset;
}

$train = array_merge(
    load_dataset_from_dir(__DIR__ . '/kvs', 1),
    load_dataset_from_dir(__DIR__ . '/rects', 0)
);

$test_squares = load_dataset_from_dir(__DIR__ . '/kvs_test', 1);
$test_rects  = load_dataset_from_dir(__DIR__ . '/rects_test', 0);

/* ===============================
   ОБУЧЕНИЕ
=============================== */

echo '
<style>
.col {
    float: left;
    width: 48%;
    padding: 1%;
    box-sizing: border-box;
    font-family: monospace;
    font-size: 13px;
}
.clear { clear: both; }
</style>

<div class="col" id="log_left"></div>
<div class="col" id="log_right"></div>
<div class="clear"></div>
';


$train_log = array();
$time_start = microtime(true);

for ($epoch = 1; $epoch <= $epochs; $epoch++) {

    $err = 0;

    foreach ($train as $d) {

        $inp = $d['input'];
        $target = $d['target'];

        $hidden = array();
        for ($i = 0; $i < $hidden_count; $i++) {
            $s = $b1[$i];
            for ($j = 0; $j < $input_len; $j++) {
                $s += $inp[$j] * $w1[$i][$j];
            }
            $hidden[$i] = sigmoid($s);
        }

        $out = $b2[0];
        for ($i = 0; $i < $hidden_count; $i++) {
            $out += $hidden[$i] * $w2[0][$i];
        }
        $out = sigmoid($out);

        $err += pow($target - $out, 2);

        $dout = ($target - $out) * dsigmoid($out);

        for ($j = 0; $j < $hidden_count; $j++) {
            $w2[0][$j] += $lr * $dout * $hidden[$j];
        }
        $b2[0] += $lr * $dout;

        for ($i = 0; $i < $hidden_count; $i++) {
            $dh = $dout * $w2[0][$i] * dsigmoid($hidden[$i]);
            for ($j = 0; $j < $input_len; $j++) {
                $w1[$i][$j] += $lr * $dh * $inp[$j];
            }
            $b1[$i] += $lr * $dh;
        }
    }

	if ($epoch % 50 == 0) {

		$time_now = microtime(true) - $time_start;

		$train_log[] = array(
			'epoch' => $epoch,
			'error' => $err,
			'time'  => $time_now // вот сюда добавляем время
		);

		$div = ($epoch <= 1000) ? 'log_left' : 'log_right';
		echo "<script>
			document.getElementById('$div').innerHTML +=
			'Epoch $epoch error: $err (time: " . round($time_now, 2) . "s)<br>';
		</script>";
	}

}


$train_time = microtime(true) - $time_start;

/* ===============================
   ТЕСТИРОВАНИЕ
=============================== */

function test_dataset($dataset, $w1, $b1, $w2, $b2, $hidden_count, $input_len) {

    $correct = 0;
    $values = array();

    foreach ($dataset as $d) {

        $inp = $d['input'];

        $hidden = array();
        for ($i = 0; $i < $hidden_count; $i++) {
            $s = $b1[$i];
            for ($j = 0; $j < $input_len; $j++) {
                $s += $inp[$j] * $w1[$i][$j];
            }
            $hidden[$i] = sigmoid($s);
        }

        $out = $b2[0];
        for ($i = 0; $i < $hidden_count; $i++) {
            $out += $hidden[$i] * $w2[0][$i];
        }
        $out = sigmoid($out);

        $pred = $out > 0.5 ? 1 : 0;
        if ($pred == $d['target']) $correct++;

        $values[] = $out;
    }

    return array(
        'accuracy' => count($dataset) ? $correct / count($dataset) : 0,
        'values'   => $values
    );
}

$res_sq   = test_dataset($test_squares, $w1, $b1, $w2, $b2, $hidden_count, $input_len);
$res_rect = test_dataset($test_rects,  $w1, $b1, $w2, $b2, $hidden_count, $input_len);

/* ===============================
   ВЫВОД НА ЭКРАН
=============================== */

echo "<hr><b>TEST</b><br>";

foreach ($test_squares as $d) {
    $inp = $d['input'];
    $hidden = array();
    for ($i = 0; $i < $hidden_count; $i++) {
        $s = $b1[$i];
        for ($j = 0; $j < $input_len; $j++) {
            $s += $inp[$j] * $w1[$i][$j];
        }
        $hidden[$i] = sigmoid($s);
    }
    $out = $b2[0];
    for ($i = 0; $i < $hidden_count; $i++) {
        $out += $hidden[$i] * $w2[0][$i];
    }
    $out = sigmoid($out);

    $pred = $out > 0.5 ? 1 : 0;
    echo ($pred ? 'square' : 'not_square') . " (target {$d['target']}) => " . round($out, 3) . "<br>";
}

foreach ($test_rects as $d) {
    $inp = $d['input'];
    $hidden = array();
    for ($i = 0; $i < $hidden_count; $i++) {
        $s = $b1[$i];
        for ($j = 0; $j < $input_len; $j++) {
            $s += $inp[$j] * $w1[$i][$j];
        }
        $hidden[$i] = sigmoid($s);
    }
    $out = $b2[0];
    for ($i = 0; $i < $hidden_count; $i++) {
        $out += $hidden[$i] * $w2[0][$i];
    }
    $out = sigmoid($out);

    $pred = $out > 0.5 ? 1 : 0;
    echo ($pred ? 'square' : 'not_square') . " (target {$d['target']}) => " . round($out, 3) . "<br>";
}


/* ===============================
   СОХРАНЕНИЕ РЕЗУЛЬТАТОВ
=============================== */

$train_count = count($train); // количество объектов для обучения

$result = array(
    'meta' => array(
        'model'        => 'points_v1',
        'train_time'   => $train_time,
        'epochs'       => $epochs,
        'hidden_count' => $hidden_count,
        'input_len'    => $input_len,
		'train_count' => $train_count, // можно сохранить для информации
    ),
    'train' => array(
        'log' => $train_log
    ),
    'test' => array(
        'squares' => $res_sq,
        'rectangles' => $res_rect
    )
);

$file = __DIR__ . '/results/points_model_' . $train_count . '.json';
file_put_contents($file, json_encode($result));

// echo "\nSaved to: $file\n";
echo "<br>Squares accuracy: " . round($res_sq['accuracy'], 3);
echo "<br>Rectangles accuracy: " . round($res_rect['accuracy'], 3);
echo '<br>Объектов для обучения: ' . $train_count;
