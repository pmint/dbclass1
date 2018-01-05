<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
</head>
<body>
<pre>
<?php
date_default_timezone_set('Asia/Tokyo');
// mb_language('ja');
// mb_internal_encoding('utf-8');

ini_set('error_log', 'log/error.log');
ini_set('log_errors', true);
ini_set('display_errors', true);
ini_set('error_reporting', E_ALL);

// https://community.c9.io/t/setting-up-phpmyadmin/1723

require_once 'db.class.php';

$db = new Db();

// $table = 'table1';
// $foreign_key = mt_rand('100', '999999');
$foreign_key = '19859';

echo "# insert, update\n";

$d = new DateTime();
$ext_array = ['column_any' => [$d->format('H:i:s'), 'TAG_A', 'TAG_B'], 'column_datetime' => $d->format('Y-m-d H:i:s')];

$db->store($foreign_key, $ext_array);

echo "\n----------\n";
echo "# delete\n";

$db->delete($foreign_key, []);
$db->delete($foreign_key, array_keys($ext_array));

echo "\n----------\n";
echo "# select\n";
$db->fetch($foreign_key);

?>
</pre>
</body>
</html>
