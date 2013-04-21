<?php
include_once("CPage.php");
include_once("incl/class/logic/CLogicRegister.php");

class CPageRegister extends CPage
{
  private $m_pLogicRegister;

  function __construct($envs)
  {
    CPage::__construct($envs, false);

    $this->m_pLogicRegister = new CLogicRegister($envs);
  }

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

  public function createNewUserForm()
  {
    $code = "";
    $code .= "User registration:<br><br>";
    $code .= "<form name=\"register\" action=\"register.php?cid=1\" method=\"post\">\n";
    $code .= "  <table border=\"1\">\n";
    $code .= "    <tr><td>Name:</td><td><input type=\"text\" name=\"name\" /></td></tr>\n";
    $code .= "    <tr><td>Username:</td><td><input type=\"text\" name=\"username\" /></td></tr>\n";
    $code .= "    <tr><td>Password:</td><td><input type=\"password\" name=\"password1\" /></td></tr>\n";
    $code .= "    <tr><td>Pepeat password:</td><td><input type=\"password\" name=\"password2\" /></td></tr>\n";
    $code .= "    <tr><td>Email:</td><td><input type=\"text\" name=\"email1\" /></td></tr>\n";
    $code .= "    <tr><td>Repeat email:</td><td><input type=\"text\" name=\"email2\" /></td></tr>\n";
    $code .= "  </table>\n";
    $code .= "  <input type=\"submit\" name=\"submit\" value=\"Register\">\n";
    $code .= "</form>\n";

    return $code;
  }

  /*
    storeNewUser: Called on submission of registration form.
  */
  public function storeNewUser()
  {
    // prepare variables from post values.
    $name       = $_POST['name'];
    $username   = $_POST['username'];
    $password1  = $_POST['password1'];
    $password2  = $_POST['password2'];
    $email1     = $_POST['email1'];
    $email2     = $_POST['email2'];
    $state      = $this->m_envs['register_state'];
    $type       = 1;
    $userGroup  = "user";
    $ip         = $_SERVER['REMOTE_ADDR'];

    // create an array we can write to logic.
    $arrInput['name']       = $name;
    $arrInput['username']   = $username;
    $arrInput['password1']  = $password1;
    $arrInput['password2']  = $password2;
    $arrInput['email1']     = $email1;
    $arrInput['email2']     = $email2;
    $arrInput['state']      = $state;
    $arrInput['type']       = $type;
    $arrInput['userGroup']  = $userGroup;
    $arrInput['ip']         = "$ip";
    $arrInput['createdBy']  = 0;

    // write the array to logic.
    $this->m_pLogicRegister->m_arrInputFields = $arrInput;

    // call createNewUser and return whether result value > 0.
    return $this->m_pLogicRegister->createNewUser() > 0;
  }

  /*
    getErrorList: read the array of errors from the logic layer and create a result string we can show.
  */
  public function getErrorList()
  {
    $strErrors = "";
    $arrErrors = $this->m_pLogicRegister->m_arrErrorLines;
    $lenErrors = count($arrErrors);

    for($i = 0; $i < $lenErrors; $i++)
    {
      $strErrors .= $arrErrors[$i]."<br>";
    }

    return $strErrors;
  }
}
?>