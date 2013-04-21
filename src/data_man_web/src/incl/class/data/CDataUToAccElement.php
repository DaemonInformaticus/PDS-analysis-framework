<?php
include_once("CData.php");


class CDataUToAccElement extends CData
{
  function __construct($id, $bPrefetchAll, $envs)
  {
    // CData::__construct($id, $bPrefetchAll, $tblName, $dbConn)

    if($id == 0)
      $bPrefetchAll = false;

    $arr = array("id", "userID", "accessID", "created", "updated", "createdBy", "updatedBy");

    CData::__construct($id, $bPrefetchAll, "tblUserToAccess", $envs, $arr);
  }

  public function getIDByReferences($userID, $accessID)
  {
    $sql = "SELECT id FROM tblUserToAccess WHERE userID=$userID AND accessID=$accessID;";
    $qry = mysql_query($sql, $this->getConnection());

    if($row = mysql_fetch_row($qry))
    {
      $id = $row[0];

      $this->setValue("id",       $id,        true);
      $this->setValue("userID",   $userID,    true);
      $this->setValue("accessID", $accessID,  true);

      return true;
    }

    return false;
  }
}

?>