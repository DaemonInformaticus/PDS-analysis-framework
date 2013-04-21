<?php
include_once("CData.php");
include_once("CDataLanguageLine.php");

class CDataLanguage extends CData
{
  private $m_arrLines;

  function __construct($id, $bPrefetchAll, $envs)
  {
    $arr = array("id", "name", "description", "active", "abbreviation", "created", "updated", "createdBy", "updatedBy");
    CData::__construct($id, $bPrefetchAll, "tblLanguage", $envs, $arr);

    $this->m_arrLines = array();
  }

  /*
    getLanguageByName: load the id of a language by its given name.
      input: (string)strName: name of the language set, as defined in the database.
      output: (boolean):  true:  A language with the given name was found and the id is loaded into the object..
                          false: No language with the given name was found.
  */
  public function getLanguageByName($strName)
  {
    $sql = "SELECT id FROM tblLanguage WHERE name='$strName';";
    $qry = mysql_query($sql, $this->getConnection());
    if($row = mysql_fetch_row($qry))
    {
      $this->setValue("id", $row[0], true);
      return true;
    }

    return false;
  }

  /*
    translate: Translate a given key from the language currently loaded.
      input:  (string)key: name of the field.
      output: (string): translation. empty if no translation was found, or the field is empty in the database.
  */
  public function translate($key)
  {
    $line = NULL;
    // $languageID = $this->getValue("id");

    // print("CDataLanguage::translate: languageID: $languageID<br>\n");
    // See if an active language is loaded:
    // id > 0?
    if($this->getValue("id") == 0)
      return false;

    // active = 1?
    if($this->getValue("active") == 0)
      return false;

    // print("CDataLanguage::translate: id and active are valid<br>\n");
    // See if this language object translated the key before:
    if(isset($this->m_arrLines[$key]))
      $line = $this->m_arrLines[$key];

    // Get the key from database'
    if($line == NULL)
    {
      // print("CDataLanguage::translate: getting line from database. <br>\n");
      // Create a new line object.
      $line = new CDataLanguageLine(0, false, $this->m_envs);
      // Use the key to find an id and the associated values.
      if($line->getTranslationByKey($this->getValue("id"), $key))
      {
        // print("CDataLanguage::translate: found by key $key!<br>\n");
        // If the element is not active: drop it.
        if($line->getValue("active") == 0)
        {
          // print("CDataLanguage::translate: Line destroyed!<br>\n");
          $line = NULL;
        }
      }
      else
      {
        // If there was no translation for this key: drop the object
        // print("CDataLanguage::translate: key $key not found!<br>\n");
        $line = NULL;
      }

      // If there was a line object found in the database: store it.
      if($line != NULL)
        $this->m_arrLines[$key] = $line;
    }

    // print("CDataLanguage::translate: value: ".$line->getValue("value")."<br>\n");

    // If there was a line: return the value. Otherwise return an empty string.
    return $line != NULL ? $line->getValue("value") : "$key";
  }

  /*
    updateLine: Update a language line for a given key, in this language.
                Alternatively, if the key does not exist yet in this language, a new key is created. The userID in 'updatedBy' is then used for 'createdBy'.

      input:  (String)$key:     Name of the object to update.
              (String)$value:   New Value of the key
              (int)$updatedBy:  userID of the person updating the language.

      output: (boolean) success.
  */
  public function updateLine($key, $value, $updatedBy)
  {
    // Create a new line object.
    $line = new CDataLanguageLine(0, false, $this->m_envs);

    // Use the key to find an id and the associated value.
    if($line->getTranslationByKey($this->getValue("id"), $key))
    {
      $line->setValue("value",      $value);
      $line->setValue("updatedBy",  $updatedBy);

      $line->updateValues();
    }
    else
    {
      // Key doesn't exist yet for this language. Create a new one.
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
      $newLine = new CDataLanguageLine(0, false, $this->m_envs);
      $newLine->setValue("languageID",  $this->getValue("id"),  true);
      $newLine->setValue("fieldname",   $key,                   true);
      $newLine->setValue("value",       $value,                 true);
      $newLine->setValue("active",      1,                      true);
      $newLine->setValue("created",     date("Y-m-d H:i"),      true);
      $newLine->setValue("createdBy",   $updatedBy,             true);

      $newLine->insertValues();

    }

    return true;
  }
}
?>