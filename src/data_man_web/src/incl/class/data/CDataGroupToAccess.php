<?php
include_once("CData.php");

class CDataGroupToAccess extends CData
{

  function __construct($id, $bPrefetchAll, $envs)
  {
    if($id == 0)
      $bPrefetchAll = false;

    /*
    tblGroupToAccess
    - id        INT PRIMARY KEY AUTO_INCREMENT
    - groupID   INT
    - accessID  INT
    - created   DATETIME
    - updated   DATETIME
    - createdBy INT
    - updatedBy INT
    */
    $arr = array("id", "groupID", "accessID", "created", "updated", "createdBy", "updatedBy");

    CData::__construct($id, $bPrefetchAll, "tblGroupToAccess", $envs, $arr);
  }

  /*
    getIDByRef: Get the id of the group to access reference, based on groupID and accessID.

      input:  (int)$groupID:  id of the group referenced.
              (int)$accessID: id of the access element referenced.

      output: (boolean)success: true:   Element found. (id, groupID and accessID are stored in the object.)
                                false:  no element with these references found.
  */
  public function getIDByRef($groupID, $accessID)
  {
    // init return value.
    $bFound = false;

    // query the id, based on references.
    $sql    = "SELECT id FROM tblGroupToAccess WHERE groupID=$groupID AND accessID=$accessID;";
    $qry    = mysql_query($sql, $this->getConnection());

    // if a row was found.
    if($row = mysql_fetch_row($qry))
    {
      // set result to true.
      $bFound = true;

      // store all values.
      $id     = $row[0];

      $this->setValue("id",       $id,        true);
      $this->setValue("groupID",  $groupID,   true);
      $this->setValue("accessID", $accessID,  true);
    }

    // return success.
    return $bFound;
  }
}
?>