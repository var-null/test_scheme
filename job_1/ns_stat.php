<?php

header('Content-Type: text/html; utf-8; charset=UTF-8');

$basePath = __DIR__ . '/results/';

$pointsFile = $basePath . 'points_model_' . $currentModel . '.json';
$arrayFile  = $basePath . 'array_model_' . $currentModel . '.json';

function preparePlotDataFromLog($data)
{
    $out = [];

    if (!isset($data['log'])) {
        return $out;
    }

    foreach ($data['log'] as $row) {
        if (!isset($row['epoch']) || !isset($row['error'])) {
            continue;
        }

        $out[] = [
            (int)$row['epoch'],
            (float)$row['error']
        ];
    }

    return $out;
}

function interpolatePlot($data)
{
    $out = [];

    if (count($data) < 2) {
        return $out;
    }

    for ($i = 0; $i < count($data) - 1; $i++) {
        $e1 = $data[$i][0];
        $v1 = $data[$i][1];
        $e2 = $data[$i + 1][0];
        $v2 = $data[$i + 1][1];

        for ($e = $e1; $e <= $e2; $e++) {
            $t = ($e - $e1) / ($e2 - $e1);
            $v = $v1 + ($v2 - $v1) * $t;
            $out[] = [$e, $v];
        }
    }

    return $out;
}

$series = [];

if (file_exists($pointsFile)) {
    $json = json_decode(file_get_contents($pointsFile), true);

    if (isset($json['train']['log'])) {
        $plot = preparePlotDataFromLog($json['train']);
    } elseif (isset($json['log'])) {
        $plot = preparePlotDataFromLog($json);
    } else {
        $plot = [];
    }

    $plot = interpolatePlot($plot);

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
        $plot = preparePlotDataFromLog($json);
        $plot = interpolatePlot($plot);

        if (!empty($plot)) {
            $series[] = [
                'label' => 'Обучение по массивам',
                'data'  => $plot
            ];
        }
    }
}

?>
<div id="ns_compare_plot" style="width:100%;height:400px;"></div>

<script type="text/javascript">
$(function () {

    var series = <?php echo json_encode($series); ?>;

    if (!series.length) {
        return;
    }

    for (var i = 0; i < series.length; i++) {
        series[i].lines  = { show: true };
        series[i].points = { show: false };
    }

    $.plot(
        $("#ns_compare_plot"),
        series,
        {
            grid: {
                hoverable: true,
                clickable: true,
                borderWidth: 1
            },
            xaxis: {
                tickDecimals: 0,
                axisLabel: "Эпохи"
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

});
</script>
