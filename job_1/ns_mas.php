<?php

// neurotopic.ru/scheme/job_1/ns_array.php

include_once(__DIR__ . '/menu.php');

function sigmoid($x) { return 1 / (1 + exp(-$x)); }
function dsigmoid($y) { return $y * (1 - $y); }

/* =====================
   ПАРАМЕТРЫ СЕТИ
===================== */

$size = 30;
$input_len = $size * $size; // 900
$hidden_count = 16;
$output_count = 1;
$lr = 0.2;
$epochs = 2000;

/* =====================
   ИНИЦИАЛИЗАЦИЯ ВЕСОВ
===================== */

$w1 = array();
$b1 = array();
for ($i = 0; $i < $hidden_count; $i++) {
    $b1[$i] = mt_rand()/mt_getrandmax() - 0.5;
    for ($j = 0; $j < $input_len; $j++) {
        $w1[$i][$j] = mt_rand()/mt_getrandmax() - 0.5;
    }
}

$w2 = array();
$b2 = array();
$b2[0] = mt_rand()/mt_getrandmax() - 0.5;
for ($j = 0; $j < $hidden_count; $j++) {
    $w2[0][$j] = mt_rand()/mt_getrandmax() - 0.5;
}

/* =====================
   ЗАГРУЗКА ДАННЫХ
===================== */

$train = array();
$test  = array();

function load_folder($dir, $label) {
    $out = array();
    $files = glob($dir . '/*.data');
    foreach ($files as $file) {
        $json = file_get_contents($file);
        $d = json_decode($json, true);
        if (!$d || !isset($d['grid'])) continue;

        $vec = array();
        for ($y = 0; $y < 30; $y++) {
            for ($x = 0; $x < 30; $x++) {
                $vec[] = $d['grid'][$y][$x];
            }
        }

        $out[] = array(
            'input' => $vec,
            'target' => $label
        );
    }
    return $out;
}

$train = array_merge(
    load_folder(__DIR__.'/kvs', 1),
    load_folder(__DIR__.'/rects', 0)
);

$test = array_merge(
    load_folder(__DIR__.'/kvs_test', 1),
    load_folder(__DIR__.'/rects_test', 0)
);

/* =====================
   ВЕРСТКА ЛОГА
===================== */

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

/* =====================
   ОБУЧЕНИЕ
===================== */

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

$time_end = microtime(true);
$train_time = $time_end - $time_start;

/* =====================
   ТЕСТИРОВАНИЕ
===================== */

echo "<hr><b>TEST</b><br>";

$correct = 0;
foreach ($test as $d) {

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

    $pred = $out > 0.5 ? 1 : 0;
    if ($pred == $target) $correct++;

    echo ($pred ? 'square' : 'not_square') .
         " (target $target) => " . round($out, 3) . "<br>";
}

$accuracy = $correct / max(1, count($test));

/* =====================
   СОХРАНЕНИЕ РЕЗУЛЬТАТА
===================== */

$train_count = count($train); // количество объектов для обучения

$result = array(
    'type' => 'array_pixels',
    'epochs' => $epochs,
    'learning_rate' => $lr,
    'hidden' => $hidden_count,
    'train_time_sec' => $train_time,
    'accuracy' => $accuracy,
    'train_count' => $train_count, // можно сохранить для информации
    'log' => $train_log
);

file_put_contents(
    __DIR__ . '/results/array_model_' . $train_count . '.json',
    json_encode($result)
);


echo "<hr>Accuracy: " . round($accuracy * 100, 2) . "%";
echo "<br>Train time: " . round($train_time, 3) . " sec";
echo '<br><b>Объектов для обучения</b>: ' . $train_count . '<br>';
