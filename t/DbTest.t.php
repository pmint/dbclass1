<?php
use PHPUnit\Framework\TestCase;

require_once 'db.class.php';

class DbClassTest extends TestCase
{
    private $db;
    private $foreign_key;

    protected function setUp()
    {
        $this->db = new Db();
        $this->assertTrue($this->db instanceof Db);
        
        $this->foreign_key = 9999999;
    }

    private function clear()
    {
        $array = $this->db->fetch($this->foreign_key);
        $this->db->delete($this->foreign_key, array_keys($array));
    }

    public function testConstruct1()
    {
        $this->db = new Db();
        $this->assertTrue($this->db instanceof Db);
    }

    public function testConnect1()
    {
        $mysql = $this->db->connect();
        $this->assertTrue($mysql instanceof mysqli);
    }

    public function testFetch1()
    {
        # setup
        $this->clear();

        # test, verify
        $actual = $this->db->fetch($this->foreign_key);
        $except = [];
        $this->assertEquals($actual, $except, var_export($actual, true));
    }

    public function testStore1()
    {
        # setup
        $this->clear();

        # test
        $this->db->store($this->foreign_key, []);

        # verify
        $actual = $this->db->fetch($this->foreign_key);
        $except = [];
        $this->assertEquals($actual, $except);
    }

    public function testStore2()
    {
        # setup
        $this->clear();

        # test
        $in = ['store4238' => ['STORE4238']];
        $this->db->store($this->foreign_key, $in);

        # verify
        $actual = $this->db->fetch($this->foreign_key);
        $except = $in;
        $this->assertEquals($actual, $except);
    }

    public function testDelete1()
    {
        $this->db->delete($this->foreign_key, []);
    }

    public function testDelete2()
    {
        # setup
        $this->clear();
        
        $in = ['delete920' => ['DELETE920']];
        $this->db->store($this->foreign_key, $in);

        $actual = $this->db->fetch($this->foreign_key);
        $except = $in;
        $this->assertEquals($actual, $except);

        # test
        $in2 = array_keys($in);
        $this->db->delete($this->foreign_key, $in2);

        # verify
        $actual = $this->db->fetch($this->foreign_key);
        $except = [];
        $this->assertEquals($actual, $except);
    }

}
