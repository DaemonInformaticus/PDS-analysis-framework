<?php
include_once("CLogic.php");
include_once("incl/class/data/CDataUserSet.php");
include_once("incl/class/data/CDataUserGroupSet.php");
include_once("incl/class/data/CDataUToAccElement.php");
include_once("incl/class/data/CDataAccessElementSet.php");

class CLogicUserMan extends CLogic
{
  private $m_pDataUserSet;
  private $m_pDataGroupSet;

  function __construct($envs)
  {
    CLogic::__construct($envs);

    $this->m_pDataUserSet   = new CDataUserSet($envs);
    $this->m_pDataGroupSet  = new CDataUserGroupSet($envs);
  }

  public function getGroupList()
  {
    $arrList = $this->m_pDataGroupSet->getGroups("");

    return $arrList;
  }

  public function getUserList($filter)
  {
    $listUsers  = array();
    $arrUsers   = $this->m_pDataUserSet->getUserSet();
    $keyUsers   = array_keys($arrUsers);
    $lenUsers   = count($keyUsers);

    for($i = 0; $i < $lenUsers; $i++)
    {
      $key    = $keyUsers[$i];
      $pUser  = $arrUsers[$key];

      array_push($listUsers, $pUser);
    }

    return $listUsers;
  }

  public function storeNewUser($name, $username, $email, $password, $userGroup, $createdBy)
  {
    // create new user object.
    // check if user already exists.
    $pUser = new CDataUser(0, false, $this->m_envs);

    if($pUser->getIDByUsername($username))
    {
      $this->addError("Username already exists.");
      return false;
    }

    $created      = date("Y-m-d H:i");
    $password     = md5($this->m_envs['md5salt'].$password);
    $pUserGroup   = new CDataUserGroup($userGroup, false, $this->m_envs);
    $strUserGroup = $pUserGroup->getValue("name");
    // print("CLogicUserMan: userGroup $userGroup evaluates to $strUserGroup<br>\n");

    // write data to object.
    $pUser->setValue("name",        $name,                                true);
    $pUser->setValue("username",    $username,                            true);
    $pUser->setValue("password",    $password,                            true);
    $pUser->setValue("email",       $email,                               true);
    $pUser->setValue("state",       1,                                    true);
    $pUser->setValue("type",        1,                                    true);
    $pUser->setValue("languageID",  $this->m_envs['default_language_id'], true);
    $pUser->setValue("userGroup",   $strUserGroup,                        true);
    $pUser->setValue("created",     $created,                             true);
    $pUser->setValue("createdBy",   $createdBy,                           true);
    $pUser->setValue("updatedBy",   0,                                    true);

    // store object.
    $bResult = $pUser->insertValues();

    if(!$bResult)
    {
      $this->addError("Error storing new user object.");
    }

    // If adding the user went correctly:
    if($bResult)
    {
      // get the id.
      $userID = $pUser->getValue("id");

      // get the elements for the given group.
      $pUserGroup         = new CDataUserGroup($userGroup, false, $this->m_envs);
      $arrAccessElements  = $pUserGroup->getElements();
      $lenAccessElements  = count($arrAccessElements);

      // for each element:
      for($i = 0; $i < $lenAccessElements; $i++)
      {
        $pAccess      = $arrAccessElements[$i];
        $accessID     = $pAccess->getValue("id");

        // create new user to element object.
        $pUserAccess  = new CDataUToAccElement(0, false, $this->m_envs);

        // add data.
        $pUserAccess->setValue("userID",    $userID,    true);
        $pUserAccess->setValue("accessID",  $accessID,  true);
        $pUserAccess->setValue("created",   $created,   true);
        $pUserAccess->setValue("createdBy", $createdBy, true);
        $pUserAccess->setValue("updatedBy", 0,          true);

        // store object.
        $pUserAccess->insertValues();
      }
    }

    //return success.
    return $bResult;
  }

  public function storeEditUser($id, $name, $username, $email, $password, $updatedBy)
  {
    // get the user object.
    $pUser    = new CDataUser($id, true, $this->m_envs);

    $updated  = date("Y-m-d H:i");

    if($password != "")
    {
      $password = md5($this->m_envs['md5salt'].$password);
      $pUser->setValues("password", $password);
    }

    // update the values.
    $pUser->setValue("name", $name);
    $pUser->setValue("username", $username);
    $pUser->setValue("email", $email);
    $pUser->setValue("updated", $updated);
    $pUser->setValue("updatedBy", $updatedBy);


    // store the updates.
    $bSuccess = $pUser->updateValues();

    if(!$bSuccess)
    {
      $this->addError("Could not store the update for user $name.");
    }

    // return success.
    return $bSuccess;
  }

  public function storeUserState($userID, $state, $updatedBy)
  {
    $pUser = new CDataUser($userID, false, $this->m_envs);
    $updated = date("Y-m-d H:i");

    if($state == 0)
      $pUser->setValue("session", "");

    $pUser->setValue("state",      $state);
    $pUser->setValue("updated",    $updated);
    $pUser->setValue("updatedBy",  $updatedBy);

    $bResult = $pUser->updateValues();
    if(!$bResult)
    {
      $this->addError("Could not store the new state.");
    }

    return $bResult;
  }

  public function storeNewUserGroup($userID, $groupID, $updatedBy)
  {
    $pUser  = new CDataUser($userID, false, $this->m_envs);
    $pGroup = new CDataUserGroup($groupID, false, $this->m_envs);

    $pUser->m_access->clearAccess();

    $arrElements = $pGroup->getElements();
    $lenElements = count($arrElements);

    for($i = 0; $i < $lenElements; $i++)
    {
      $pElement     = $arrElements[$i];
      $elementName  = $pElement->getValue("name");

      $pUser->addAccessTo($elementName, $updatedBy);
    }

    $groupName = $pGroup->getValue("name");

    $pUser->setValue("userGroup", $groupName);
    $pUser->setValue("updated",   date("Y-m-d H:i"));
    $pUser->setValue("updatedBy", $updatedBy);

    $pUser->updateValues();

    return true;
  }

  public function setUserAccessElement($userID, $elementID, $bConnect, $createdBy)
  {
    // if bCOnnect = false (disconnect)
    if(!$bConnect)
    {
      // get the connection reference.
      $pUserToAccess  = new CDataUToAccElement(0, false, $this->m_envs);
      $bResult        = $pUserToAccess->getIDByReferences($userID, $elementID);

      // delete the row.
      if($bResult)
        $pUserToAccess->deleteRow();
    }
     // else (connect)
     else
    {
      // if the reference does not exist yet:
      $pUserToAccess  = new CDataUToAccElement(0, false, $this->m_envs);
      $bResult        = $pUserToAccess->getIDByReferences($userID, $elementID);
      if(!$bResult)
      {
        // create new reference object
        $pUserToAccess = new CDataUToAccElement(0, false, $this->m_envs);

        // enter data.
        $created = date("Y-m-d H:i");

        $pUserToAccess->setValue("userID",    $userID,    true);
        $pUserToAccess->setValue("accessID",  $elementID, true);
        $pUserToAccess->setValue("created",   $created,   true);
        $pUserToAccess->setValue("createdBy", $createdBy, true);

        // store object.
        $bResult = $pUserToAccess->insertValues();
      }
    }

    // return success.
    return $bResult;
  }

  public function getUserAccessElements($userID, $bInUser = true)
  {
    $arr          = array();
    $pUser        = new CDataUser($userID, false, $this->m_envs);
    $elementSet   = new CDataAccessElementSet($this->m_envs);
    $allElements  = $elementSet->getElements();
    $lenElements  = count($allElements);

    for($i = 0; $i < $lenElements; $i++)
    {
      $pElement   = $allElements[$i];
      $strElement = $pElement->getValue("name");

      if($pUser->m_access->hasAccessTo($strElement) == true && $bInUser == true)
        array_push($arr, $pElement);
       elseif($pUser->m_access->hasAccessTo($strElement) == false && $bInUser == false)
        array_push($arr, $pElement);
    }

    return $arr;
  }
}
?>