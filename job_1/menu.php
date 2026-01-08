<?php

header('Content-Type: text/html; utf-8; charset=UTF-8');

$count_create = 30;

$menu = [

    'БАЗА' => [
        ['url' => 'index.php', 'title' => 'Index.php'],
        ['url' => 'ns_stats.php', 'title' => 'Графики обучения'],
    ],
	
    'СЕТКА ОТРИСОВКА' => [
        ['url' => 'kvs_paint.php', 'title' => 'Квадраты: обучение'],
        ['url' => 'rects_paint.php', 'title' => 'Прямоугольники: обучение'],
        ['url' => 'kvs_paint.php?dir=kvs_test', 'title' => 'Квадраты: проверка'],
        ['url' => 'rects_paint.php?dir=rects_test', 'title' => 'Прямоугольники: проверка'],
    ],

    'КОНТУР ОТРИСОВКА' => [
        ['url' => 'kvs_paint_points.php', 'title' => 'Квадраты: обучение'],
        ['url' => 'rects_paint_points.php', 'title' => 'Прямоугольники: обучение'],
        ['url' => 'kvs_paint_points.php?dir=kvs_test', 'title' => 'Квадраты: проверка'],
        ['url' => 'rects_paint_points.php?dir=rects_test', 'title' => 'Прямоугольники: проверка'],
    ],

    'ОБУЧЕНИЕ' => [
        ['url' => 'ns_mas_.php', 'title' => 'По сетке', 'admin' => 1],
        ['url' => 'ns_points.php', 'title' => 'По контуру', 'admin' => 1],
    ],

    'СОЗДАНИЕ ДАТАСЕТА' => [
        ['url' => 'kvs_list_create.php?dir=&count=' . $count_create, 'title' => $count_create . ' квадратов (обучение)', 'admin' => 1],
        ['url' => 'rects_list_create.php?dir=&count=' . $count_create, 'title' => $count_create . ' прямоугольников (обучение)', 'admin' => 1],
        ['url' => 'kvs_list_create.php?dir=kvs_test&count=' . $count_create, 'title' => $count_create . ' квадратов (проверка)', 'admin' => 1],
        ['url' => 'rects_list_create.php?dir=rects_test&count=' . $count_create, 'title' => $count_create . ' прямоугольников (проверка)', 'admin' => 1],
    ],
];

$current = basename($_SERVER['PHP_SELF']);
if (!empty($_SERVER['QUERY_STRING'])) {
    $current .= '?' . $_SERVER['QUERY_STRING'];
}


$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST']; // neurotopic.ru
$path_   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); // /scheme/job_1
$path    = $scheme . '://' . $host . $path_ . '/';

// $path = 'https://neurotopic.ru/scheme/job_1/';
$activeSection = '';

echo '<div class="menu-wrap" align="center">';

$admin_key = 'uyulvtvo86o8_';

foreach ($menu as $sectionTitle => $items) {

    echo '<div class="menu-block">';
    echo '<div class="menu-title">' . htmlspecialchars($sectionTitle) . '</div>';
    echo '<ul class="menu" align="left">';

    foreach ($items as $item) {

        $isActive = ($item['url'] === $current);

        if ($isActive) {
            $activeSection = $item['title'];
        }

        echo '<li>';

        if ($isActive) {
            echo '<span class="active"><b>' . htmlspecialchars($item['title']) . '</b></span>';
        } else {
			
			if($item['admin'] == 1)
			{
				if($admin_key == 'uyulvtvo86o8')
				{
					echo '<a href="' . $path . $item['url'] . '">'
						. htmlspecialchars($item['title'])
						. '</a>';						
				}
				else
					echo '<span class="no_active">' . htmlspecialchars($item['title']) . '</span>';
			}
			else
			{
				echo '<a href="' . $path . $item['url'] . '">'
					. htmlspecialchars($item['title'])
					. '</a>';	
			}				

        }

        echo '</li>';
    }

    echo '</ul>';
    echo '</div>';
}

echo '<div style="clear:both"></div>';
echo '</div>';

if ($activeSection != '') {
    echo '<div class="title" align="center"><b>' . htmlspecialchars($activeSection) . '</b></div>';
}

?>

<style>
.title{
	background-color:#4aabab;
	padding:10px;
	color:#fff;
	text-transform:uppercase;
}

.menu-wrap {
    width:100%;
    text-align:center;
	background-color:#c2eded;
	
}

.menu-block {
    display:inline-block;
    vertical-align:top;
    width:260px;
    margin:10px;
    border:1px solid #c0c0c0;
    padding:10px;
    border-radius:10px;
	background-color:#fff;
}

.menu-title {
    font-weight:bold;
    margin-bottom:6px;
}
.menu li {
    list-style:none;
    margin:4px 0;
}
.menu a {
    text-decoration:none;
}
.active {
    text-decoration:underline;
}
.no_active{color:#c0c0c0}
</style>

<script src="<?php echo $site_progect; ?>/js/jquery-1.11.3.js"></script>
<script type="text/javascript" src="<?php echo $site_progect; ?>/js/jquery.flot.min.js"></script> 
<script type="text/javascript" src="<?php echo $site_progect; ?>/js/jquery.flot.time.js"></script>

