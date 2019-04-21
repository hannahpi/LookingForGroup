<?php
session_start();
require_once 'DebugHelper.php';
require_once '../config/config.php';

class Technology {
    private $conn;
    private $table_name = 'Technology';
    private $table_group_tech = "group_tech";
    private $attributes;
    private $debugH;
    private $dirty;
    private $id

    public $name;
    public $desc;

    /**
     * function: interpretItem
     * purpose: converts extracted data from db to an array.
     */
    private function interpretItem($dbRow) {
        $dbTechnology = array(
            "ID" => $dbRow["ID"],
            "Name" => $dbRow["Name"],
            "Desc" => $dbRow["Desc"]
        );
        return $dbTechnology;
    }

    public function __construct($conn, $attributes) {
        $this->attributes = $attributes;
        $this->conn = $conn;
        $this->debugH = new DebugHelper(true);
        $this->debugH->addObject($this);
        $this->dirty = false;
    }

    /**
     * function: createNew
     *
     */
    public function createNew($name, $desc=NULL) {
        $this->name = $name;
        $this->desc = $desc;

        $query = " INSERT INTO " . $this->table . " (`UserID`, `Name`, `Desc`) "
                ." VALUES (NULL, :name, :desc ) ;";

        $stmt = $this->conn->prepare($query, $this->attributes);
        $stmt->bindValue(":name", $this->name, PDO::PARAM_STR);
        $stmt->bindValue(":desc", $this->desc, PDO::PARAM_STR);
        $stmt->execute() or $this->debugH->errormail("Unknown", "Create new session failed", "Create Session Query failed.");
        if ($stmt->rowCount()==0)
            return;
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return print_r(json_encode($this->interpretItem($row)),true);
    }

    public function get($id, $json=false) {
        $query = " SELECT `ID`, `Name`, `Desc` "
               . " FROM  " . $this->table
               . " Where Technology.ID = :id ; "

        $stmt = $this->conn->prepare($query, $this->attributes);
        $stmt->bindValue(":id", $userID, PDO::PARAM_INT);
        $stmt->execute() or $this->debugH->errormail("Unknown", "Get by ID failed", $this->table ." Query failed.");
        if ($stmt->rowCount()==0)
            return;
        if ($json) {
            $rows= $stmt->fetchAll();
            foreach ($rows as $row)
            {
                if empty($sessionsArray) {
                    $sessionsArray = array($this->interpretItem($row));
                } else {
                    array_push($sessionsArray, $this->interpretItem($row));
                }
            }
            return print_r(json_encode($sessionsArray), true);
        } else {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row["ID"];
            $this->name = $row["Name"];
            $this->desc = $row["Desc"];
        }
    }

    public function updateDB() {
        if (isset($this->sessionID) && $this->dirty) {
            $query = " Update `Technology` set `Name` = :name, `Desc` = :desc "
                   . " WHERE `Technology`.ID = :id ;";

            $stmt = $this->conn->prepare($query, $this->attributes);
            $stmt->bindValue(":name", $this->name, PDO::PARAM_STR);
            $stmt->bindValue(":desc", $this->desc, PDO::PARAM_STR);
            $stmt->bindValue(":id", $this->id, PDO::PARAM_INT);
            $stmt->execute() or $this->debugH->errormail("Unknown", "Update Session failed", "Update Session Query failed.");
            if ($stmt->rowCount() == 0)
                return json_encode(array("message"=>"already up to date!"));
            else {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return print_r(json_encode($this->interpretItem($row)),true);
            }
        } else {
            return json_encode(array("message"=>"no changes found to update!"));
        }
    }

    ?>
