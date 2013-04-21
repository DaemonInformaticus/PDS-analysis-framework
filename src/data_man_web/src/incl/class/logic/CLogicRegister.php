<?php
include_once("incl/class/data/CDataUser.php");
include_once("incl/class/data/CDataUserSet.php");
include_once("incl/class/data/CDataGroup.php");


class CLogicRegister
{
  private   $m_userSet;         // Reference to CDataUserSet object
  private   $m_newUser;         // Reference to new CDataUser object.
  private   $m_envs;            // Website env-variables
  public    $m_dataIsValid;     // object scope boolean to verify if a given input data set is valid.
  private   $m_md5Salt;         // Salt value for md5 of the password.

  public    $m_arrInputFields;  // Dataset that is passed on by the registration page.
  public    $m_arrErrorLines;   // List of all errors.
  public    $m_arrErrorFields;  // Key enumerated errorfields.

  function __construct($envs)
  {
    $this->m_envs           = $envs;
    $this->m_md5Salt        = $envs['md5salt'];
    $this->m_arrInputFields = array();
    $this->m_arrErrorLines  = array();
    $this->m_dataIsValid    = true;
  }

  /*
    checkUserName: See if a given username already exists.
      output: 0: Invalid value for 'username' in the object property 'm_arrInputFields'.
              1: User with this name already exists.
              2: OK.
  */
  private function checkUserName()
  {
    if(!isset($this->m_arrInputFields['username']))
      return 0;

    if($this->m_arrInputFields['username'] == "")
      return 0;

    $username = $this->m_arrInputFields['username'];
    $pUser    = new CDataUser(0, false, $this->m_envs);
    $bFound   = $pUser->getIDByUsername($username);

    if($bFound)
      return 1;

    return 2;
  }


  /*
    checkData: Check all fields in the arrInputFields array to see if nothing is missing.
        Is something is wrong, m_dataIsValid is set to false and 'createNewUser will not create a new user.
  */
  private function checkData()
  {
    $this->m_arrErrorFields['name']       = 0;
    $this->m_arrErrorFields['username']   = 0;
    $this->m_arrErrorFields['password1']  = 0;
    $this->m_arrErrorFields['password2']  = 0;
    $this->m_arrErrorFields['email1']     = 0;
    $this->m_arrErrorFields['email2']     = 0;
    $this->m_arrErrorFields['state']      = 0;
    $this->m_arrErrorFields['type']       = 0;
    $this->m_arrErrorFields['createdBy']  = 0;

    if(empty($this->m_arrInputFields['name']))
    {
      $this->m_arrErrorFields['name'] = 1;
      array_push($this->m_arrErrorLines, "Name field is empty.");
    }

    /*if(empty($this->m_arrInputFields['username']))
    {
      $this->m_arrErrorFields['username'] = 1;
      array_push($this->m_arrErrorLines, "Username field is empty.");
    }*/

    // check out stuff about the username.
    $retUsername = $this->checkUserName($this->m_arrInputFields['username']);
    if($retUsername == 0)
    {
      $this->m_arrErrorFields['username'] = 1;
      array_push($this->m_arrErrorLines, "Username field is empty.");
    }
    if($retUsername == 1)
    {
      $this->m_arrErrorFields['username'] = 1;
      array_push($this->m_arrErrorLines, "Username already exists.");
    }

    // retUsername == 2? all is well with the username.


    if(empty($this->m_arrInputFields['password1']))
    {
      $this->m_arrErrorFields['password1'] = 1;
      array_push($this->m_arrErrorLines, "Password field is empty.");
    }

    if(empty($this->m_arrInputFields['password2']))
    {
      $this->m_arrErrorFields['password2'] = 1;
      array_push($this->m_arrErrorLines, "Repeat password field is empty.");
    }

    if(($this->m_arrErrorFields['password1'] == 0)
        && ($this->m_arrErrorFields['password2'] == 0)
        && ($this->m_arrInputFields['password1'] != $this->m_arrInputFields['password2']))
    {
      $this->m_arrErrorFields['password1']   = 1;
      $this->m_arrErrorFields['password2']  = 1;

      array_push($this->m_arrErrorLines, "Password fields do not match.");
    }

    if(empty($this->m_arrInputFields['email1']))
    {
      $this->m_arrErrorFields['email1'] = 1;
      array_push($this->m_arrErrorLines, "Email field is empty.");
    }

    if(empty($this->m_arrInputFields['email2']))
    {
      $this->m_arrErrorFields['email2'] = 1;
      array_push($this->m_arrErrorLines, "Repeat email field is empty.");
    }

    if(!isset($this->m_arrInputFields['state']))
    {
      $this->m_arrInputFields['state'] = 0;
    }

    if(!isset($this->m_arrInputFields['type']))
    {
      $this->m_arrInputFields['type'] = 0;
    }

    if(!isset($this->m_arrInputFields['createdBy']))
    {
      $this->m_arrInputFields['createdBy'] = 0;
    }

    print("CLogicRegister::checkData: Count of errors: ".count($this->m_arrErrorLines)."<br>\n");

    if(count($this->m_arrErrorLines) > 0)
      $this->m_dataIsValid = false;

    print("CLogicRegister::checkData: Count of errors: passing OK<br>\n");
    return true;
  }

  /*
    createNewUser: Create a new database user.
        See if all mandatory data fields are present and valid.
        Create new user object.
        Store user object.
  */
  public function createNewUser()
  {
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

    // Check value validity
    $this->checkData();

    // Create and store new User object.
    if($this->m_dataIsValid == true)
    {
      // Prepare values:
      $name             = $this->m_arrInputFields['name'];
      $username         = $this->m_arrInputFields['username'];
      $password         = md5($this->m_md5Salt.$this->m_arrInputFields['password1']);
      $email            = $this->m_arrInputFields['email1'];
      $type             = $this->m_arrInputFields['type'];
      $state            = $this->m_arrInputFields['state'];
      $languageID       = $this->m_envs['default_language_id'];
      $userGroup        = $this->m_arrInputFields['userGroup'];
      $IP               = $this->m_arrInputFields['ip'];
      $createdBy        = $this->m_arrInputFields['createdBy'];
      $updatedBy        = 0;

      $this->m_newUser  = new CDataUser(0, false, $this->m_envs);

      $this->m_newUser->setValue("name",        $name,              true);
      $this->m_newUser->setValue("username",    $username,          true);
      $this->m_newUser->setValue("password",    $password,          true);
      $this->m_newUser->setValue("email",       $email,             true);
      $this->m_newUser->setValue("state",       $state,             true);
      $this->m_newUser->setValue("languageID",  $languageID,        true);
      $this->m_newUser->setValue("userGroup",   $userGroup,         true);
      $this->m_newUser->setValue("IP",          $IP,                true);
      $this->m_newUser->setValue("created",     date("Y-m-d H:i"),  true);
      $this->m_newUser->setValue("createdBy",   $createdBy,         true);
      $this->m_newUser->setValue("updatedBy",   $createdBy,         true);

      // if all went well: User now has an ID. Return it.
      if($this->m_newUser->insertValues() == true)
      {
        $this->userToGroup();

        return $this->m_newUser->getValue("id");
      }
    }

    // return ID = 0. Something went wrong.
    return 0;
  }

  /*
    userToGroup: Link a user to a given group ID.
      output: (boolean) true:   User successfully linked to a group and all its access elements.
                        false:  Something went wrong linking the user to the group.
  */
  private function userToGroup()
  {
    if(!isset($this->m_arrInputFields['groupID']))
      return false;

    if(!is_numeric($this->m_arrInputFields['groupID']))
      return false;

    if($this->m_arrInputFields['groupID'] == 0)
      return true;

    $groupID    = $this->m_arrInputFields['groupID'];
    $createdBy  = $this->m_arrInputFields['createdBy'];

    $data       = new CData(0, false, "", $this->m_envs);
    $dataGroup  = new CDataGroup($groupID, $data->getConnection());

    for($i = 0; $i < count($dataGroup->m_elements); $i++)
    {
      $element      = $dataGroup->m_elements[$i];
      $elementName  = $element->getValue("name");

      $this->m_newUser->addAccessTo($elementName, $createdBy);
    }

    return true;
  }
}
?>