<?php

// neurotopic.ru/scheme/job_1/kv_create.php

$count = 1;

if($_GET['count'] > 1)
	$count = $_GET['count'];

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'];
$path   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$url    = $scheme . '://' . $host . $path . '/kv_create.php?dir=' . $_GET['dir'];

for($k = 0; $k < $count; $k++)
{
	echo $k . ')' . file_get_contents($url) . '<br>';
	
	if($count > 1)
		sleep(1);
}

