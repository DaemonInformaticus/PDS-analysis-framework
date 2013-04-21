<?php
include("incl/const.php");
include_once("incl/class/page/CPageRegister.php");

// $arrAccElem = array("page1");
$page       = new CPageRegister($env);

$page->showHeader(0);

switch($page->m_cid)
{
  case 0:
    // point of entry
    // Show registration form:
    print($page->createNewUserForm());

    break; // end of case 0

  case 1:
    // store new user:
    if($page->storeNewUser())
    {
      print("New user stored successfully.<br>Click <a href=\"index.php\">here</a> to continue.<br>\n");
    }
     else
    {
      $strErrors = $page->getErrorList();
      print("Error storing new user.The following problems were found: <br>$strErrors<br><br>Click <a href=\"register.php\">here</a> to continue.<br>\n");
    }
}

$page->showFooter();
?>