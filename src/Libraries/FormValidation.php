<?php namespace Ctechhindi\CorePhpFramework\Libraries;

use CTH\Config\Database;

class FormValidation
{
    /**
     * Validation Rules
     */
    public $rules = [];

    /**
     * Validation Rule Name
     * ---------------------
     * required
     * valid_email
     * valid_token : for check form token in the session data
     * xss_clean : htmlspecialchars
     * unique : unique[table.column]
     * in_list : in_list[beta,stable]
     * numeric
     * alpha : NOT ALLOW, if field value anything other than alphabetic characters.
     * alpha_space : NOT ALLOW, if field value anything other than alphabetic characters or spaces.
     * alpha_numeric : NOT ALLOW, if field value anything other than alphanumeric characters.
     * max_length : max_length[8]
     * min_length : min_length[3]
     */
    private $ruleName = [
        "required",
        "valid_email",
        "valid_token",
        "xss_clean",
        "unique",
        "in_list",
        "numeric",
        "alpha",
        "alpha_space",
        "alpha_numeric",
        "max_length",
        "min_length",
    ];

    /**
     * Validation Errors
     */
    public $errors = [];


    public function __construct($rules = []) {
        $this->rules = $rules;
    }

    /**
     * [PUBLIC]
     * 
     * Check Validation Rules
     */
    public function isValid() {

        // Check Validation Rules Array is Valid
        if (empty($this->rules) || !is_array($this->rules)) {
            return false;
        }

        // Fetch From Data
        $formData = $this->fetchFormData();
        if ($formData === false) { return false; }

        foreach ($this->rules as $field => $data) {
            // echo "Form Field Name: <strong>". $field. "</strong><br />";
            // echo "Form Field Rules: <strong>". $data["rules"]. "</strong><br />";

            // Field Label Name
            $fieldName = $field;

            // Check Field `Label` is Found
            if (isset($data["label"]) && !empty($data["label"])) {
                $fieldName = $data["label"];
            }

            // Check Field Validation Rules
            $rulesArray = explode("|", $data["rules"]);
            if (!isset($data["rules"]) && empty($rulesArray)) {
                $this->errors[$field] = sprintf("Validation rules of %s field are not defined.", $fieldName);
            }

            // Field Rules
            for ($i=0; $i < count($rulesArray); $i++) {
                $rule = $rulesArray[$i];

                // if rule value found then explode rule value and rule name
                $rule_value = "";
                $ruleExplode = explode("[", $rule);
                if (isset($ruleExplode[1]) && !empty($ruleExplode[1]) && !empty(rtrim($ruleExplode[1],  "]"))) {
                    // Remove last char `]`
                    $rule_value = rtrim($ruleExplode[1],  "]");
                    $rule = $ruleExplode[0];
                }

                // Check Rule
                if (in_array($rule, $this->ruleName) === true) {

                    // Check validation rule `required`
                    $isRequiredField = false;
                    if (in_array("required", $rulesArray) === true) {
                        $isRequiredField = true;
                    }

                    // Check Field Exists in the Form Data 
                    if (array_key_exists($field, $formData) === false) {
                        // If (required) validation rule is exists.
                        if ($isRequiredField === true) {
                            $this->errors[$field] = sprintf("The %s field data is not being received.", $fieldName);
                            break;
                        } else {
                            continue;
                        }
                    }
                    
                    $output = $this->valid_rules($field, $formData[$field], $rule, $rule_value, $isRequiredField);
                    if ($output === false) {

                        $this->errors[$field] = sprintf($this->validation_rule_message($rule, $rule_value), $fieldName);
                        break;
                    }
                    
                } else {
                    $this->errors[$field] = sprintf("The validation rule %s is not the correct rule.", $rule);
                    break;
                }
            }
        }
            
        return $this->errors;
    }

    /**
     * [PRIVATE]
     * 
     * Check Data with Rule
     * 
     * @param {string} field
     * @param {*} field value
     * @param {string} rule
     * @param {string} rule value
     * @param {boolean} isRequired
     * 
     * @return {boolean}
     */
    private function valid_rules($field, $value, $rule, $rule_value, $isRequired) {

        // Validation Rules
        if ($rule === "required")
        {
            if (is_array($value) && count($value) > 0) { return true; }
            if (!empty(trim($value))) { return true; }
            return false;
        }
        else if ($rule === "valid_email")
        {
            if ($isRequired === false && empty($value)) {
                return true;
            } else if (filter_var(trim($value), FILTER_VALIDATE_EMAIL)) {
                return true;
            } return false;
        } 
        else if ($rule === "valid_token")
        {
            if (!isset($_SESSION["FORM_TOKEN"]) || empty($_SESSION["FORM_TOKEN"]) || empty($value)) {
                return false;
            }

            if ($_SESSION["FORM_TOKEN"] === $value) {
                return true;
            }

            return false;
        } 
        else if ($rule === "xss_clean")
        {
            if (strtolower($_SERVER['REQUEST_METHOD']) === "post") {

                if (isset($_POST[$field]) && !empty($_POST[$field])) {
                    if (!is_array($value)) {
                        $_POST[$field] = htmlspecialchars($value);
                    }
                }
                return true;

            } else if (strtolower($_SERVER['REQUEST_METHOD']) === "get") {

                if (isset($_GET[$field]) && !empty($_GET[$field])) {
                    $_GET[$field] = htmlspecialchars($value);
                }
                return true;

            } else {
                return false;
            }
            
        } 
        else if ($rule === "unique")
        {
            // if not required field and field value in empty
            if ($isRequired === false && empty($value)) {
                return true;
            }

            // if field value not empty then explode table and table column name
            $table_data = explode(".", $rule_value);
            if (count($table_data) === 2 && !empty($table_data[0]) && !empty($table_data[1])) {
                
                $tableName = $table_data[0]; // Database Table Name
                $tableColumn = $table_data[1]; // Database Table Column

                // Connect to Database
                $db = new Database();
                $db->connect();

                $sql = "SELECT * FROM `{$tableName}` WHERE `{$tableColumn}` = '{$value}'";
                $sql = $db->conn->prepare($sql);
                if ($sql->execute())
                {
                    // Close
                    $db->close();

                    if (empty($sql->fetchAll())) { return true; }
                    else { return false; }
                } 
            }
            return false;
        }
        else if ($rule === "numeric")
        {
            // NOT ALLOW :: if field value anything other than numeric characters.

            if ($isRequired === false && empty($value)) {
                return true;
            }

            if (preg_match ("/^[0-9]*$/", $value) ) {  
                return true;
            }

            return false;
        }
        else if ($rule === "alpha")
        {
            // NOT ALLOW, if field value anything other than alphabetic characters.

            if ($isRequired === false && empty($value)) {
                return true;
            }

            if (preg_match ("/^[a-zA-z]*$/", $value)) {  
                return true;
            }

            return false;
        }
        else if ($rule === "alpha_space")
        {
            // NOT ALLOW, if field value anything other than alphabetic characters or spaces.

            if ($isRequired === false && empty($value)) {
                return true;
            }

            if (preg_match ("/^[a-zA-z .]*$/", $value)) {  
                return true;
            }

            return false;
        }
        else if ($rule === "alpha_numeric")
        {
            // NOT ALLOW, if field value anything other than alphanumeric characters.

            if ($isRequired === false && empty($value)) {
                return true;
            }

            if (preg_match ("/^[a-zA-z0-9]*$/", $value)) {  
                return true;
            }

            return false;
        }
        else if ($rule === "in_list")
        {
            if ($isRequired === false && empty($value)) {
                return true;
            }

            // If field value not empty then explode `in_list[beta,stable]`
            $allowList = explode(",", $rule_value);

            // If allow list/array is empty.
            if (empty($allowList)) { return false; }

            // Check field value in the allow list
            if (in_array($value, $allowList)) {
                return true;
            }
            
            return false;
        }
        else if ($rule === "max_length")
        {
            // NOT ALLOW, if field value is longer than the parameter value.

            if ($isRequired === false && empty($value)) {
                return true;
            }

            if (strlen($value) <= (int) $rule_value) {  
                return true;
            }

            return false;
        }
        else if ($rule === "min_length")
        {
            // NOT ALLOW, if field value is shorter than the parameter value.

            if ($isRequired === false && empty($value)) {
                return true;
            }

            if (strlen($value) >= (int) $rule_value) {  
                return true;
            }

            return false;
        }
    }

    /**
     * [PRIVATE]
     * 
     * Return Validation Message According to Validation Rule
     * 
     * @param {string} rule
     * @param {string} rule_value
     */
    private function validation_rule_message($rule, $rule_value) {

        if ($rule === "valid_email")
        {
            return "The %s field is not a valid email address.";

        } else if ($rule === "required")
        {
            return "The %s field data is required.";

        } else if ($rule === "valid_token")
        {
            return "The %s field data is valid.";

        } else if ($rule === "unique")
        {
            return "The %s field data already exists.";
        }
        else if ($rule === "numeric")
        {
            return "The %s field only allow numbers.";
        }
        else if ($rule === "alpha")
        {
            return "The %s field only allow alphabets.";
        }
        else if ($rule === "alpha_space")
        {
            return "The %s field only allow alphabets and whitespace.";
        }
        else if ($rule === "alpha_numeric")
        {
            return "The %s field only allow alphanumeric characters.";
        }
        else if ($rule === "in_list")
        {
            return "The %s field only allow within a predetermined list.";
        }
        else if ($rule === "max_length")
        {
            return "The %s field only allow maximum ". $rule_value ." characters.";
        }
        else if ($rule === "min_length")
        {
            return "The %s field only allow minimum ". $rule_value ." characters.";
        }
        
    }

    /**
     * [PRIVATE]
     * 
     * Fetch Form Data According to Request Method
     */
    private function fetchFormData() {

        if (strtolower($_SERVER['REQUEST_METHOD']) === "post") {
            return $_POST;
        } else if (strtolower($_SERVER['REQUEST_METHOD']) === "get") {
            return $_GET;
        } else {
            return false;
        }
    }

    /**
     * [PUBLIC]
     * 
     * Manually Insert Error Message
     * @param {string|array} errors
     */
    public function insert_error_message($errors) {

        if (empty($errors)) { return false; }

        if (is_string($errors)) {

            array_push($this->errors, $errors);

        } else if (is_array($errors)) {

            foreach ($errors as $key => $value) {
                array_push($this->errors, $value);
            }
        }
    }

    /**
     * [PUBLIC]
     * 
     * Return Errors in the List Format
     * ---------------------------------------
     * @param {array} options["style"] = ""
     * @param {string} output [html, json]
     */
    public function errorList($options = [], $output = "html") {

        $html = "";

        // if validation errors is empty
        if (empty($this->errors)) {
            return $html;
        }

        // Validation Output
        if ($output === "html")
        {
            $html .= "<ul style='". (isset($options["style"]) && !empty($options["style"])? $options["style"]:'') ."'>";
            foreach ($this->errors as $key => $value) {
                $html .= "<li>". $value ."</li>";
            }
            $html .= "</ul>";

            return $html;
        }

        if ($output === "json")
        {
            return $this->errors;
        }
    }
}