<?php
include_once("CData.php");
include_once("CDataAccessElement.php");
include_once("CDataGroupToAccess.php");

class CDataUserGroup extends CData
{
  private $m_arrElements;

  function __construct($id, $bPrefetchAll, $envs)
  {
    $m_arrElements = array();

    if($id == 0)
      $bPrefetchAll = false;

    /*
    tblUserGroup
    - id        INT PRIMARY KEY AUTO_INCREMENT
    - name      TEXT
    - created   DATETIME
    - updated   DATETIME
    - createdBy INT
    - updatedBy INT
    */
    $arr = array("id", "name", "created", "updated", "createdBy", "updatedBy");

    CData::__construct($id, $bPrefetchAll, "tblUserGroup", $envs, $arr);
  }

  public function getElements()
  {
    if(count($this->m_arrElements) > 0)
      return $this->m_arrElements;

    $arr      = array();
    $groupID  = $this->getValue("id");
    $sql      = "SELECT id FROM tblGroupToAccess WHERE groupID=$groupID;";
    $qry      = mysql_query($sql, $this->getConnection());

    while($row = mysql_fetch_row($qry))
    {
      $id           = $row[0];
      $pGroupAccess = new CDataGroupToAccess($id, false, $this->m_envs);
      $accessID     = $pGroupAccess->getValue("accessID");
      $pAccess      = new CDataAccessElement($accessID, false, $this->m_envs);

      array_push($arr, $pAccess);
    }

    $this->m_arrElements = $arr;

    return $arr;
  }

  public function getGroupByName($name)
  {
    $bFound = false;
    $name   = addslashes($name);
    $sql    = "SELECT id FROM tblUserGroup WHERE name='$name';";
    $qry    = mysql_query($sql, $this->getConnection());

    if($row = mysql_fetch_row($qry))
    {
      $bFound = true;
      $id     = $row[0];

      $this->setValue("id", $id, true);
    }

    return $bFound;
  }
}
?>