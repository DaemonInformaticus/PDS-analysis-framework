<?php
include_once("CDataLanguage.php");
include_once("CDataLanguageLine.php");

class CDataLanguageSet
{

  private $m_arrLanguages;
  private $m_bActiveOnly;
  private $m_envs;
  private $m_dbConn;

  function __construct($bActiveOnly, $envs)
  {
    $this->m_arrLanguages = array();
    $this->m_bActiveOnly  = $bActiveOnly;
    $this->m_envs         = $envs;

    $pData                = new CData(0, false, "", $envs);
    $this->m_dbConn       = $pData->getConnection();

    $this->loadLanguages();
  }

  /*
    getLanguageList: Get the raw array of all languages loaded by specified parameters in the constructor of this class.
  */
  public function getLanguageList() { return $this->m_arrLanguages; }

  /*
    loadLanguages: called on initialization: Load all elements specified by parameters.
  */
  private function loadLanguages()
  {
    $sql = "SELECT id FROM tblLanguage";
    if($this->m_bActiveOnly)
    {
      $sql .= " WHERE active=1;";
    }
     else
    {
      $sql .= ";";
    }

    $qry = mysql_query($sql, $this->m_dbConn);
    while($row = mysql_fetch_row($qry))
    {
      $language = new CDataLanguage($row[0], false, $this->m_envs);
      array_push($this->m_arrLanguages, $language);
    }
  }

  /*
    getLanguageByID: return a CDataLanguage object by its id.

      input:  (int)$id: id of the language object.

      output: (CDataLanguage)language object
  */
  public function getLanguageByID($id)
  {
    $langLen = count($this->m_arrLanguages);

    for($i = 0; $i < $langLen; $i++)
    {
      $currLang = $this->m_arrLanguages[$i];
      if($currLang->getValue("id") == $id)
        return $currLang;
    }

    return NULL;
  }

  /*
    getKeys: Get all keys in the language set.
  */
  public function getKeys()
  {

    /*
    tblLanguageLine
    - id          INT PRIMARY KEY AUTO_INCREMENT
    - languageID  INT
    - fieldname   TEXT
    - value       TEXT
    - active      INT
    - created     DATETIME
    - updated     DATETIME
    - createdBy   INT
    - updatedBy   INT
    */

    $sql      = "SELECT fieldname FROM tblLanguageLine GROUP BY fieldname ORDER BY fieldname ASC;";
    $qry      = mysql_query($sql, $this->m_dbConn);
    $arrKeys  = array();

    while($row = mysql_fetch_row($qry))
    {
      array_push($arrKeys, $row[0]);
    }

    return $arrKeys;
  }

  public function getLinesByLanguage($languageID)
  {
    $arr = array();
    $sql = "SELECT id FROM tblLanguageLine WHERE languageID=$languageID;";
    $qry = mysql_query($sql, $this->m_envs['dbConn']);

    while($row = mysql_fetch_row($qry))
    {
      $id = $row[0];
      $pLanguageLine = new CDataLanguageLine($id, false, $this->m_envs);
      array_push($arr, $pLanguageLine);
    }

    return $arr;
  }

}

?>
