<?php
include_once("CDataUserGroup.php");

class CDataUserGroupSet
{
  private $m_envs;
  private $m_arrGroups;

  function __construct($envs)
  {
    $this->m_envs       = $envs;
    $this->m_arrGroups  = array();
  }

  public function getGroups($filter)
  {
    if(count($this->m_arrGroups) > 0)
      return $this->m_arrGroups;

    $arr = array();
    $sql = "SELECT id FROM tblUserGroup";

    if($filter != "")
    {
      $filter = addslashes($filter);
      $sql   .= " WHERE name like '%$filter%'";
    }

    $sql .= ";";
    $qry  = mysql_query($sql, $this->m_envs['dbConn']);

    while($row = mysql_fetch_row($qry))
    {
      $id     = $row[0];
      $pGroup = new CDataUserGroup($id, false, $this->m_envs);

      array_push($arr, $pGroup);
    }

    $this->m_arrGroups = $arr;

    return $arr;
  }

}

?>