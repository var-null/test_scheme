<?php

header('Content-Type: text/html; utf-8; charset=UTF-8');

$basePath = __DIR__ . '/results/';

$pointsFile = $basePath . 'points_model_' . $currentModel . '.json';
$arrayFile  = $basePath . 'array_model_' . $currentModel . '.json';

function preparePlotDataFromLogTime($data)
{
    $out = [];

    if (!isset($data['log'])) {
        return $out;
    }

    foreach ($data['log'] as $row) {
        if (!isset($row['time']) || !isset($row['error'])) {
            continue;
        }

        $out[] = [
            (float)$row['time'],
            (float)$row['error']
        ];
    }

    return $out;
}

function interpolatePlotTime($data)
{
    $out = [];

    if (count($data) < 2) {
        return $out;
    }

    for ($i = 0; $i < count($data) - 1; $i++) {
        $t1 = $data[$i][0];
        $v1 = $data[$i][1];
        $t2 = $data[$i + 1][0];
        $v2 = $data[$i + 1][1];

        if ($t2 <= $t1) {
            continue;
        }

        for ($t = $t1; $t <= $t2; $t += 0.01) {
            $k = ($t - $t1) / ($t2 - $t1);
            $v = $v1 + ($v2 - $v1) * $k;
            $out[] = [$t, $v];
        }
    }

    return $out;
}

$series = [];

if (file_exists($pointsFile)) {
    $json = json_decode(file_get_contents($pointsFile), true);

    if (isset($json['train']['log'])) {
        $plot = preparePlotDataFromLogTime($json['train']);
    } elseif (isset($json['log'])) {
        $plot = preparePlotDataFromLogTime($json);
    } else {
        $plot = [];
    }

    $plot = interpolatePlotTime($plot);

    if (!empty($plot)) {
        $series[] = [
            'label' => 'Обучение по координатам',
            'data'  => $plot
        ];
    }
}

if (file_exists($arrayFile)) {
    $json = json_decode(file_get_contents($arrayFile), true);

    if (isset($json['log'])) {
        $plot = preparePlotDataFromLogTime($json);
        $plot = interpolatePlotTime($plot);

        if (!empty($plot)) {
            $series[] = [
                'label' => 'Обучение по массивам',
                'data'  => $plot
            ];
        }
    }
}

?>
<div id="ns_compare_time_plot" style="width:100%;height:400px;"></div>

<script type="text/javascript">
(function () {

    var series = <?php echo json_encode($series); ?>;

    if (!series.length) {
        return;
    }

    for (var i = 0; i < series.length; i++) {
        series[i].lines  = { show: true };
        series[i].points = { show: false };
    }

    $.plot(
        $("#ns_compare_time_plot"),
        series,
        {
            grid: {
                hoverable: true,
                clickable: true,
                borderWidth: 1
            },
            xaxis: {
                tickDecimals: 1,
                axisLabel: "Время (сек)"
            },
            yaxis: {
                min: 0,
                axisLabel: "Ошибка"
            },
            legend: {
                position: "ne"
            }
        }
    );

})();
</script>
