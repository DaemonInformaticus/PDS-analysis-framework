<?php
include_once("CSecurePage.php");
include_once("incl/class/data/CDataLanguageLine.php");
include_once("incl/class/logic/CLogicLanguageMan.php");


class CPageLanguageMan extends CSecurePage
{

  // private $m_envs;
  private $m_pLogicLanguageMan;

  function __construct($envs)
  {
    $arrAccessReq = array();
    array_push($arrAccessReq, "cms-edit-lang");
    CSecurePage::__construct($envs, $arrAccessReq, true);

    // $this->m_envs               = $envs;
    $this->m_pLogicLanguageMan  = new CLogicLanguageMan($envs);
  }


  /*
    buildLanguageTable: build a table overview of all languages and all keys, containing all values. And an edit button to edit a given key.
  */
  public function buildLanguageTable()
  {
    $languageList   = $this->m_pLogicLanguageMan->getLanguageList();
    $keyList        = $this->m_pLogicLanguageMan->getKeys();
    $langListLength = count($languageList);
    $keySetLength   = count($keyList);


    // Horizontal: 'keys' | [language 1] | [language 2] | [language n]  | 'options'
    // Vertical:    key 1                                                 edit
    //              key 2                                                 edit
    //              key 3                                                 edit
    //              key 4                                                 edit
    //              key 5                                                 edit
    ?>
    <table border="1">
      <tr>
        <td>Keys:</td>
        <?php
        for($i = 0; $i < $langListLength; $i++)
        {
          $pCurrLang  = $languageList[$i];
          $langName   = $pCurrLang->getValue("description");
          print("<td>$langName</td>");
        }
        print("<td>edit:</td>");
        ?>
      </tr>
      <?php
      // for each key:
      for($i = 0; $i < $keySetLength; $i++)
      {
        // start a row.
        print("<tr>\n");
        // show key name
        $keyName = $keyList[$i];
        print("<td>$keyName</td>");
        // for each language:
        for($j = 0; $j < $langListLength; $j++)
        {
          $pCurrLang  = $languageList[$j];
          // get key value.
          $value = $pCurrLang->translate($keyList[$i]);

          // show key value
          print("<td>$value</td>");

        }

        // Show option to edit.
        print("<td><a href=\"adm_langman.php?cid=10&kid=$keyName\">edit</a></td>\n");
        // end a row
        print("<tr>\n");
      }
      ?>
    </table>
    <?php
  }

  /*
    buildKeyEditor: Build a simple key editor form to edit all languages for a given keyname
      input :(String)$keyName: Name of the key for which to edit the languages.
  */
  public function buildKeyEditor($keyName)
  {
    $languageList   = $this->m_pLogicLanguageMan->getLanguageList();
    $langListLength = count($languageList);

    print("key name: $keyName<br>");

    print("<form name=\"edit\" action=\"adm_langman.php?cid=11&kid=$keyName\" method=\"post\">\n");
    ?>
      <table border="1">
        <tr>
          <td>Language: </td><td>Value: </td>
          <?php
          for($i = 0; $i < $langListLength; $i++)
          {
            $pLanguage    = $languageList[$i];
            $langName     = $pLanguage->getValue("name");
            $langDesc     = $pLanguage->getValue("description");
            $translation  = $pLanguage->translate($keyName);

            print("<tr>");
            print("<td>$langDesc</td>");
            print("<td><input type=\"\" name=\"$langName\" value=\"$translation\"></td>");
            print("</tr>");
          }
          ?>
        </tr>
      </table>
      <br />
      <input type="submit" name="submit" value="Update">
    </form>
    <?php
  }

  /*
    storeKeyEditor: Called after submitting the key editor form. gets the POST of all input fields for the languages and for each language stores the fields.
  */
  public function storeKeyEditor($keyName)
  {
    $languageList   = $this->m_pLogicLanguageMan->getLanguageList();
    $langListLength = count($languageList);

    for($i = 0; $i < $langListLength; $i++)
    {
      $pLanguage  = $languageList[$i];
      $langName   = $pLanguage->getValue("name");

      $value      = $_POST[$langName];

      $this->m_pLogicLanguageMan->updateTranslationByKey($pLanguage->getValue("id"), $keyName, $value, $this->getUserID());
    }
  }

  /*
    buildAddKeyForm: Form to add a new key for all languages.
  */
  public function buildAddKeyForm()
  {
    ?>
    <form name="newkey" action="adm_langman.php?cid=20" method="post">
      Keyname: <input type="text" name="keyname" value="" />
      <input type="submit" name="submit" value="Store" />
    </form>
    <?php
  }

  /*
    addKey: For a given key name, add a placeholder value in the form of the key name for each language.
  */
  public function addKey($keyName)
  {
    $languageList   = $this->m_pLogicLanguageMan->getLanguageList();
    $langListLength = count($languageList);

    for($i = 0; $i < $langListLength; $i++)
    {
      $pLanguage  = $languageList[$i];
      $this->m_pLogicLanguageMan->updateTranslationByKey($pLanguage->getValue("id"), $keyName, $keyName, $this->getUserID());
    }
  }

  public function buildAddLanguageForm()
  {
    $languageList   = $this->m_pLogicLanguageMan->getLanguageList();
    $langListLength = count($languageList);
    // print("CPageLanguageMan::buildAddLanguageForm: langListLength: $langListLength<br>\n");
    ?>
    Add new language: <br>
    <form name="newkey" action="adm_langman.php?cid=30" method="post">
      Language Name: <input type="text" name="name" value="" /><br />
      Language Description: <input type="text" name="description" value=""><br />
      Two letter abbreviation: <input type="text" name="abbr" value="" /><br />
      Copy from existing set:
      <select name="copyfrom">
        <?php
        for($i = 0; $i < $langListLength; $i++)
        {
          // print("CPageLanguageMan::buildAddLanguageForm: Adding language<br>\n");
          $pLanguage  = $languageList[$i];
          $langName   = $pLanguage->getValue("name");
          $pLangID    = $pLanguage->getValue("id");
          print("<option value=\"$pLangID\">$langName</option>\n");
        }
        ?>
      </select><br />
      <input type="submit" name="submit" value="Store" />
    </form>
    <?php
  }


  public function addLanguage()
  {
    // name
    $name = $_POST['name'];
    // description
    $description = $_POST['description'];
    // abbr
    $abbr = $_POST['abbr'];
    // copyfrom
    $copyfrom = $_POST['copyfrom'];

    $this->m_pLogicLanguageMan->createNewLanguage($abbr, $name, $description, $copyfrom, $this->getUserID());
  }
}
?>