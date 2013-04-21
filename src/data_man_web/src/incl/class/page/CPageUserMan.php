<?php
include_once("CSecurePage.php");
include_once("incl/class/logic/CLogicUserMan.php");

class CPageUserMan extends CSecurePage
{
  private $m_pLogicUserMan;

  function __construct($envs)
  {
    $arrAccessReq           = array();
    $this->m_pLogicUserMan  = new CLogicUserMan($envs);

    array_push($arrAccessReq, "adm-userman");
    CSecurePage::__construct($envs, $arrAccessReq, true);
  }

  private function createSelectGroup($nSelected = 0)
  {
    $code       = "";
    $arrGroups  = $this->m_pLogicUserMan->getGroupList();
    $lenGroups  = count($arrGroups);
    $code      .= "<select name=\"groupID\">\n";
    $strSelected = "";
    if($nSelected == 0)
      $strSelected = "selected";

    $code .= "<option value=\"0\" $strSelected>Select a group</option>\n";
    for($i = 0; $i < $lenGroups; $i++)
    {

      $pGroup = $arrGroups[$i];
      $id     = $pGroup->getValue("id");
      $name   = $pGroup->getValue("name");

      if($id == $nSelected)
        $strSelected = "selected";
       else
        $strSelected = "";

      $code  .= "<option value=\"$id\" $strSelected>$name</option>\n";
    }

    $code .= "</select>\n";

    return $code;
  }

  public function createUserList()
  {
    $code = "";
    /*
    tblUser
    - id          INT PRIMAIRY KEY AUTO_INCREMENT
    - name        TEXT
    - username    TEXT
    - password    TEXT
    - email       TEXT
    - state       INT
    - type        INT
    - session     TEXT
    - languageID  INT
    - userGroup   TEXT
    - IP          TEXT
    - created     DATETIME
    - updated     DATETIME
    - createdBy   INT
    - updatedBy   INT
    */
    $arrUsers = $this->m_pLogicUserMan->getUserList("");
    $lenUsers = count($arrUsers);

    $code .= "<table border=\"1\">\n";
    $code .= "  <tr><td>id:</td><td>Name:</td><td>Username:</td><td>Email:</td><td>State:</td><td>Usergroup:</td><td>IP:</td><td>created:</td><td>updated:</td><td>Created by:</td><td>Updated by:</td><td>Options:</td></tr>\n";
    for($i = 0; $i < $lenUsers; $i++)
    {
      $pUser      = $arrUsers[$i];
      $id         = $pUser->getValue("id");
      $name       = $pUser->getValue("name");
      $username   = $pUser->getValue("username");
      $email      = $pUser->getValue("email");
      $state      = $pUser->getValue("state") == 0 ? "Inactive" : "Active";
      $usergroup  = $pUser->getValue("userGroup");
      $IP         = $pUser->getValue("IP");
      $created    = $pUser->getValue("created");
      $updated    = $pUser->getValue("updated");
      $createdBy  = $this->m_pLogicUserMan->getNameByID($pUser->getValue("createdBy"));
      $updatedBy  = $this->m_pLogicUserMan->getNameByID($pUser->getValue("updatedBy"));

      $code .= "<tr valign=\"top\">\n";
      $code .= "  <td>$id</td>\n";
      $code .= "  <td>$name &nbsp;</td>\n";
      $code .= "  <td>$username</td>\n";
      $code .= "  <td>$email &nbsp;</td>\n";
      $code .= "  <td>$state</td>\n";
      $code .= "  <td>$usergroup &nbsp;</td>\n";
      $code .= "  <td>$IP &nbsp;</td>\n";
      $code .= "  <td>$created</td>\n";
      $code .= "  <td>$updated</td>\n";
      $code .= "  <td>$createdBy &nbsp;</td>\n";
      $code .= "  <td>$updatedBy &nbsp;</td>\n";
      $code .= "  <td>\n";
      $code .= "    <a href=\"adm_users.php?cid=10&uid=$id\">edit</a>\n";
      $code .= "    -\n";
      $code .= "    <a href=\"adm_users.php?cid=20&uid=$id\">block</a>\n";
      $code .= "  </td>\n";
      $code .= "</tr>\n";
    }
    $code .= "</table>\n";

    return $code;
  }

  public function createNewUser()
  {
    $code = "";

    $code .= "New user: <br /><br />\n";
    $code .= "<form name=\"newuser\" action=\"adm_users.php?cid=31\" method=\"post\">\n";
    $code .= "  <table border=\"1\">\n";
    $code .= "    <tr><td>Name:</td><td><input type=\"text\" name=\"name\"></td></tr>\n";
    $code .= "    <tr><td>username:</td><td><input type=\"text\" name=\"username\"></td></tr>\n";
    $code .= "    <tr><td>Email:</td><td><input type=\"text\" name=\"email\"></td></tr>\n";
    $code .= "    <tr><td>usergroup:</td><td>".$this->createSelectGroup()."</td></tr>\n";
    $code .= "    <tr><td>Password:</td><td><input type=\"password\" name=\"password1\"></td></tr>\n";
    $code .= "    <tr><td>Repeat password:</td><td><input type=\"password\" name=\"password2\"></td></tr>\n";
    $code .= "  </table>\n";
    $code .= "  <input type=\"submit\" name=\"submit\" value=\"submit\" />\n";
    $code .= "</form>\n";

    return $code;
  }

  public function createEditUser()
  {
    $code       = "";
    $userID     = $_GET['uid'];
    $pUser      = new CDataUser($userID, false, $this->m_envs);
    $name       = $pUser->getValue("name");
    $username   = $pUser->getValue("username");
    $email      = $pUser->getValue("email");
    $userGroup  = $pUser->getValue("userGroup");
    $state      = $pUser->getValue("state");

    $code .= "Edit user: <br /><br />\n";
    $code .= "<form name=\"newuser\" action=\"adm_users.php?cid=11\" method=\"post\">\n";
    $code .= "  <table border=\"1\">\n";
    $code .= "    <tr><td>Name:</td><td><input type=\"text\" name=\"name\" value=\"$name\"></td></tr>\n";
    $code .= "    <tr><td>username:</td><td><input type=\"text\" name=\"username\" value=\"$username\"></td></tr>\n";
    $code .= "    <tr><td>Email:</td><td><input type=\"text\" name=\"email\" value=\"$email\"></td></tr>\n";
    $code .= "    <tr><td>usergroup:</td><td>$userGroup</td></tr>\n";
    $code .= "    <tr><td>Password:</td><td><input type=\"password\" name=\"password1\"></td></tr>\n";
    $code .= "    <tr><td>Repeast password:</td><td><input type=\"password\" name=\"password2\"></td></tr>\n";
    $code .= "  </table>\n";
    $code .= "  <input type=\"hidden\" name=\"userID\" value=\"$userID\" />\n";
    $code .= "  <input type=\"submit\" name=\"submit\" value=\"submit\" />\n";
    $code .= "</form>\n";

    $code .= "Add user to group:<br /><br />\n";
    $code .= "<form name=\"edit_usergroup\" action=\"adm_users.php?cid=40\" method=\"post\">\n";
    $code .= "  New usergroup: ".$this->createSelectGroup()."\n";
    $code .= "  <input type=\"hidden\" name=\"userID\" value=\"$userID\" >\n";
    $code .= "  <input type=\"submit\" name=\"submit\" value=\"Update group\" >\n";
    $code .= "</form>\n";

    $newState = $state == 0 ? 1 : 0;
    $strState = $newState == 1 ? "Activate" : "Deactivate";
    $code .= "<br /><br /><a href=\"adm_users.php?cid=20&uid=$userID&state=$newState\">$strState</a><br />\n";


    return $code;
 }

  public function createUserAccessElements()
  {
    $code = "";

    // Get the user's ID and object.
    $userID = $_GET['uid'];

    // get the user's Access Elements
    $arrUserElements = $this->m_pLogicUserMan->getUserAccessElements($userID);
    $lenUserElements = count($arrUserElements);

    // get all elements not connected to this user.
    $arrOtherElements = $this->m_pLogicUserMan->getUserAccessElements($userID, false);
    $lenOtherElements = count($arrOtherElements);

    // create 2 sets of elements, for connected and unconnected.
    $code .= "User access element management: <br />";
    $code .= "<table border=\"1\">\n";
    $code .= "  <tr>\n";
    $code .= "    <td valign=\"top\">\n";
    $code .= "      Elements connected to this user:<br />\n";
    for($i = 0; $i < $lenUserElements; $i++)
    {
      $pElement = $arrUserElements[$i];
      $id       = $pElement->getValue("id");
      $name     = $pElement->getValue("name");
      $code .= "  <a href=\"adm_users.php?cid=10&state=1&uid=$userID&eid=$id\">$name</a><br />\n";
    }
    $code .= "    </td>\n";
    $code .= "    <td valign=\"top\">\n";
    $code .= "      Elements NOT connected to this user:<br />\n";
    for($i = 0; $i < $lenOtherElements; $i++)
    {
      $pElement = $arrOtherElements[$i];
      $id       = $pElement->getValue("id");
      $name     = $pElement->getValue("name");
      $code .= "<a href=\"adm_users.php?cid=10&state=2&uid=$userID&eid=$id\">$name</a><br />\n";
    }

    $code .= "    </td>\n";
    $code .= "  </tr>\n";
    $code .= "</table>\n";

    return $code;
  }

  public function toggleUserAccessState()
  {
    if(isset($_GET['state']))
    {
      $userID     = $_GET['uid'];
      $elementID  = $_GET['eid'];
      $bConnect   = $_GET['state'] == 1 ? false : true;
      $createdBy  = $this->getUserID();

      return $this->m_pLogicUserMan->setUserAccessElement($userID, $elementID, $bConnect, $createdBy);
    }

    return true;
  }

  public function storeNewUser()
  {
    $name       = $_POST['name'];
    $username   = $_POST['username'];
    $email      = $_POST['email'];
    $userGroup  = $_POST['groupID'];
    $password1  = $_POST['password1'];
    $password2  = $_POST['password2'];
    $bError     = false;

    // Run some basic checks.
    if($password1 == "")
    {
      $this->addError("No password given.");
      $bError = true;
    }

    if($password1 != $password2)
    {
      $this->addError("No match between password and password verification.");
      $bError = true;
    }

    // All is OK?
    if(!$bError)
    {
      // store new user.
      $createdBy = $this->getUserID();

      // return success.
      return $this->m_pLogicUserMan->storeNewUser($name, $username, $email, $password1, $userGroup, $createdBy);
    }

    // return error.
    return false;
  }

  public function storeEditUser()
  {
    // get all values.
    $id         = $_POST['userID'];
    $name       = $_POST['name'];
    $username   = $_POST['username'];
    $email      = $_POST['email'];
    $password1  = $_POST['password1'];
    $password2  = $_POST['password2'];
    $bError     = false;


    // some basic tests.
    if($password1 != "")
    {
      if($password1 != $password2)
      {
        $this->addError("No match between password and password verification.");
        $bError = true;
      }
    }

    // If all is well:
    if(!$bError)
    {
      // call logic's 'store edit user.
      $updatedBy  = $this->getUserID();
      $bSuccess   = $this->m_pLogicUserMan->storeEditUser($id, $name, $username, $email, $password1, $updatedBy);
    }
    else
    {
      $bSuccess = false;
    }

    // return success.
    return $bSuccess;
  }

  public function storeUserState()
  {
    $userID     = $_GET['uid'];
    $state      = $_GET['state'];
    $updatedBy  = $this->getUserID();

    return $this->m_pLogicUserMan->storeUserState($userID, $state, $updatedBy);
  }

  public function storeNewUserGroup()
  {
    $userID     = $_POST['userID'];
    $groupID    = $_POST['groupID'];
    $updatedBy  = $this->getUserID();

    return $this->m_pLogicUserMan->storeNewUserGroup($userID, $groupID, $updatedBy);
  }

  public function listErrors()
  {
    $pageErrors   = $this->getErrors();
    $logicErrors  = $this->m_pLogicUserMan->getErrors();
    $strErrors    = "";

    for($i = 0; $i < count($pageErrors); $i++)
    {
      $error      = $pageErrors[$i];
      $strErrors .= "$error<br />";
    }
    for($i = 0; $i < count($logicErrors); $i++)
    {
      $error      = $logicErrors[$i];
      $strErrors .= "$error<br />";
    }

    return $strErrors;
  }
}