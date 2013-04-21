<?php
include_once("CData.php");

class CDataLogbook extends CData
{

  function __construct($id, $bPrefetchAll, $envs)
  {
    if($id == 0)
      $bPrefetchAll = false;

    $arr = array("id", "IP", "description", "type", "website", "created", "updated", "createdBy", "updatedBy");
    CData::__construct($id, $bPrefetchAll, "tblLogbook", $envs, $arr);
  }

  /******************************************************************/
  /* Functions to write specific logbook lines.                     */
  /******************************************************************/
  public function LogbookWriteNotice($userID, $website, $Message)
  {
    $type = 1;
    $this->LogbookWriteLine($userID, $Message, $type, $website);
  }

  public function LogbookWriteWarning($userID, $website, $Message)
  {
    $type = 2;
    $this->LogbookWriteLine($userID, $Message, $type, $website);
  }

  public function LogbookWriteError($userID, $website, $Message)
  {
    $type = 3;
    $this->LogbookWriteLine($userID, $Message, $type, $website);
  }

  /*
    LogbookWriteLine: Write a line to tblLogbook.
    input:  (int)$userID:     id of the user that hit the page writing the logbook
            (String)$Message  Message of the event.
            (int)$type        Enumeration of the type of message. (Notice, warning or error)
            (String)$website  Name (or other reference) of the page from which the call came.
  */
  private function LogbookWriteLine($userID, $Message, $type, $website)
  {

    /*
    - id          INT PRIMARY KEY AUTO_INCREMENT
    - IP          TEXT
    - description TEXT
    - type        INT
    - website     TEXT
    - created     DATETIME
    - updated     DATETIME
    - createdBy   INT
    - updatedBy   INT
    */
    $created = date("Y-m-d H:i");
    $IP = $_SERVER['REMOTE_ADDR'];

    // $sqlAddNotice = "INSERT INTO tblLogbook VALUES(0,'$IP', '$Message', $type, '$website', '$created', '', $userID, 0);";
    //mysql_query($sqlAddNotice, $dbConn0);
    $this->setValue("IP",           $IP);
    $this->setValue("description",  $Message);
    $this->setValue("type",         $type);
    $this->setValue("website",      $website);
    $this->setValue("created",      $created);
    $this->setValue("createdBy",    $userID);

    $this->insertLogbookData();
  }
}
?>