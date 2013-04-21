<?php
include_once("incl/class/data/CDataUser.php");

class CLogicIndex
{

  private $m_envs;  // Array with website environment variables.
  private $m_user;  // CData user object of the currently running session

  function __construct($envs)
  {
    $this->m_envs = $envs;

    // Check user session.
    if(!$this->loadUser())
      $this->m_user = NULL;

    /*if($this->m_user != NULL)
      print("CLogicIndex::__construct: user found!");*/
  }


  /*
    loadUser: Check to see if there's a session cookie containing an id and session.
      output: (boolean) true:   Session found and verified.
                        false:  No (valid) session found
  */
  private function loadUser()
  {
    // print("CLogicIndex::loadUser: user found!");
    // Get a session cookie and dissect it for id and session value.
    if(!isset($_COOKIE['session']))
      return false;

    $cki    = $_COOKIE['session'];
    $arrCki = explode(";", $cki);

    if(count($arrCki) < 2)
      return false;

    $id       = $arrCki[0];
    $session  = $arrCki[1];
    // print("CLogicIndex::loadUser: id: $id<br>\n");
    // print("CLogicIndex::loadUser: session: $session<br>\n");

    // validate id and session values.
    if($id == 0 || $id == "")
    {
      // print("CLogicIndex::loadUser: id Error!<br>\n");
      return false;
    }

    if($session == "")
    {
      // print("CLogicIndex::loadUser: session Error empty!<br>\n");
      return false;
    }

    // Get a user by ID from the database.
    $this->m_user = new CDataUser($id, false, $this->m_envs);

    // compare session in the cookie with the user object:
    $userSession = $this->m_user->getValue("session");
    // print("CLogicIndex::loadUser: userSession: $userSession<br>\n");
    if($session != $userSession)
      return false;

    // User is valid and the session is active. return success.
    return true;
  }

  /*
    getUserName: If there's a user, return its name, otherwise return "".
      output: (String)User's name field.
  */
  public function getUserName()
  {
    if($this->m_user == NULL)
      return "";

    return $this->m_user->getValue("Name");
  }

  /*
    getUserAccess: get the user's access field.
      output: (CDataUserToAccess) reference to a user's access properties.
  */
  public function getUserAccess()
  {
    if($this->m_user == NULL)
      return NULL;

    return $this->m_user->m_access;
  }

  public function getUser() { return $this->m_user; }
}
?>