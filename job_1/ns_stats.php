<?php

include_once(__DIR__ . '/menu.php');

$resultsDir = __DIR__ . '/results';
$files = glob($resultsDir . '/*_model_*.json');

$modelNumbers = array();

foreach ($files as $file) {
    if (preg_match('/_model_(\d+)\.json$/', $file, $m)) {
        $modelNumbers[] = (int)$m[1];
    }
}

$modelNumbers = array_values(array_unique($modelNumbers));
sort($modelNumbers);

$currentModel = isset($_GET['model'])
    ? (int)$_GET['model']
    : (count($modelNumbers) ? $modelNumbers[0] : 0);

?>
<div style="display:flex; max-width:1200px; margin:0 auto;">

    <div style="width:200px; margin-right:20px;">
        <div style="font-weight:bold; margin-bottom:10px;">Выберите модель</div>

        <?php foreach ($modelNumbers as $num): ?>
            <div style="margin-bottom:5px;">
                <a href="?model=<?php echo $num; ?>"
                   <?php if ($num == $currentModel) echo 'style="font-weight:bold; color:red"'; ?>>
                    Модель <?php echo $num; ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <div style="flex:1;">

        <div style="text-align:center; margin:20px 0; font-size:18px; font-weight:bold;">
            График по эпохам
        </div>
        <div style="width:100%; max-width:800px; margin:0 auto;">
            <?php include_once(__DIR__ . '/ns_stat.php'); ?>
            <div style="text-align:center; margin-top:5px; font-size:13px;">
                X — Эпохи, Y — Ошибка
            </div>
        </div>

        <div style="text-align:center; margin:40px 0 20px 0; font-size:18px; font-weight:bold;">
            График по времени
        </div>
        <div style="width:100%; max-width:800px; margin:0 auto;">
            <?php include_once(__DIR__ . '/ns_stat_time.php'); ?>
            <div style="text-align:center; margin-top:5px; font-size:13px;">
                X — Время (сек), Y — Ошибка
            </div>
        </div>

        <div style="text-align:center; margin-top:30px;">
            такие дела
        </div>

    </div>
</div>
