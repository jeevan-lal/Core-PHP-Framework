<?php

namespace Ctechhindi\CorePhpFramework;

use CTH\Config\Database;

/**
 * Application Base Model
 */

class Model
{
    /**
     * Database Class
     */
    public $db;

    public function __construct()
    {
        $this->db = new Database();
        $this->db->connect();
    }

    /**
     * Insert Data in the Table
     * 
     * @var array
     * 
     * [
     *   "table" => string,
     *   "insert" => array,
     *   "unique_column" => array,
     * ]   
     */
    public function insert(array $data)
    {

        // Check Data
        if (empty($data["table"])) {
            return false;
        }
        if (empty($data["insert"])) {
            return false;
        }

        // Check is Unique Columns
        if (!empty($data["unique_column"]) && is_array($data["unique_column"])) {
            $whereString = "";
            $whereArray = [];
            foreach ($data["unique_column"] as $key => $value) {
                if (isset($data["insert"][$value]) && !empty($data["insert"][$value])) {
                    $whereString .= "lower(" . $value . ") = :" . $value;
                    $whereArray[$value] = strtolower($data["insert"][$value]);
                    if ($key !== count($data["unique_column"]) - 1) {
                        $whereString .= " AND ";
                    }
                }
            }

            $query = "SELECT * FROM `" . $data['table'] . "` WHERE " . $whereString;
            $sql = $this->db->conn->prepare($query);
            $sql->execute($whereArray);
            if (!empty($sql->fetchAll())) {
                return false;
            }
        }

        // Push `created_at` and `updated_at` columns
        $data["insert"] = array_merge(["created_at" => time(), "updated_at" => time()], $data["insert"]);

        // Generate MySQL Query
        $field_name = "(";
        $field_value = "(";
        foreach (array_keys($data["insert"]) as $field) {
            $field_name .= "`" . $field . "`, ";
            $field_value .= ":" . $field . ", ";
        }
        $field_name = substr($field_name, 0, -2) . ")"; // remove last (,) and join last bracket
        $field_value = substr($field_value, 0, -2) . ")"; // remove last (,) and join last bracket

        // MySQL Insert Query
        $query = "INSERT INTO `" . $data['table'] . "` $field_name VALUES $field_value";
        $sql = $this->db->conn->prepare($query);
        if ($sql->execute($data["insert"])) {

            // Last Insert Id
            $last_id = $this->db->conn->lastInsertId();

            // Close
            // $this->db->close();

            return $last_id;
        } else {

            // Close
            // $this->db->close();

            return false;
        }
    }

    /**
     * Update Data in the Table (Only One Where Condition)
     * 
     * [
     *   "table" => String,
     *   "id" => Number,
     *   "update" => Array,
     *  ]
     */
    public function update(array $data)
    {
        // Check Data
        if (empty($data["table"])) {
            return false;
        }
        if (empty($data["id"])) {
            return false;
        }
        if (empty($data["update"])) {
            return false;
        }

        // Push `updated_at` columns
        $data["update"] = array_merge(["updated_at" => time()], $data["update"]);

        $update_str = "";
        foreach (array_keys($data["update"]) as $field) {
            $update_str .= "`" . $field . '`= :' . $field . ', ';
        }
        $update_str = substr($update_str, 0, -2); // remove last (,)

        // Query
        $sql = "UPDATE `{$data['table']}` SET ${update_str} WHERE `id`=${data['id']}";
        $sql = $this->db->conn->prepare($sql);
        if ($sql->execute($data["update"])) {

            // Close
            // $this->db->close();

            return true;
        } else {

            // Close
            // $this->db->close();

            return false;
        }
    }


    /**
     * Update Data in the Table (Only One Where Condition)
     * 
     * [
            "table" => String,
            "where" => [
                ["manifest_id", $found["id"], "="],
                ["version", $found["version"], "="],
            ],
            "update" => [
                "json_data" => $request->getPost("json")
            ]
        ]
     */
    public function updateWithWhere(array $options)
    {
        // Check Data
        if (empty($options["table"])) {
            return false;
        }
        if (empty($options["where"])) {
            return false;
        }
        if (empty($options["update"])) {
            return false;
        }

        // Push `updated_at` columns
        $options["update"] = array_merge(["updated_at" => time()], $options["update"]);

        // Data is Found then Update Data
        $sql = $this->generateUpdateSQLQuery($options["table"], $options["update"], $options["where"]);
        $sql = $this->db->conn->prepare($sql);
        if ($sql->execute($options["update"])) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Fetch Data in the Table
     * 
     * @var array
     * [
     *      "table" => string,
     *      "select_column" => "id, english_name",
     *      "remove_column" => ["id", "english_name"],
     *      "where" => [
     *          ["id", $_GET["id"], "="],
     *      ],
     *      "order_by" => [
     *          ["id", "DESC"], // ASC
     *      ],
     *      "limit" => 1,
     *      "output" => "", ['columns', 'numbers', 'query']
     *  ]
     */
    public function fetch(array $options = [])
    {
        // [VALIDATION]
        if (!isset($options["table"]) || !is_string($options["table"]) || empty($options["table"])) {
            throw new \Exception("Required :: Database Table Name");
        }

        // Select
        $queryString = "SELECT ";

        /**
         * Select Column name
         * ---------------------------------------
         *   "select_column" => "id, english_name"
         */
        if (isset($options["select_column"]) && is_string($options["select_column"]) && !empty($options["select_column"])) {
            $queryString .= trim($options["select_column"]) . " FROM ";
        } else {

            /**
             * Remove Column name
             * ---------------------------------------
                "remove_column" => ["english_name", "..."]
             */
            if (isset($options["remove_column"]) && is_array($options["remove_column"]) && !empty($options["remove_column"])) {

                // First of all we have to fetch all the columns of the table and after that we have to delate the Remove Column from these columns.
                $q = $this->db->conn->prepare("DESCRIBE `" . trim($options['table']) . "`");
                if (!$q->execute()) {
                    throw new \Exception("Database table column name data not received.");
                }

                // Fetch Column Name Data
                $table_fields = $q->fetchAll(\PDO::FETCH_COLUMN);

                // Remove Column Name
                $selectionColumn = array_diff($table_fields, $options["remove_column"]);
                if (empty($selectionColumn)) {
                    throw new \Exception("Database table column name not found.");
                }

                // Select Column Name and Remove Column Name
                $queryString .= trim(implode(", ", $selectionColumn)) . " FROM ";
            } else {
                $queryString .= " * FROM ";
            }
        }

        /**
         * Table Name
         * --------------------------------------
         *  "table" => "table_name"
         */
        $queryString .= "`" . trim($options['table']) . "` ";

        /**
         * where
         * ---------------------------------------
         *   "where" => [
         *       ["id", $_GET["id"], "="],
         *       ["email", "jeevan", "=", "AND"],
         *   ],
         */
        if (isset($options["where"]) && is_array($options["where"]) && !empty($options["where"])) {
            $queryString .= "WHERE ";
            foreach ($options["where"] as $key => $value) {
                if (!empty($value[0]) && isset($value[1])) {
                    // where operators (=, <, >, LIKE,..)
                    $where_operators = "=";
                    if (!empty($value[2])) {
                        $where_operators = $value[2];
                    }

                    // next where condition (OR, AND)
                    if ($key >= 1) {
                        $next_where_condition = "AND";
                        if (!empty($value[3])) {
                            $next_where_condition = $value[3];
                        }

                        $queryString .= $next_where_condition . " ";
                    }

                    $queryString .= "`{$value[0]}` {$where_operators} '{$value[1]}' ";
                }
            }
        }

        /**
         * order_by
         * ---------------------------------------
         *  "order_by" => [
         *      ["id", "DESC"],
         *      ["id2", "ASC"],
         *  ],
         */
        if (isset($options["order_by"]) && is_array($options["order_by"])) {
            $queryString .= "ORDER BY ";
            foreach ($options["order_by"] as $key => $value) {
                if (!empty($value[0]) && !empty($value[1])) {
                    // next order by
                    if ($key >= 1) {
                        $queryString .= ", ";
                    }

                    $queryString .= "`{$value[0]}` {$value[1]} ";
                }
            }
        }

        /**
         * limit
         * ---------------------------------------
         *  "limit" => 1,
         */
        if (isset($options["limit"])) {
            $queryString .= "LIMIT " . $options["limit"] . " ";
        }

        /**
         * Output Type (String)
         * ---------------------------------------
         *  "output" => "", ['columns', 'numbers', 'query']
         */
        $outputType = NULL;
        if (isset($options["output"])) {
            $outputType = $this->recordFetchType($options["output"]);
        }

        // View Query
        if ($outputType === 1) {
            print_r($queryString);
            die();
        }

        // Query
        $sql = $this->db->conn->prepare($queryString);
        if ($sql->execute()) {

            // For Class
            if (!empty($this->returnType)) {
                $sql->setFetchMode(\PDO::FETCH_CLASS, $this->returnType, [$options]);
            } else {
                if (!empty($outputType)) $sql->setFetchMode($outputType);
            }

            if (isset($options["limit"]) && $options["limit"] == "1") {
                $out = $sql->fetch();
            } else {
                $out = $sql->fetchAll();
            }

            return $out;
        } else {

            return false;
        }
    }

    /**
     * Delete Data in the Table
     * 
     * @var array
     * 
     * [
     *      "table" => string,
     *      "where" => [
     *          ["id", $_GET["id"], "="],
     *      ],
     * ]   
     */
    public function delete(array $options)
    {

        if (empty($options["table"])) {
            return false;
        }
        if (empty($options["where"]) || !is_array($options["where"])) {
            return false;
        }

        /**
         * where
         * ---------------------------------------
            "where" => [
                ["id", $_GET["id"], "="],
                ["email", "jeevan", "=", "AND"],
            ],
         */
        $queryString = "";
        foreach ($options["where"] as $key => $value) {
            if (!empty($value[0]) && isset($value[1])) {
                // where operators (=, <, >, LIKE,..)
                $where_operators = "=";
                if (!empty($value[2])) {
                    $where_operators = $value[2];
                }

                // next where condition (OR, AND)
                if ($key >= 1) {
                    $next_where_condition = "AND";
                    if (!empty($value[3])) {
                        $next_where_condition = $value[3];
                    }

                    $queryString .= $next_where_condition . " ";
                }

                $queryString .= "`{$value[0]}` {$where_operators} '{$value[1]}' ";
            }
        }

        // MySQL Delete Query
        $query = "DELETE FROM `" . $options['table'] . "` WHERE " . $queryString;
        $sql = $this->db->conn->prepare($query);
        if ($sql->execute()) {

            // Close
            // $this->db->close();

            return true;
        } else {

            // Close
            // $this->db->close();

            return false;
        }
    }

    /**
     * Insert and Update. If Record Exists in the Table then Record Update if not the Record Insert.
     * 
     * @var array
     * 
     * [
            "table" => string,
            "insert" => array,
            "where" => [
                ["id", $_GET["id"], "="],
                ["email", "jeevan", "=", "AND"],
            ],
            "unique_column" => array,
       ]
     */
    public function put(array $options)
    {

        if (empty($options["table"])) {
            return false;
        }
        if (empty($options["insert"]) || !is_array($options["insert"])) {
            return false;
        }
        if (empty($options["where"]) || !is_array($options["where"])) {
            return false;
        }

        // Check is Unique Columns
        if (!empty($options["unique_column"]) && is_array($options["unique_column"])) {
            $whereString = "";
            $whereArray = [];
            foreach ($options["unique_column"] as $key => $value) {
                if (isset($options["insert"][$value]) && !empty($options["insert"][$value])) {
                    $whereString .= "lower(" . $value . ") = :" . $value;
                    $whereArray[$value] = strtolower($options["insert"][$value]);
                    if ($key !== count($options["unique_column"]) - 1) {
                        $whereString .= " AND ";
                    }
                }
            }

            $query = "SELECT * FROM `" . $options['table'] . "` WHERE " . $whereString;
            $sql = $this->db->conn->prepare($query);
            $sql->execute($whereArray);
            if (!empty($sql->fetchAll())) {
                return ["status" => false, "message" => "This data already exists."];
            }
        }

        /**
         * First Check Data in exists [Select and Where]
         */
        $queryString_WHERE = "SELECT * FROM `" . $options['table'] . "` ";; // Where Condition
        $queryString_WHERE .= $this->generateWhereSQLQuery($options["where"]);
        $sql = $this->db->conn->prepare($queryString_WHERE);
        $sql->execute();
        if (empty($sql->fetchAll())) {
            // Data Not Found then Insert Data
            $queryString_INSERT = $this->generateInsertSQLQuery($options["table"], $options["insert"]);
            if ($this->db->conn->prepare($queryString_INSERT)->execute(array_merge(["created_at" => time(), "updated_at" => time()], $options["insert"]))) {
                return ["status" => true, "lastInsertId" => $this->db->conn->lastInsertId()];
            }
        } else {
            // Data is Found then Update Data
            $queryString_UPDATE = $this->generateUpdateSQLQuery($options["table"], $options["insert"], $options["where"]);
            if ($this->db->conn->prepare($queryString_UPDATE)->execute(array_merge(["updated_at" => time()], $options["insert"]))) {
                return ["status" => true];
            }
        }
    }


    /**
     * Fetch Data in the Table With Join Query
     * 
     * @var array
     * [
     *      "table" => "wff_manifest_installed",
     *      "select_column" => [
     *         wff_manifest_installed.id,
     *              wff_manifest_installed.name
     *      ],
     *      "join" => [
     *          [
     *              "table" => "wff_manifest_data",
     *              "join_type" => "INNER",
     *              "where" => [
     *                  ["wff_manifest_installed.manifest_id", "wff_manifest_data.id", "="]
     *              ]
     *          ]
     *      ],
     *      "where" => [
     *          ["wff_manifest_installed`.`email", $request->getPost("username"), "="]
     *      ]
     *      "limit" => 1,
     *      "order_by" => [
     *          ["wff_manifest_data`.`id", "DESC"]
     *      ],
     *      "output" => "", ['columns', 'numbers', 'query']
     * ]
     */
    public function join(array $options = [])
    {
        // [VALIDATION]
        if (!isset($options["table"]) || !is_string($options["table"]) || empty($options["table"])) {
            throw new \Exception("Required :: Database Table Name");
        }

        // Select
        // $queryString = "SELECT * FROM `". $options['table'] ."` ";
        $queryString = "SELECT ";

        /**
         * Select Column name
         * ---------------------------------------
         *  "select_column" => "table.id, table.english_name"
         */
        if (isset($options["select_column"]) && is_array($options["select_column"]) && !empty($options["select_column"])) {
            $queryString .= trim(implode(",", $options["select_column"])) . " FROM ";
        } else {
            $queryString .= " * FROM ";
        }

        /**
         * Table Name
         * --------------------------------------
         *   "table" => "table_name"
         */
        $queryString .= "`" . trim($options['table']) . "` ";


        /**
         * join
         * ---------------------------------------
         * [
         *      "table" => "wff_manifest_data",
         *      "join_type" => "INNER", // INNER, LEFT, RIGHT, FULL OUTER
         *      "where" => [
         *          ["wff_manifest_installed.manifest_id", "wff_manifest_data.id", "="]
         *      ]
         * ]
         */
        if (isset($options["join"]) && is_array($options["join"]) && count($options["join"]) > 0) {
            for ($joinIndex = 0; $joinIndex < count($options["join"]); $joinIndex++) {

                $queryString .= "\n";

                $joinTB = $options["join"][$joinIndex];

                // [VALIDATION] Check Join Table Name
                if (!isset($joinTB["table"]) || empty($joinTB["table"])) {
                    continue;
                }

                // JOIN Query: INNER JOIN wff_manifest_data ON wff_manifest_installed.manifest_id=wff_manifest_data.id
                $joinQuery = "";

                // JOIN TYPE: [INNER, LEFT, RIGHT, FULL OUTER]
                if (!isset($joinTB["join_type"]) || empty($joinTB["join_type"])) {
                    $joinQuery .= "INNER JOIN";
                } else {
                    $joinQuery .= $joinTB["join_type"] . " JOIN ";
                }

                // JOIN TABLE
                $joinQuery .= $joinTB["table"] . " ON ";

                // JOIN TABLE WHERE
                $joinQuery .= $this->generateJoinOnSQLQuery($joinTB["where"]);

                $queryString .= $joinQuery;
            }
        }

        /**
         * where
         * ---------------------------------------
         * "where" => [
         *     ["id", $_GET["id"], "="],
         *     ["email", "jeevan", "=", "AND"],
         * ],
         */
        if (isset($options["where"]) && is_array($options["where"]) && !empty($options["where"])) {
            $queryString .= " " . $this->generateWhereSQLQuery($options["where"]) . " ";
        }


        /**
         * order_by
         * ---------------------------------------
         * "order_by" => [
         *     ["id", "DESC"],
         *     ["id2", "ASC"],
         * ],
         */
        if (isset($options["order_by"]) && is_array($options["order_by"])) {
            $queryString .= " ORDER BY ";
            foreach ($options["order_by"] as $key => $value) {
                if (!empty($value[0]) && !empty($value[1])) {
                    // next order by
                    if ($key >= 1) {
                        $queryString .= ", ";
                    }

                    $queryString .= "`{$value[0]}` {$value[1]} ";
                }
            }
        }

        /**
         * limit
         * ---------------------------------------
         *  "limit" => 1,
         */
        if (isset($options["limit"])) {
            $queryString .= "LIMIT " . $options["limit"] . " ";
        }

        /**
         * Output Type (String)
         * ---------------------------------------
         *  "output" => "", ['columns', 'numbers', 'query']
         */
        $outputType = NULL;
        if (isset($options["output"])) {
            $outputType = $this->recordFetchType($options["output"]);
        }

        /**
         *  SELECT * FROM `wff_manifest_installed` 
         *      INNER JOIN wff_manifest_data ON wff_manifest_installed.manifest_id=wff_manifest_data.id
         *  WHERE wff_manifest_installed.email = 'ctechhindi@gmail.com'
         */

        // View Query
        if ($outputType === 1) {
            print_r($queryString);
            die();
        }

        // Query
        $sql = $this->db->conn->prepare($queryString);
        if ($sql->execute()) {

            // For Class
            if (!empty($this->returnType)) {
                $sql->setFetchMode(\PDO::FETCH_CLASS, $this->returnType, [$options]);
            } else {
                if (!empty($outputType)) $sql->setFetchMode($outputType);
            }

            if (isset($options["limit"]) && $options["limit"] == "1") {
                $out = $sql->fetch();
            } else {
                $out = $sql->fetchAll();
            }

            return $out;
        } else {

            return false;
        }
    }


    /**
     * [PRIVATE]
     * 
     * Generate Where MySQL Query
     * 
     * @param {array} where
     * 
     * $where = 
     * [
     *      ["manifest_id", $found["id"], "="],
     *      ["plan_name", "basic", "="]
     * ]
     * @return: WHERE `manifest_id` = '15' AND `plan_name` = 'basic' 
     */
    private function generateWhereSQLQuery($where)
    {
        if (empty($where) || !is_array($where)) {
            return false;
        }

        /**
         * 
         * WHERE STRING QUERY
         * _________________________________________________________________________________________________
         * WHERE `manifest_id` = '15' AND `plan_name` = 'basic' 
         */

        $queryString_WHERE = "WHERE ";

        foreach ($where as $key => $value) {
            if (!empty($value[0]) && isset($value[1])) {
                // where operators (=, <, >, LIKE,..)
                $where_operators = "=";
                if (!empty($value[2])) {
                    $where_operators = $value[2];
                }

                // next where condition (OR, AND)
                if ($key >= 1) {
                    $next_where_condition = "AND";
                    if (!empty($value[3])) {
                        $next_where_condition = $value[3];
                    }

                    $queryString_WHERE .= $next_where_condition . " ";
                }

                $queryString_WHERE .= "`{$value[0]}` {$where_operators} '{$value[1]}' ";
            }
        }

        return trim($queryString_WHERE);
    }

    /**
     * [PRIVATE]
     * 
     * Generate JOIN ON MySQL Query
     * 
     * @param {array} where
     * 
     * $where = 
     * [
            ["manifest_id", "id", "="],
            ["plan_name", "basic", "="]
       ]

     * @return: `manifest_id` = 'id' AND `plan_name` = 'basic' 
     */
    private function generateJoinOnSQLQuery($where)
    {
        if (empty($where) || !is_array($where)) {
            return false;
        }

        /**
         * 
         * STRING QUERY
         * _________________________________________________________________________________________________
         * ON `wff_manifest_installed`.`manifest_id` = `wff_manifest_data`.`id`
         */

        $queryString_WHERE = "";

        foreach ($where as $key => $value) {
            if (!empty($value[0]) && isset($value[1])) {
                // where operators (=, <, >, LIKE,..)
                $where_operators = "=";
                if (!empty($value[2])) {
                    $where_operators = $value[2];
                }

                // next where condition (OR, AND)
                if ($key >= 1) {
                    $next_where_condition = "AND";
                    if (!empty($value[3])) {
                        $next_where_condition = $value[3];
                    }

                    $queryString_WHERE .= $next_where_condition . " ";
                }

                $queryString_WHERE .= "" . $value[0] . "" . $where_operators . "" . $value[1] . " ";
            }
        }

        return trim($queryString_WHERE);
    }


    /**
     * [PRIVATE]
     * 
     * Generate Insert MySQL Query
     * 
     * @param {string} table
     * @param {array}  insert
     * @param {boolean}  isPrepareQuery
     * @return
     * 
     * INSERT INTO Payments (CustomerID, Amount) VALUES('145300', 12)
     * INSERT INTO `application_plans` (`manifest_id`, `plan_name`) VALUES (:manifest_id, :plan_name)
     */
    private function generateInsertSQLQuery($table, $insert, $isPrepareQuery = true)
    {
        if (empty($table) || !is_string($table)) {
            return false;
        }
        if (empty($insert) || !is_array($insert)) {
            return false;
        }

        /**
         * 
         * INSERT STRING QUERY
         * _________________________________________________________________________________________________
         * INSERT INTO Payments(CustomerID, Amount) VALUES('145300', 12)
         * INSERT INTO `application_plans` (`manifest_id`, `plan_name`) VALUES (:manifest_id, :plan_name)
         */

        // Merge Array :: `created_at` and `updated_at` table columns
        $insert = array_merge($insert, ["created_at" => time(), "updated_at" => time()]);

        // INSERT
        $field_name = "(";
        $field_value = "(";
        foreach (array_keys($insert) as $field) {
            // (`manifest_id`, `plan_name`)
            $field_name .= "`" . $field . "`, ";

            if ($isPrepareQuery === true) {
                // Prepare Query :: VALUES (:manifest_id, :plan_name)
                $field_value .= ":" . $field . ", ";
            } else {
                // Basic Query :: VALUES('145300', 12)
                if (is_string($insert[$field])) {
                    $field_value .= "'" . $insert[$field] . "', "; // If Value is String 'value'
                } else {
                    $field_value .= "" . $insert[$field] . ", ";
                }
            }
        }
        $field_name = substr($field_name, 0, -2) . ")"; // remove last (,) and join last bracket
        $field_value = substr($field_value, 0, -2) . ")"; // remove last (,) and join last bracket

        // INSERT
        $queryString_INSERT = "INSERT INTO `" . $table . "` $field_name VALUES $field_value";

        return trim($queryString_INSERT);
    }

    /**
     * [PRIVATE]
     * 
     * Generate Update MySQL Query
     * 
     * @param {string} table
     * @param {array}  insert
     * @param {boolean}  isPrepareQuery
     * @return
     * 
     * UPDATE Payments SET Amount = 12 WHERE CustomerID = '145300'
     * UPDATE `application_plans` SET `updated_at`= :updated_at, `manifest_id`= :manifest_id WHERE CustomerID = '145300'
     */
    private function generateUpdateSQLQuery($table, $update, $where, $isPrepareQuery = true)
    {
        if (empty($table) || !is_string($table)) {
            return false;
        }
        if (empty($update) || !is_array($update)) {
            return false;
        }
        if (empty($where) || !is_array($where)) {
            return false;
        }

        /**
         * 
         * UPDATE STRING QUERY
         * _________________________________________________________________________________________________
         * UPDATE Payments SET Amount = 12 WHERE CustomerID = '145300'
         * UPDATE `application_plans` SET `updated_at`= :updated_at, `manifest_id`= :manifest_id WHERE CustomerID = '145300'
         */

        // Merge Array :: `updated_at` table columns
        $update = array_merge($update, ["updated_at" => time()]);

        // UPDATE
        $update_str = "";
        foreach (array_keys($update) as $field) {
            if ($isPrepareQuery === true) {
                // Prepare Query :: `updated_at`= :updated_at, `manifest_id`= :manifest_id
                $update_str .= "`" . $field . '`= :' . $field . ', ';
            } else {
                // Basic Query :: Amount = 12, ..
                if (is_string($update[$field])) {
                    $update_str .= "`" . $field . '`= "' . $update[$field] . '", ';
                } else {
                    $update_str .= "`" . $field . '`= ' . $update[$field] . ', ';
                }
            }
        }
        $update_str = substr($update_str, 0, -2); // remove last (,)

        // QUERY
        $queryString_INSERT = "UPDATE `{$table}` SET ${update_str} " . $this->generateWhereSQLQuery($where);

        return trim($queryString_INSERT);
    }

    /**
     * Which Type are you using for query output.
     * @param {string} type
     */
    private function recordFetchType($type)
    {
        /**
         * 
         * (MYSQLI_NUM) /PDO::FETCH_NUM returns enumerated array
         * (MYSQLI_ASSOC) /PDO::FETCH_ASSOC returns associative array
         * (MYSQLI_BOTH) /PDO::FETCH_BOTH - both of the above
         * ()  /PDO::FETCH_OBJ returns object
         * ()  /PDO::FETCH_LAZY allows all three (numeric associative and object) methods without memory overhead.
         */

        if ($type === "columns") {
            return MYSQLI_NUM; // Int 2
        } else if ($type === "numbers") {
            return MYSQLI_BOTH; // Int 3
        } else if ($type === "query") {
            return MYSQLI_ASSOC; // Int 1
        } else {
            return NULL;
        }
    }
}
