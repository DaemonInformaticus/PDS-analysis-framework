<?php
include_once("CData.php");

class CDataLanguageLine extends CData
{

  function __construct($id, $bPrefetchAll, $envs)
  {

    $arr = array("id", "languageID", "fieldname", "value", "active", "created", "updated", "createdBy", "updatedBy");
    CData::__construct($id, $bPrefetchAll, "tblLanguageLine", $envs, $arr);
  }

  /*
    getTranslationByKey: Get a translation directly by its languageID and key.

      input:  (int)$languageID: id of the language.
              (String)$key:     name of the element.
  */
  public function getTranslationByKey($languageID, $key)
  {
    $sql = "SELECT id FROM tblLanguageLine WHERE languageID=$languageID AND fieldname='$key';";
    $qry = mysql_query($sql, $this->getConnection());
    if($row = mysql_fetch_row($qry))
    {
      $this->setValue("id", $row[0], true);
      return true;
    }

    return false;
  }
}
?>