<?php
include_once("CData.php");
include_once("CDataAccess.php");

class CDataUser extends CData
{
  public  $m_access;

  function __construct($id, $bPrefetchAll, $envs)
  {
    // CData::__construct($id, $bPrefetchAll, $tblName, $envs)
    $this->m_access = NULL;

    $arr = array("id", "name", "username", "password", "email", "state", "session", "languageID", "userGroup", "IP", "lastLogin", "created", "updated", "createdBy", "updatedBy");

    if($id == 0)
      $bPrefetchAll = false;

    CData::__construct($id, $bPrefetchAll, "tblUser", $envs, $arr);
    if($this->isValid() == true )
    {
      $this->initUserAccess();
    }
  }

  /*
    initUserAccess: Initialize the collection of access elements for this user.
  */
  private function initUserAccess()
  {
    $this->m_access = new CDataUserToAccess($this->m_envs, $this->getValue("id"));
  }

  /*
    hasAccessTo: See if the user has access to a specific element.
      input:  (String)$elementName: Name of the element to check on.
      ouput:  (boolean) true: User has access.
                        false: User does not have access.
  */
  public function hasAccessTo($elementName)
  {
    if($this->m_access == NULL)
      initUserAccess();

    return $this->m_access->hasAccessTo($elementName);
  }

  /*
    revokeAccessTo: revoke access to a specified element.
      input: (String)$elementName: name of the element the user should no longer have access to.
  */
  public function revokeAccessTo($elementName)
  {
    if($this->m_access == NULL)
      initUserAccess();

    $this->m_access->revokeAccessTo($elementName);
  }

  public function addAccessTo($elementName, $createdBy)
  {
    $this->m_access->addAccessTo($elementName, $createdBy);
  }

  /*
    getIDByUsername: Get the id of a given user by a given username.
                      The id isn't returned as a value, but stored in the dataset.
                      It can then be read by getValue. Also 'prefetchAllValues' can then be called to get the details.
      input: (String) username: username of the Employee.
      output: (Boolean): true if user was found, false otherwise.

    This function is used during login, and maybe registration (to see if a username already exists?).
  */
  public function getIDByUsername($username)
  {
    $tblName  = $this->getTblName();
    $conn     = $this->getConnection();
    $username = addslashes($username);

    $this->setValue("id", 0, true);

    $sqlID = "SELECT id FROM $tblName WHERE username='$username';";
    $qryID = mysql_query($sqlID, $this->getConnection());
    if($rowID = mysql_fetch_row($qryID))
    {
      $this->setValue("id", $rowID[0], true);
      return true;
    }

    return false;
  }

  /*
    allowUserLogin: The user's column 'state' is a set of flaggs over which one can put a bitmask.
                    The mask '2' is a bit to verify that the user is indeed allowed to login.
                    This function sets bit '2' to $bAllow.

      input: (boolean)$bAllow: User is allowed to login. (or not).
  */
  public function allowUserLogin($bAllow)
  {
    $state = $this->getValue("state");
    // If the current state is 'allow login' and the new state is 'do not allow login'

    if(($state & 2) > 0 && $bAllow == false)
    {
      // remove the allow.
      $state -= 2;
      $this->setValue("state", $state);
    }

    elseif(($state & 2) == 0 && $bAllow == true)
    {
      // If the current state is 'do not allow login' and the new state is 'allow login'
      // add the allow.
      $state += 2;
      $this->setValue("state", $state);
    }
  }
}
?>