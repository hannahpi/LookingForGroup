<?php
require_once 'DebugHelper.php';
require_once '../config/config.php';
require_once 'Technology.php';
require_once 'User.php';

class Group {
    private $conn;
    private $table_name = 'groups';
    private $attributes;
    private $debugH;
    private $id;
    private $leader;
    private $technologies = array();
    private $users = array();
    private $leader;

    public $leaderID;
    public $name;
    public $desc;
    public $startTime;
    public $duration;

    /**
     * function: interpretItem
     * purpose: converts extracted data from db to an array.
     */
    private function interpretItem($dbRow) {
        $leaderID = $dbRow["LeaderID"];
        if (isset($leaderID) && $leaderID > 0) {
          $leader = new User();
          $leader.getByID($leaderID);
          $leaderName = $leader->name;
          if ($leaderName == "No Leader") {
            $leaderName = "\"No\" Leader";  //it could've been worse...
          }
        } else {
          $leaderName = "No Leader";  //please for the love of all that is good don't name
                                      //yourself 'No Leader'... seriously just don't
        }
        $dbGroup = array(
            "ID" => $dbRow["ID"],
            "Leader" => $leader->name,
            "Name" => $dbRow["Name"],
            "Desc" => $dbRow["Desc"],
            "StartTime" => $dbRow["StartTime"],
            "Duration" => $dbRow["Duration"]
        );
        return $dbGroup;
    }

    public function __construct($conn, $attributes) {
        $this->attributes = $attributes;
        $this->conn = $conn;
        $this->debugH = new DebugHelper();
        $this->debugH->addObject($this);
    }

    public function createNew($startTime, $duration, $name=NULL,
         $desc=NULL, $leaderID=NULL) {

        $query = " INSERT INTO " . $this->table_name ." (ID, LeaderID, Name, "
                    ." `Desc`, StartTime, Duration ) "
                    ." VALUES (NULL, :leaderID, :name,  "
                    ."  :desc, :startTime, :duration );";

        $stmt = $this->conn->prepare($query, $this->attributes);
        if (isset($leaderID)) {
            $this->leader = new User();
            $this->leader->getByID($leaderID);
            if (empty($this->leader->getID()) || $this->leader->getID() == $leaderID) {
              errormail($GLOBALS['AUTO_ADMIN_EMAIL'],"No such user exist",
                "User ID $leaderID does not exist to set as the leader for this group.",
                "Invalid leader specified.  Try again later.");
            }
        }
        $stmt->bindValue(":leaderID", $leaderID, PDO::PARAM_INT);
        $stmt->bindValue(":name", $name, PDO::PARAM_STR);
        $stmt->bindValue(":desc", $desc, PDO::PARAM_STR);
        $stmt->bindValue(":startTime", $startTime, PDO::PARAM_INT);
        $stmt->bindValue(":duration", $duration, PDO::PARAM_INT);
        $stmt->execute() or $this->debugH->errormail("Unknown", "Create new group failed", "Create Group Query failed.");

        if ($stmt->rowCount()==0)
            return;
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return print_r(json_encode($this->interpretItem($row)),true);
    }

//TODO: fix the following functions:
//TODO: if these functions are fixed remove these todos...
    public function setName($name) {
        if ($this->id) {
            if ($this->name != $name) {
                $this->name = $name;
                $this->dirty = true;
            }
        } else {
            throw new Exception('Set name for an uninitialized ' . get_class($this));
        }
    }

    public function setDesc($desc) {
        if ($this->id) {
            if ($this->desc != $desc) {
                $this->desc = $desc;
                $this->dirty = true;
            }
        } else {
            throw new Exception('Set desc for an uninitialized ' . get_class($this));
        }
    }

    public function setStartTime($user, $startTime) {
        $startTime = new DateTimeInterval();
        $startTime->add(DateInterval::createFromDateString($user->timezoneOffset . " hours"));
        if ($this->id) {
            if ($this->startTime != $startTime) {
                $this->startTime = $startTime;
                $this->dirty = true;
            }
        } else {
            throw new Exception('Set start time for an uninitialized ' . get_class($this));
        }
    }

    public function getByID($id, $json=false) {
        $query = "SELECT ID, LeaderID, Name, `Desc`, StartTime, Duration "
               . "FROM " . $this->table
               . "Where ID = :id ;";

        $stmt = $this->conn->prepare($query, $this->attributes);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute() or $this->debugH->errormail("Unknown Group", "Get Group by ID failed", "Image Name Query failed.");
        if ($stmt->rowCount()==0) {
            if ($json)
                return json_encode(array("message" => "No image found by that id, $id"));
            else
                return;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $row = $this->interpretItem($row);
        if ($json) {
            return print_r(json_encode($row), true);
        } else {
            $this->id = $row["ID"];
            $this->fileName = $row["FileName"];
            if ($row["Anonymous"])
                $this->anonymous=true;
            else
                $this->displayName = $row["DisplayName"];
            $this->name = $row["Name"];
            $this->date = $row["Date"];
            $this->desc = $row["Desc"];
            $this->tags = $row["Tags"];
            $this->filePath = $row["UploadPath"];
        }

    }

    /* getByName
     * returns json of images that match the name.
     * or an array of image/s that match the name.
     */
    public function getByName($name, $json=true) {
        $query = " SELECT i.id, i.UserID, u.DisplayName, i.FileName, i.Name, i.Date, i.Desc, i.Anonymous, u.UploadPath "
                ." FROM Images i, User u "
                ." WHERE i.Name LIKE :name "
                ." AND i.UserID = u.UserID ";

        $stmt = $this->conn->prepare($query, $this->attributes);
        $stmt->bindValue(":name", $name . "%", PDO::PARAM_STR);
        $stmt->execute() or $this->debugH->errormail("Unknown", "get by name failed", "Image Search Query failed.");
        $rowCt = $stmt->rowCount();
        if ($stmt->rowCount()==0) {
            if ($json)
                return json_encode(array("message" => "No images found by that name $name"));
            else
                return;
        } else if ($stmt->rowCount()==1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($json) {
                return print_r(json_encode($this->interpretItem($row)), true);
            } else {
                return array($this->interpretItem($row));
            }
        } else {
            $rows = $stmt->fetchAll();
            foreach ($rows as $row) {
                if (empty($imageArray)) {
                    $imageArray = array($this->interpretItem($row));
                } else {
                    array_push($userArray, $this->interpretItem($row));
                }
            }
            if ($json) {
                return print_r(json_encode($imageArray), true);
            } else {
                return $imageArray;
            }
        }
    }

    public function getJson($id) {
        $query = "SELECT i.ID, u.DisplayName, i.FileName, i.Name, i.Date, i.Desc, i.Anonymous, u.UploadPath "
                ." FROM Images i, User u "
                ." WHERE Images.id = :id "
                ." AND Images.UserID = User.UserID ";

        $stmt = $this->conn->prepare($query, $this->attributes);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute() or $this->debugH->errormail("Unknown", "Get Image by ID failed", "Image Query failed.");
        if ($stmt->rowCount()==0)
            return;
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return print_r(json_encode($this->interpretItem($row)), true);
    }

    public function getAllJson($descMatch="") {
        $query = " SELECT i.ID, u.DisplayName, i.FileName, i.Name, i.Date, i.Desc, i.Anonymous, u.UploadPath "
                ." FROM Images i, User u "
                ." WHERE i.UserID = u.UserID "
                ." AND i.Desc LIKE :descMatch ";

        $stmt = $this->conn->prepare($query, $this->attributes);
        $stmt->bindValue(":descMatch", $descMatch . "%", PDO::PARAM_STR);
        $stmt->execute() or $this->debugH->errormail("Unknown", "Images All Json", "Image Query failed.");
        if ($stmt->rowCount()==0)
            return;
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            if (empty ($imageArray))
                $imageArray = array($this->interpretItem($row));
            else {
                $row = $this->interpretItem($row);
                array_push($imageArray, $row);
            }
        }
        return print_r(json_encode($imageArray), true);
    }
}

 ?>
