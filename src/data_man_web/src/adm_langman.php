<?php
include("incl/const.php");
include("incl/class/page/CPageLanguageMan.php");


$page = new CPageLanguageMan($env);

$access = $page->getUserAccess();

$page->showHeader($access);
if(!$page->m_isSecure)
  $page->m_cid = 1000;


switch($page->m_cid)
{
  case 0:

    // point of entry. Show matrix of languages, keys and values.
    $page->buildAddLanguageForm();
      ?>
    <br /><br />
    <?php
    $page->buildLanguageTable();
    ?>
    <br /><br />
    <?php
    $page->buildAddKeyForm();
    break;  // end of case 0

  case 10:

    // Show edit form for given keyname
    // Get the keyname.
    $keyName = $_GET['kid'];

    // call function to show editor.
    $page->buildKeyEditor($keyName);

    break;  // end of case 10

  case 11:

    // Store submitted updates from case 10.
    $keyName = $_GET['kid'];

    $page->storeKeyEditor($keyName);
    ?>
    Value updated. Click <a href="adm_langman.php">here</a> to continue.<br />
    <?php
    break;  // end of case 11

  case 20:

    // Store the values for a new key:
    $keyName = $_POST['keyname'];
    $page->addKey($keyName);
    ?>
    New key created. Click <a href="adm_langman.php">here</a> to continue.<br />
    <?php
    break; // end of case 20.

  case 30:
    // store new language
    $page->addLanguage();
    ?>
    New language created. Click <a href="adm_langman.php">here</a> to continue.<br />
    <?php
    break;

  case 1000:

    // Access denied!
    $page->showAccessDenied();
    break; // end of case 1000

}


$page->showFooter();

?>