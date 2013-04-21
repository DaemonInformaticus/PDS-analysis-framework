<?php
include("incl/const.php");
include_once("incl/class/page/CPageGroupMan.php");

// $arrAccElem = array("page1");
$page       = new CPageGroupMan($env);

$access     = $page->getUserAccess();

$page->showHeader($access);
if(!$page->m_isSecure)
  $page->m_cid = 1000;

$actionResult = true;

if(isset($_GET['aid']) && $page->m_cid != 1000)
{
  $aid = $_GET['aid'];
  switch($aid)
  {
    case 10:
      // remove an access element from the given group.
      $actionResult = $page->removeElementFromGroup();

      break; // end of case 10

    case 20:
      // add an access element to the given group.
      $actionResult = $page->addElementToGroup();

      break; // end of case 20
  }
}

?>
<h2>Group management:</h2>
<?php

switch($page->m_cid)
{
  case 0:
    // point of entry
    // Show a list of groups.
    print("<a href=\"adm_groups.php?cid=40\">Manage access elements.</a><br /><br />");

    print($page->createNewGroup());
    print("<br><br>\n");
    print($page->createGroupsList());

    break; // end of case 0

  case 10:
    // store new group:
    if($page->storeNewGroup())
    {
      print("New Group stored successfully. Click <a href=\"adm_groups.php\">here</a> to continue.<br>\n");
    }
    else
    {
      $errors = $page->getErrors();
      print("New group".$errors."<br>");
    }

    break; // end of case 10

  case 20:
    // edit a given group
    if($actionResult == false)
      print($page->getLogicErrors());

    print($page->createEditGroup());
    print("<br />");
    print($page->createAddRemoveElements());
    break; // end of case 20

  case 21:
    // store an edited group
    if($page->storeEditGroup())
    {
      print("Group updated successfully. Click <a href=\"adm_groups.php\">here</a> to continue.<br>\n");
    }
    else
    {
      $errors = $page->getErrors();
      print("Update group:<br><br>".$errors."<br> Click <a href=\"adm_groups.php\">here</a> to continue.<br>");
    }
    break; // end of case 21.
  case 30:
    // remove a given group

    $page->deleteGroup();
    print("Group deleted successfully. Click <a href=\"adm_groups.php\">here</a> to continue.<br>\n");

    break; // end of case 30

  case 40:
    // Show interface to create new element.
    print($page->createNewAccessElement());

    // Show list of elements to edit.
    print($page->createElementList());

    break; // end of case 40

  case 41:
    // Store new Element.
    if($page->storeNewElement())
    {
      print("Element stored successfully. Click <a href=\"adm_groups.php?cid=40\">here</a> to continue.<br>\n");
    }
    else
    {
      $errors = $page->getLogicErrors();
      print("Error(s) storing access element:<br><br>".$errors."<br> Click <a href=\"adm_groups.php?cid=40\">here</a> to continue.<br>");
    }

    break; // end of case 41.

  case 50:
    // Edit element.
    print($page->createEditAccessElement());

    break; // end of case 50.

  case 51:
    // Store edit element.
    if($page->storeEditElement())
    {
      print("Element updated successfully. Click <a href=\"adm_groups.php?cid=40\">here</a> to continue.<br>\n");
    }
    else
    {
      $errors = $page->getLogicErrors();
      print("Error(s) updating access element:<br><br>".$errors."<br> Click <a href=\"adm_groups.php?cid=40\">here</a> to continue.<br>");
    }

    break; // end of case 51.

  case 1000:
    // Access denied!
    $page->showAccessDenied();
    break; // end of case 1000
}
$page->showFooter();
?>