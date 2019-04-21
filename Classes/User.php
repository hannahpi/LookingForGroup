<?php

require_once 'DebugHelper.php';
require_once '../config/config.php';
require_once 'Technology.php';

class User {
    private $conn;
    private $table_name = 'users';
    private $attributes;
    private $debugH;
    private $id; //primary key, no reason to change this.
    private $dirty;

    public $groupID;
    public $email;
    public $username;
    public $startTime;
    public $duration;
    public $timezoneOffset;
    public $dst;
    public $location;
    public $verificationHash;


    /**
     * function: interpretItem
     * purpose: converts extracted data from db to an array.
     */
    private function interpretItem($dbRow) {
        $dbUser = array(
            "ID" => $dbRow["ID"],
            "GroupID" => $dbRow["GroupID"],
            "Email" => $dbRow["Email"],
            "Username" => $dbRow["Username"],
            "StartTime" => $dbRow["StartTime"],
            "Duration" => $dbRow["Duration"],
            "TimezoneOffset" => $dbRow["TimezoneOffset"],
            "DST" => $dbRow["DST"],
            "Location" => $dbRow["Location"],
            "VerificationHash" => $dbRow["VerificationHash"]
        );
        return $dbUser;
    }

    public function __construct($conn, $attributes) {
        $this->attributes = $attributes;
        $this->conn = $conn;
        $this->debugH = new DebugHelper();
        $this->debugH->addObject($this);
        $this->dirty = false;
    }

    public function createNew($email, $username, $startTime, $duration,
        $timezoneOffset, $dst=false, $location=NULL, $groupID=NULL
      ) {  //verification hash is generated
        $this->email = $email;
        $this->username = $username;
        $this->startTime = $startTime;
        $this->duration = $duration;
        $this->timezoneOffset = $timezoneOffset;
        $this->dst = $dst;
        $this->location = $location;
        $this->groupID = $groupID;

        if ($GLOBALS['SKIP_EMAIL_CONFIRMATION']) {
            $this->verificationHash = $GLOBALS['CONFIRMED'];
        } else {
            $verificationHash = chr(random_int(33,126)); //generate random ascii sequence
            for ($i=1; $i<15; $i++) {
                $verificationHash .= chr(random_int(33,126));
            }
            $this->verificationHash = crypt($verificationHash);
        }

        $query = " INSERT INTO ". $this->table
                ." (ID, Username, StartTime, Duration, TimezoneOffset, DST, "
                . " Location, GroupID, VerificationHash) "
                ." VALUES (NULL, :email, :username, :startTime, :duration, :timezoneOffset, "
                ." :dst, :location, :groupID, :verificationHash ); ";


        if (!$GLOBALS['SKIP_EMAIL_CONFIRMATION']) {
            //send email with confirmation link
		        $headers = "From: " . $GLOBALS['AUTO_ADMIN_NAME'] . " " . $GLOBALS['AUTO_ADMIN_EMAIL'];
		        $subject = "Confirm your email address";
		        $message = "Please confirm your email address at ". $GLOBALS['FQP'] . "/verifyemail.html?confirmNum=$passGen&Email=$email \n"
		                 . "If you have problems you may go back to ". $GLOBALS['FQP'] . "/getconfirm.html and try again!";
		        mail($email,$subject,$message,$headers);
        }

        $stmt = $this->conn->prepare($query, $this->attributes);
        $stmt->bindValue(":ID", $this->id, PDO::PARAM_INT);  //this should be NULL
        $stmt->bindValue(":username", $this->username, PDO::PARAM_STR);
        $stmt->bindValue(":startTime", $this->startTime, PDO::PARAM_INT);
        $stmt->bindValue(":duration", $this->duration, PDO::PARAM_INT);
        $stmt->bindValue(":timezoneOffset", strval($this->timezoneOffset), PDO::PARAM_STR);
        $stmt->bindValue(":dst", $this->dst, PDO::PARAM_BOOL);
        $stmt->bindValue(":location", $this->location, PDO::PARAM_STR);
        $stmt->bindValue(":groupID", $this->groupID, PDO::PARAM_INT);
        $stmt->bindValue(":verificationHash", $this->verificationHash, PDO::PARAM_STR);
        $stmt->execute() or $this->debugH->errormail("Unknown", "Create new user failed", "Create User Query failed.");
        if ($stmt->rowCount()==0)
            return;
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return print_r(json_encode($this->interpretItem($row)),true);
    }

    public function setEmail($email) {
        if ($this->id) {
            if ($this->email != $email) {
                $this->email = $email;
                $this->dirty = true;
            }
        } else {
            throw new Exception('Set email for an uninitialized ' . get_class($this));
        }
    }

    public function setUsername($username) {
        if ($this->id) {
            if ($this->username != $username) {
                $this->username = $username;
                $this->dirty = true;
            }
        } else {
            throw new Exception ('Set username for an uninitialized ' . get_class($this));
        }
    }

    public function setStartTime($startTime) {
        if ($this->id) {
            if ($this->firstName != $firstName) {
                $this->firstName = $firstName;
                $this->dirty = true;
            }
        } else {
            throw new Exception ('Set startTime for an uninitialized ' . get_class($this));
        }
    }

    public function setDuration($duration) {
        if ($this->id) {
            if ($this->duration != $duration) {
                $this->duration = $duration;
                $this->dirty = true;
            }
        } else {
            throw new Exception ('Set duration for an uninitialized ' . get_class($this));
        }
    }

    public function setTimezoneOffset($timezoneOffset) {
        if ($this->id) {
            if ($this->timezoneOffset != $timezoneOffset) {
                $this->timezoneOffset = $timezoneOffset;
                $this->dirty = true;
            }
        } else {
            throw new Exception ('Set timezoneOffset for an uninitialized ' . get_class($this));
        }
    }

    public function setDST($dst) {
        if (!empty($this->dst)) {
            if ($this->dst != $dst) {
                $this->dst = $dst;
                $this->dirty = true;
            }
        } else {
            throw new Exception ('Set dst for an uninitialized ' . get_class($this));
        }
    }

    public function setLocation($location) {
        if ($this->location) {
            if ($this->location != $location) {
                $this->location = $location;
                $this->dirty = true;
            }
        } else {
            throw new Exception ('Set location for an uninitialized ' . get_class($this));
        }
    }

    public function setGroupID($groupID) {
        if ($this->groupID) {
            if ($this->groupID != $groupID) {
                $this->groupID = $groupID;
                $this->dirty = true;
            }
        } else {
            throw new Exception ('Set location for an uninitialized ' . get_class($this));
        }
    }

    public function setVerificationHash($verificationHash) {
        if ($this->verificationHash) {
            if ($this->verificationHash != $verificationHash) {
                $this->verificationHash = $verificationHash;
                $this->dirty = true;
            }
        } else {
            throw new Exception ('Set verificationHash for an uninitialized ' . get_class($this));
        }
    }

    public function getID() {
        return $this->id;
    }

    public function getByID($id, $json=false) {
        $query = "SELECT ID, Email, Username, StartTime, Duration, TimezoneOffset, "
               . " DST, Location, GroupID, VerificationHash "
               . " FROM " . $this->table
               . " Where ID = :id ";

        $stmt = $this->conn->prepare($query, $this->attributes);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute() or $this->debugH->errormail("Unknown", "Get by ID failed", "User Query failed.");
        if ($stmt->rowCount()==0)
            return;
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($json) {
            return print_r(json_encode($this->interpretItem($row)), true);
        } else {
            $this->id = $row["ID"];
            $this->email = $row["Email"];
            $this->username = $row["Username"];
            $this->startTime = $row["StartTime"];
            $this->duration = $row["Duration"];
            $this->timezoneOffset = $row["TimezoneOffset"];
            $this->dst = $row["DST"];
            $this->location = $row["Location"];
            $this->groupID = $row["GroupID"];
            $this->verificationHash = $row["VerificationHash"];
        }
    }

    public function get($userEmail, $json=true) {
      $query = "SELECT ID, Email, Username, StartTime, Duration, TimezoneOffset, "
             . " DST, Location, GroupID, VerificationHash "
             . " FROM " . $this->table
             . " Where Email = :email ";

        $stmt = $this->conn->prepare($query, $this->attributes);
        $stmt->execute(array(":email"=>$userEmail));
        if ($stmt->rowCount()==0) {
            if ($json)
                return json_encode(array("message" => "No users found."));
            else
                return;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($json) {
            return print_r(json_encode($this->interpretItem($row)), true);
        } else {
            $this->id = $row["ID"];
            $this->email = $row["Email"];
            $this->username = $row["Username"];
            $this->startTime = $row["StartTime"];
            $this->duration = $row["Duration"];
            $this->timezoneOffset = $row["TimezoneOffset"];
            $this->dst = $row["DST"];
            $this->location = $row["Location"];
            $this->groupID = $row["GroupID"];
            $this->verificationHash = $row["VerificationHash"];
        }
    }

    public function getAllJson() {
      $query = "SELECT ID, Email, Username, StartTime, Duration, TimezoneOffset, "
             . " DST, Location, GroupID, VerificationHash "
             . " FROM " . $this->table;

        $stmt = $this->conn->prepare($query, $this->attributes);
        $stmt->execute();
        if ($stmt->rowCount()==0)
            return json_encode(array("message" => "No users found."));
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            if (empty($userArray)){
                $userArray=array($this->interpretItem($row));
            } else {
                array_push($userArray, $this->interpretItem($row));
            }
        }
        return print_r(json_encode($userArray), true);
    }

    public function getTableName() {
        return $this->table;
    }

    public function updateDB() {
        if (isset($this->id) && $this->dirty) {
            $query = " Update " . $this->table . " set Email = :email, Username = :userName, "
                   . " StartTime = :startTime, Duration = :duration,  "
                   . " TimezoneOffset = :timezoneOffset, dst = :dst, "
                   . " Location = :location, GroupID = :groupID, VerificationHash = :verificationHash "
                   . " WHERE ID = :ID ;";

            $stmt = $this->conn->prepare($query, $this->attributes);
            $stmt->bindValue(":ID", $this->id, PDO::PARAM_INT);  //this should be NULL
            $stmt->bindValue(":username", $this->username, PDO::PARAM_STR);
            $stmt->bindValue(":startTime", $this->startTime, PDO::PARAM_INT);
            $stmt->bindValue(":duration", $this->duration, PDO::PARAM_INT);
            $stmt->bindValue(":timezoneOffset", strval($this->timezoneOffset), PDO::PARAM_STR);
            $stmt->bindValue(":dst", $this->dst, PDO::PARAM_BOOL);
            $stmt->bindValue(":location", $this->location, PDO::PARAM_STR);
            $stmt->bindValue(":groupID", $this->groupID, PDO::PARAM_INT);
            $stmt->bindValue(":verificationHash", $this->verificationHash, PDO::PARAM_STR);
            $stmt->execute() or $this->debugH->errormail("Unknown", "Update user failed", "Update User Query failed.");
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
}

 ?>
