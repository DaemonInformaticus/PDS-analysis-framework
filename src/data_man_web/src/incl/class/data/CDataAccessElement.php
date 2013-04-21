<?php
include_once("CData.php");


class CDataAccessElement extends CData
{
  function __construct($id, $bPrefetchAll, $envs)
  {
    // CData::__construct($id, $bPrefetchAll, $tblName, $dbConn)

    if($id == 0)
      $bPrefetchAll = false;
    $arr = array("id", "name", "description", "active", "created", "updated", "createdBy", "updatedBy");

    CData::__construct($id, $bPrefetchAll, "tblAccessElements", $envs, $arr);
  }

  /*
    getAccessByName: retrieve the id of the element that is referenced by name.
      input:  (String)  elementName: name of the element for which an id has to be found.
      output: (boolean) true:   the element has been found and the value 'id' has been set.
                        false:  no element by the given name has been found.
  */
  public function getAccessByName($elementName)
  {
    $sqlAccess = "SELECT id FROM tblAccessElements WHERE name='$elementName';";
    $qryAccess = mysql_query($sqlAccess, $this->getConnection());
    if(!($rowAccess = mysql_fetch_row($qryAccess)))
      return false;

    $id = $rowAccess[0];
    $this->setValue("id",   $id,          true);
    $this->setValue("name", $elementName, true);

    return true;
  }
}

?>