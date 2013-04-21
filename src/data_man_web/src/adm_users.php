<?php
include("incl/const.php");
include_once("incl/class/page/CPageUserMan.php");

// $arrAccElem = array("page1");
$page       = new CPageUserMan($env);

$access     = $page->getUserAccess();

$page->showHeader($access);
if(!$page->m_isSecure)
  $page->m_cid = 1000;

?>
<h2>User management:</h2>
<?php

switch($page->m_cid)
{
  case 0:
    // point of entry
    print("<a href=\"adm_users.php?cid=30\">Add new user</a><br /><br />");
    // Show list of existing users.
    print($page->createUserList());

    break; // end of case 0

  case 10:
    // edit user:
    $page->toggleUserAccessState();
    print($page->createEditUser());
    print("<br />");
    print($page->createUserAccessElements());

    break; // end of case 10

  case 11:
    // store edit user.
    if($page->storeEditUser())
    {
      print("User updated successfully. Click <a href=\"adm_users.php\">here</a> to continue.");
    }
    else
    {
      $strErrors = $page->listErrors();
      print("Error updating user. <br />$strErrors<br />Click <a href=\"adm_users.php\">here</a> to continue.");
    }

    break; // end of case 11

  case 20:
    // toggle user state:
    if($page->storeUserState())
    {
      print("User state updated successfully. Click <a href=\"adm_users.php\">here</a> to continue.");
    }
    else
    {
      $strErrors = $page->listErrors();
      print("Error updating user state. <br />$strErrors<br />Click <a href=\"adm_users.php\">here</a> to continue.");
    }

    break; // end of case 20

  case 30:
    // new user:
    print($page->createNewUser());
    break; // end of case 30

  case 31:
    // store new user
    if($page->storeNewUser())
    {
      print("New user stored successfully. Click <a href=\"adm_users.php\">here</a> to continue.");
    }
    else
    {
      $strErrors = $page->listErrors();
      print("Error storing new user. <br />$strErrors<br />Click <a href=\"adm_users.php\">here</a> to continue.");
    }
    break; // end of case 31.

  case 40:
    // store new usergroup:
    if($page->storeNewUserGroup())
    {
      print("New usergroup stored successfully. Click <a href=\"adm_users.php\">here</a> to continue.");
    }
    else
    {
      $strErrors = $page->listErrors();
      print("Error storing new usergroup. <br />$strErrors<br />Click <a href=\"adm_users.php\">here</a> to continue.");
    }

    break; // end of case 40

  case 1000:
    // Access denied!
    $page->showAccessDenied();
    break; // end of case 1000
}
$page->showFooter();
?>