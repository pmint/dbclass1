<?php

class Db
{
    public $mysql;
    public $connection_info;
    public $table = 'table1';

    function __construct()
    {
        $this->connect();
    }

    function __destruct()
    {
        $this->mysql->close();
    }

    public function connect(){
        $this->connection_info = [
            'mysql_user' => getenv('mysql_user'),
            'mysql_password' => getenv('mysql_password'),
            'mysql_host' => getenv('mysql_host'),
            'mysql_dbname' => getenv('mysql_dbname'),
        ];
        assert($this->connection_info);
    
        $this->mysql = mysqli_connect($this->connection_info['mysql_host'], $this->connection_info['mysql_user'], $this->connection_info['mysql_password'], $this->connection_info['mysql_dbname']);
        assert($this->mysql && ! $this->mysql->connect_errno);
        $this->mysql->set_charset('utf8');

        return $this->mysql;
    }

    public function store($foreign_key, $array = [])
    {
        $sqls = [];
        {
            foreach ($array as $column => $value){
                $value_serialized = serialize($value);
                $sqls[] = "INSERT INTO "
                    . " `" . $this->mysql->escape_string($this->table) . "` "
                    . " (`foreign_key`, `column_name`, `value`) "
                    . " VALUES "
                    . " ("
                    . " '" . $this->mysql->escape_string($foreign_key) . "' "
                    . ", "
                    . " '" . $this->mysql->escape_string($column) . "' "
                    . ", "
                    . " '" . $this->mysql->escape_string($value_serialized) . "' "
                    . ") "
                    . " ON DUPLICATE KEY UPDATE "
                    . " `foreign_key` = '" . $this->mysql->escape_string($foreign_key) . "' "
                    . ", "
                    . " `column_name` = '" . $this->mysql->escape_string($column) . "' "
                    . ", "
                    . " `value` = '" . $this->mysql->escape_string($value_serialized) . "' "
                    . "";
            }
        }
        // var_dump($sqls);
    
        if ($sqls){
            $result = $this->mysql->multi_query(implode("; ", $sqls));
            assert($result, $this->mysql->error);
    
            $results = [];
            do {
                $results[] = $result = $this->mysql->store_result();
                if ($result){
                    $result->free();
                }
            } while ($this->mysql->more_results() && $this->mysql->next_result());
            assert(count($results) == count($sqls));
            // var_dump($results);
        }
    }
    
    public function delete($foreign_key, $columns = [])
    {
        $sqls = [];
        {
            foreach ($columns as $column){
                $sqls[] = "DELETE FROM "
                    . " `" . $this->mysql->escape_string($this->table) . "` "
                    . " WHERE "
                    . " `foreign_key` = '" . $this->mysql->escape_string($foreign_key) . "' "
                    . " AND "
                    . " `column_name` = '" . $this->mysql->escape_string($column) . "' "
                    . "";
            }
        }
        // var_dump($sqls);
    
        if ($sqls){
            $result = $this->mysql->multi_query(implode("; ", $sqls));
            assert($result, $this->mysql->error);
    
            $results = [];
            do {
                $results[] = $result = $this->mysql->store_result();
                if ($result){
                    $result->free();
                }
            } while ($this->mysql->more_results() && $this->mysql->next_result());
            assert(count($results) == count($sqls));
            // var_dump($results);
        }
    }
    
    public function fetch($foreign_key)
    {
        $sql = "SELECT * FROM `" . $this->mysql->escape_string($this->table) . "` WHERE `foreign_key` = '" . $this->mysql->escape_string($foreign_key) . "'";
        $result = $this->mysql->query($sql);
    
        assert($result, $this->mysql->error);
    
        // var_dump(['$result->num_rows' => $result->num_rows]);
        $rows = [];
        while ($row = $result->fetch_array(MYSQLI_ASSOC)){
            $rows[$row['column_name']] = unserialize($row['value']);
        }
        $result->free();
    
        // var_dump($rows);
        return $rows;
    }
}
