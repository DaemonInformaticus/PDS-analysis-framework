<?php
include_once("incl/class/data/CDataLanguage.php");

class CLogicLanguage
{
  private $m_envs;      // database resource
  private $m_language;  // CDataLanguage Object, instantiated with the id, passed through the constructor.

  function __construct($envs, $languageID)
  {
    // print("CLogicLanguage::__construct: languageID: $languageID<br>\n");

    $this->m_envs     = $envs;
    $this->m_language = new CDataLanguage($languageID, false, $envs);

  }

  public function getLanguageID() { return $this->m_language->getValue("id"); }

  /*
    call the translate function of a CDataLanguage object.
      input:  (string)key: name of the line.
      output: (string)translation: the value corresponding to the key, in the given language.
  */
  public function translate($key) { return $this->m_language->translate($key); }


}
?>