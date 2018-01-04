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

ini_set('error_log', 'error.log');
ini_set('log_errors', true);
ini_set('display_errors', true);
ini_set('error_reporting', E_ALL);

// https://community.c9.io/t/setting-up-phpmyadmin/1723

connect();
function connect(){
    global $mysql;
    global $connection_info;

    $connection_info = [
        'mysql_user' => getenv('mysql_user'),
        'mysql_password' => getenv('mysql_password'),
        'mysql_host' => getenv('mysql_host'),
        'mysql_dbname' => getenv('mysql_dbname'),
    ];
    assert($connection_info);

    $mysql = mysqli_connect($connection_info['mysql_host'], $connection_info['mysql_user'], $connection_info['mysql_password'], $connection_info['mysql_dbname']);
    assert($mysql && ! $mysql->connect_errno);
    $mysql->set_charset('utf8');
    return $mysql;
}

echo "# insert, update\n";

$d = new DateTime();
$ext_array = ['column_any' => [$d->format('H:i:s'), 'TAG_A', 'TAG_B'], 'column_datetime' => $d->format('Y-m-d H:i:s')];

$table = 'table1';
// $foreign_key = mt_rand('100', '999999');
$foreign_key = '19859';

store($ext_array);
function store($array = [])
{
    global $mysql;
    global $table, $foreign_key;
    
    $sqls = [];
    {
        foreach ($array as $column => $value){
            $value_serialized = serialize($value);
            $sqls[] = "INSERT INTO "
                . " `" . $mysql->escape_string($table) . "` "
                . " (`foreign_key`, `column_name`, `value`) "
                . " VALUES "
                . " ("
                . " '" . $mysql->escape_string($foreign_key) . "' "
                . ", "
                . " '" . $mysql->escape_string($column) . "' "
                . ", "
                . " '" . $mysql->escape_string($value_serialized) . "' "
                . ") "
                . " ON DUPLICATE KEY UPDATE "
                . " `foreign_key` = '" . $mysql->escape_string($foreign_key) . "' "
                . ", "
                . " `column_name` = '" . $mysql->escape_string($column) . "' "
                . ", "
                . " `value` = '" . $mysql->escape_string($value_serialized) . "' "
                . "";
        }
    }
    // var_dump($sqls);

    {
        $result = $mysql->multi_query(implode("; ", $sqls));
        assert($result, $mysql->error);

        $results = [];
        do {
            $results[] = $result = $mysql->store_result();
            if ($result){
                $result->free();
            }
        } while ($mysql->more_results() && $mysql->next_result());
        assert(count($results) == count($sqls));
        // var_dump($results);
    }
}

echo "\n----------\n";
echo "# delete\n";

delete(array_keys($ext_array));
function delete($columns = [])
{
    global $mysql;
    global $table, $foreign_key;
    
    $sqls = [];
    {
        foreach ($columns as $column){
            $sqls[] = "DELETE FROM "
                . " `" . $mysql->escape_string($table) . "` "
                . " WHERE "
                . " `foreign_key` = '" . $mysql->escape_string($foreign_key) . "' "
                . " AND "
                . " `column_name` = '" . $mysql->escape_string($column) . "' "
                . "";
        }
    }
    // var_dump($sqls);

    {
        $result = $mysql->multi_query(implode("; ", $sqls));
        assert($result, $mysql->error);

        $results = [];
        do {
            $results[] = $result = $mysql->store_result();
            if ($result){
                $result->free();
            }
        } while ($mysql->more_results() && $mysql->next_result());
        assert(count($results) == count($sqls));
        // var_dump($results);
    }
}

echo "\n----------\n";
echo "# select\n";
fetch();
function fetch()
{
    global $mysql;
    global $table, $foreign_key;

    $sql = "SELECT * FROM `" . $mysql->escape_string($table) . "` WHERE `foreign_key` = '" . $mysql->escape_string($foreign_key) . "'";
    $result = $mysql->query($sql);

    assert($result, $mysql->error);

    // var_dump(['$result->num_rows' => $result->num_rows]);
    $rows = [];
    while ($row = $result->fetch_array(MYSQLI_ASSOC)){
        $rows[$row['foreign_key']][$row['column_name']] = unserialize($row['value']);
    }
    $result->free();

    // var_dump($rows);
}

$mysql->close();
?>
</pre>
</body>
</html>
