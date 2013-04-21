<?php
include_once("CSecurePage.php");
include_once("incl/class/logic/CLogicGroupMan.php");

class CPageGroupMan extends CSecurePage
{
  private $m_pLogicGroupMan;

  function __construct($envs)
  {
    $arrAccessReq     = array();
    $this->m_pLogicGroupMan = new CLogicGroupMan($envs);

    array_push($arrAccessReq, "adm-groupman");
    CSecurePage::__construct($envs, $arrAccessReq, true);
  }

  public function createGroupsList()
  {
    $code       = "";
    $arrGroups  = $this->m_pLogicGroupMan->getGroupList("");
    $lenGroups  = count($arrGroups);
    $code      .= "Groups currently defined:<br><br>\n";
    $code      .= "<table border=\"1\">\n";
    $code      .= "  <tr><td>id:</td><td>Name:</td><td>Created:</td><td>Updated:</td><td>Created by:</td><td>Updated by:</td><td>Options:</td></tr>\n";

    for($i = 0; $i < $lenGroups; $i++)
    {
      $pGroup     = $arrGroups[$i];
      $id         = $pGroup->getValue("id");
      $name       = $pGroup->getValue("name");
      $created    = $pGroup->getValue("created");
      $updated    = $pGroup->getValue("updated");
      $createdBy  = $pGroup->getValue("createdBy");
      $updatedBy  = $pGroup->getValue("updatedBy");

      $pCreatedBy = new CDataUser($createdBy, false, $this->m_envs);
      $pUpdatedBy = new CDataUser($updatedBy, false, $this->m_envs);
      $createdBy  = $pCreatedBy->getValue("name");
      $updatedBy  = $pUpdatedBy->getValue("name");

      $code .= "<tr>";
      $code .= "  <td>$id</td>";
      $code .= "  <td>$name</td>";
      $code .= "  <td>$created</td>";
      $code .= "  <td>$updated</td>";
      $code .= "  <td>$createdBy &nbsp;</td>";
      $code .= "  <td>$updatedBy &nbsp;</td>";
      $code .= "  <td>";
      $code .= "    <a href=\"adm_groups.php?cid=20&grid=$id\">Edit</a>";
      $code .= "     - ";
      $code .= "    <a href=\"adm_groups.php?cid=30&grid=$id\">Remove</a>";
      $code .= "  </td>";
      $code .= "</tr>";
    }

    $code .= "</table>\n";

    return $code;
  }

  public function createNewGroup()
  {
    $code = "";

    $code .= "New Group: <br>\n";
    $code .= "<form name=\"newgroup\" action=\"adm_groups.php?cid=10\" method=\"post\">\n";
    $code .= "  Group name: <input type=\"text\" name=\"name\">\n";
    $code .= "  <input type=\"submit\" name=\"submit\" value=\"submit\">\n";
    $code .= "</form>\n";

    return $code;
  }

  public function createEditGroup()
  {
    $code   = "";
    $id     = $_GET['grid'];
    $pGroup = new CDataUserGroup($id, false, $this->m_envs);
    $name   = $pGroup->getValue("name");

    $code .= "Edit Group: <br>\n";
    $code .= "<form name=\"editgroup\" action=\"adm_groups.php?cid=21&grid=$id\" method=\"post\">\n";
    $code .= "  Name: <input type=\"text\" name=\"name\" value=\"$name\" />\n";
    $code .= "  <input type=\"submit\" name=\"submit\" value=\"Update\" />\n";
    $code .= "</form>\n";

    return $code;
  }

  public function createAddRemoveElements()
  {
    $code                 = "";
    $groupID              = $_GET['grid'];
    $arrGroupElements     = $this->m_pLogicGroupMan->getElementsByGroup($groupID);
    $arrRemainingElements = $this->m_pLogicGroupMan->getElementsOutsideGroup($groupID);
    $lenGroupElements     = count($arrGroupElements);
    $lenRemainingElements = count($arrRemainingElements);

    $code .= "<table border=\"1\" width=\"100%\">\n";
    $code .= "  <tr>\n";
    $code .= "    <td valign=\"top\">\n";
    $code .= "      Elements in the set (click element to remove it):<br /><br />\n";
    for($i = 0; $i < $lenGroupElements; $i++)
    {
      $pElement = $arrGroupElements[$i];
      $elementID = $pElement->getValue("id");
      $elementName = $pElement->getValue("name");

      $code .= "<a href=\"adm_groups.php?cid=20&aid=10&grid=$groupID&eid=$elementID\">$elementName</a><br />\n";
    }
    $code .= "    </td>\n";
    $code .= "    <td valign=\"top\">\n";
    $code .= "      Elements not in the set (click element to add it):<br /><br />\n";
    for($i = 0; $i < $lenRemainingElements; $i++)
    {
      $pElement = $arrRemainingElements[$i];
      $elementID = $pElement->getValue("id");
      $elementName = $pElement->getValue("name");

      $code .= "<a href=\"adm_groups.php?cid=20&aid=20&grid=$groupID&eid=$elementID\">$elementName</a><br />\n";
    }
    $code .= "    </td>\n";
    $code .= "  </tr>\n";
    $code .= "</table>\n";

    return $code;
  }

  public function createNewAccessElement()
  {
    $code  = "";

    $code .= "Create new element:\n";
    $code .= "<form name=\"newElement\" action=\"adm_groups.php?cid=41\" method=\"post\">\n";
    $code .= "  <table border=\"1\">\n";
    $code .= "    <tr><td>Name:</td><td><input type=\"text\" name=\"name\" size=\"46\" /></td></tr>\n";
    $code .= "    <tr><td>Description:</td><td><textarea name=\"description\" rows=\"5\" cols=\"40\"></textarea></td></tr>\n";
    $code .= "  </table>\n";
    $code .= "  <input type=\"submit\" name=\"submit\" value=\"Create\">\n";
    $code .= "</form>\n";

    return $code;
  }

  public function createEditAccessElement()
  {
    $code         = "";
    $elementID    = $_GET['eid'];
    $pElement     = new CDataAccessElement($elementID, false, $this->m_envs);
    $name         = $pElement->getValue("name");
    $description  = $pElement->getValue("description");

    $code .= "Create new element:\n";
    $code .= "<form name=\"newElement\" action=\"adm_groups.php?cid=51\" method=\"post\">\n";
    $code .= "  <table border=\"1\">\n";
    $code .= "    <tr><td>Name:</td><td><input type=\"text\" name=\"name\" value=\"$name\" size=\"46\" /></td></tr>\n";
    $code .= "    <tr><td>Description:</td><td><textarea name=\"description\" rows=\"5\" cols=\"40\">$description</textarea></td></tr>\n";
    $code .= "  </table>\n";
    $code .= "  <input type=\"hidden\" name=\"elementID\" value=\"$elementID\">\n";
    $code .= "  <input type=\"submit\" name=\"submit\" value=\"Create\">\n";
    $code .= "</form>\n";

    return $code;
  }

  public function createElementList()
  {
    $code = "";

    // Get list of elements
    $arrElements = $this->m_pLogicGroupMan->getAllElements();
    $lenElements = count($arrElements);

    $code .= "<table border=\"1\">\n";
    $code .= "  <tr><td>id:</td><td>Name:</td><td>Description:</td><td>Edit:</td></tr>\n";

    // For each element:
    for($i = 0; $i < $lenElements; $i++)
    {
      // Add to table, with the option to edit.
      $pElement = $arrElements[$i];
      $id = $pElement->getValue("id");
      $name = $pElement->getValue("name");
      $description = $pElement->getValue("description");
      $code .= "<tr>\n";
      $code .= "  <td>$id</td>\n";
      $code .= "  <td>$name</td>\n";
      $code .= "  <td>$description</td>\n";
      $code .= "  <td><a href=\"adm_groups.php?cid=50&eid=$id\">Edit</a></td>\n";
      $code .= "</tr>\n";
    }

    $code .= "</table>\n";

    return $code;
  }


  public function storeNewGroup()
  {
    $name       = $_POST['name'];
    $createdBy  = $this->getUserID();

    return $this->m_pLogicGroupMan->storeNewGroup($name, $createdBy);
  }

  public function storeEditGroup()
  {
    $id         = $_GET['grid'];
    $name       = $_POST['name'];
    $updatedBy  = $this->getUserID();

    return $this->m_pLogicGroupMan->storeEditGroup($id, $name, $updatedBy);
  }

  public function deleteGroup()
  {
    $groupID = $_GET['grid'];

    return $this->m_pLogicGroupMan->removeGroup($groupID);
  }

  public function addElementToGroup()
  {
    $elementID  = $_GET['eid'];
    $groupID    = $_GET['grid'];
    $createdBy  = $this->getUserID();

    return $this->m_pLogicGroupMan->addElementToGroup($elementID, $groupID, $createdBy);
  }

  public function removeElementFromGroup()
  {
    $elementID  = $_GET['eid'];
    $groupID    = $_GET['grid'];

    return $this->m_pLogicGroupMan->removeElementFromGroup($elementID, $groupID);
  }

  public function storeNewElement()
  {
    $name         = $_POST['name'];
    $description  = $_POST['description'];
    $createdBy    = $this->getUserID();

    return $this->m_pLogicGroupMan->storeNewAccessElement($name, $description, $createdBy);
  }

  public function storeEditElement()
  {
    $id           = $_POST['elementID'];
    $name         = $_POST['name'];
    $description  = $_POST['description'];
    $updatedBy    = $this->getUserID();

    return $this->m_pLogicGroupMan->storeEditElement($id, $name, $description, $updatedBy);
  }

  public function getLogicErrors()
  {
    $arrErrors  = $this->m_pLogicGroupMan->getErrors();
    $lenErrors  = count($arrErrors);
    $code       = "";
    $code      .= "Errors: <br>\n";
    for($i = 0; $i < $lenErrors; $i++)
    {
      $strError   = $arrErrors[$i];
      $code      .= "$strError<br>\n";
    }

    return $code;
  }
}
?>