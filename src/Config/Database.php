<?php namespace Ctechhindi\CorePhpFramework\Config;

use \Exception as Exception;
use \PDO;

class Database
{
    /**
     * Database Type
     */
    public $DBType;

    /**
     * Database
     */
    public $conn;


    public function __construct() {

        // Check Database Type
        if ($this->databaseType === "production") {
            $this->DBType = $this->production;
        } else {
            $this->DBType = $this->development;
        }
    }

    /**
     * Connect to Database
     * 
     * https://www.php.net/manual/en/pdo.connections.php
     */
    public function connect() {
        
        try {

            $hostname = $this->DBType['hostname'];
            $database = $this->DBType['database'];

            $this->conn = new PDO("mysql:host=$hostname;dbname=$database", $this->DBType['username'], $this->DBType['password']);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // $this->conn->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES utf8");
            $this->conn->exec("set names ". $this->DBType['charset']);

        } catch(\PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Closing Connection
     * --------------------------
     * $conn->close();
     * mysqli_close($conn);
     * $conn = null;
     */
    public function close() {
        return $this->conn = null;
    }
}