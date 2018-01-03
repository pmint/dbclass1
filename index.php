<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
</head>
<body>
<pre>
<?php
// https://community.c9.io/t/setting-up-phpmyadmin/1723

date_default_timezone_set('Asia/Tokyo');
// mb_language('ja');
// mb_internal_encoding('utf-8');

connect();
function connect(){
    global $mysql;
    global $connection_info;

    $connection_info = [
        mysql_user => getenv('mysql_user'),
        mysql_password => getenv('mysql_password'),
        mysql_host => getenv('mysql_host'),
        mysql_dbname => getenv('mysql_dbname'),
    ];
    assert($connection_info);
    
    $mysql = mysqli_connect($connection_info['mysql_host'], $connection_info['mysql_user'], $connection_info['mysql_password'], $connection_info['mysql_dbname']);
    assert($mysql && ! $mysql->connect_errno);
    $mysql->set_charset('utf8');
    return $mysql;
}

store();
function store()
{
    global $mysql;
    echo "# delete, insert, update\n";

    $d = new DateTime();
    $ext_columns = ['column_any' => [$d->format('H:i:s'), 'TAG_A', 'TAG_B'], 'column_datetime' => $d->format('Y-m-d H:i:s')];
    $ext_columns['tags'] = null;

    $sqls = [];
    {
        $table = 'table1';
        // $foreign_key = mt_rand('100', '999999');
        $foreign_key = '19859';

        $columns = [];
        $values = [];
        $types = [];
        $delete_columns = [];

        foreach ($ext_columns as $k => $v){
            if (is_null($v)){
                $delete_columns[] = $k;
            }
            else if (is_string($v)){
                $columns[] = $k;
                $values[] = $v;
                $types[] = 'text/plain';
            }
            else {
                $columns[] = $k;
                $values[] = serialize($v);
                $types[] = 'text/plain';
            }
        }

        foreach ($delete_columns as $i => $column){
            $sqls[] = "DELETE FROM "
                . " `" . $mysql->escape_string($table) . "` "
                . " WHERE "
                . " `foreign_key` = '" . $mysql->escape_string($foreign_key) . "' "
                . " AND "
                . " `column` = '" . $mysql->escape_string($delete_columns[$i]) . "' "
                . "";
        }

        foreach ($columns as $i => $column){
            $sqls[] = "INSERT INTO "
                . " `" . $mysql->escape_string($table) . "` "
                . " (`foreign_key`, `column`, `type`, `value`) "
                . " VALUES "
                . " ("
                . " '" . $mysql->escape_string($foreign_key) . "' "
                . ", "
                . " '" . $mysql->escape_string($columns[$i]) . "' "
                . ", "
                . " '" . $mysql->escape_string($types[$i]) . "' "
                . ", "
                . " '" . $mysql->escape_string($values[$i]) . "' "
                . ") "
                . " ON DUPLICATE KEY UPDATE "
                . " `foreign_key` = '" . $mysql->escape_string($foreign_key) . "' "
                . ", "
                . " `column` = '" . $mysql->escape_string($columns[$i]) . "' "
                . ", "
                . " `type` = '" . "text/plain" . "' "
                . ", "
                . " `value` = '" . $mysql->escape_string($values[$i]) . "' "
                . "";
        }
    }
    var_dump($sqls);

    {
        $result = $mysql->multi_query(implode("; ", $sqls));
        assert($result);
        var_dump($mysql->error);
        
        do {
            $result = $mysql->store_result();
            if ($result){
                var_dump($result);
    
                $result->free();
            }
        } while ($mysql->next_result());
    }
}

echo "\n----------\n";
fetch();
function fetch()
{
    global $mysql;
    echo "# select\n";

    $foreign_key = '19859';
    $result = $mysql->query("select * from `table1` where `foreign_key` = '" . $mysql->escape_string($foreign_key) . "'");
    assert($result);
    // var_dump($mysql->error);
    
    var_dump($result->num_rows);
    $rows = [];
    while ($row = $result->fetch_array(MYSQLI_ASSOC)){
        $rows[$row['foreign_key']][$row['column']] = $row;
    }
    $result->free();
    
    echo "{$rows[$foreign_key]['column_datetime']['value']} ({$rows[$foreign_key]['column_datetime']['type']})\n\n";
    var_dump($rows);
}

$mysql->close();
?>
</pre>
</body>
</html>
