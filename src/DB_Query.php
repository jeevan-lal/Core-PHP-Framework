<?php

namespace Ctechhindi\CorePhpFramework;

use CTH\Config\Database;

/**
 * Database Query Generate
 */

class DB_Query
{
    /**
     * Database Class
     */
    private $db;

    /**
     * Database Query
     */
    public $query;

    /**
     * Database Query Limit
     */
    private $queryLimit;

    public function __construct()
    {
        $this->db = new Database();
        $this->db->connect();
    }

    /**
     * Database: Select (*)
     */
    public function select($columns = "*")
    {
        $this->query = "SELECT " . $columns;
        return $this;
    }

    /**
     * Database: Table
     */
    public function table($tableName)
    {
        $this->query .= " FROM " . $tableName;
        return $this;
    }

    /**
     * Database: Join
     */
    public function join($tableName, $condition, $joinType = "")
    {
        // Check Types of JOINs
        if (strtoupper($joinType) === "INNER") {
            $joinType = "INNER";
        } else if (strtoupper($joinType) === "LEFT") {
            $joinType = "LEFT";
        } else if (strtoupper($joinType) === "RIGHT") {
            $joinType = "RIGHT";
        } else if (strtoupper($joinType) === "FULL") {
            $joinType = "FULL";
        } else {
            $joinType = "";
        }

        $this->query .= "\n " . $joinType . " JOIN " . $tableName . " ON " . $condition;
        return $this;
    }

    /**
     * Get Database Query
     */
    public function get($limit = false, $offset = false)
    {
        /**
         * LIMIT and OFFSET
         * ---------------------------------------------------
         * "SELECT * FROM Orders LIMIT 10 OFFSET 15"
         * "SELECT * FROM Orders LIMIT 15, 10"
         * ---------------------------------------------------
         */

        if ($limit !== false) {
            $this->query .= "\n LIMIT " . $limit;

            // Set Query Limit
            $this->queryLimit = $limit;
        }

        if ($offset !== false) {
            $this->query .= " OFFSET " . $offset;
        }

        return $this->query;
    }

    // TODO: For Generate Custom Table Columns Names
    private function fetchTableColumns() {

        /**
            -----------------------------------------------------------------------------------------------------------
            QUERY::

            SELECT CONCAT(table_name, ".", column_name, " AS ", table_name, ".", column_name) field_names
            FROM information_schema.columns
            WHERE table_schema = "nominalroll" AND table_name = "file_index"
            -----------------------------------------------------------------------------------------------------------
            OUTPUT::
            
            file_index.id AS "file_index.id"
            file_index.english_name AS "file_index.english_name"
            file_index.created_at AS "file_index.created_at"
         */
    }

    /**
     * Run MySQL Query
     */
    public function run()
    {
        try {
            $query = $this->db->conn->prepare($this->query);
            if ($query->execute()) {
                if ($this->queryLimit == "1") {
                    $output = $query->fetch(\PDO::FETCH_ASSOC);
                    return $output;
                } else {
                    $output = $query->fetchAll(\PDO::FETCH_ASSOC);
                    return $output;
                }
            } else {
                return false;
            }
        } catch (\Throwable $th) {
            return false;
            // return $th->getMessage();
        }
    }
}
