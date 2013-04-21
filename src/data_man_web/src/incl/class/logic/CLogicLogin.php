<?php
include_once("incl/class/data/CDataUser.php");

class CLogicLogin
{
  private $m_envs;
  private $m_md5Salt;
  public  $m_error;

  function __construct($envs)
  {
    $this->m_envs    = $envs;
    $this->m_md5Salt = $envs['md5salt'];
    $this->m_error   = "";
  }

  /*
    authenticate: Authenticate username and password against the database.
      input:  (String)$name: username.
              (String)$pass: (flattext)password.

      output: (CDataUser): user that logged in.
  */
  public function authenticate($name, $pass)
  {
    // Get a salted md5 of the pass.
    // print("name: $name pass: $pass<br>");
    $pass = md5($this->m_md5Salt.$pass);

    // Get a user object by the given name.
    $user = new CDataUser(0, false, $this->m_envs);
    $user->setValue("session", "", true);
    // print("md5 of pass: $pass<br>");


    if(!$user->getIDByUsername($name))
    {
      $this->m_error = "Could not find user.";
      return NULL;
    }

    $userPass = $user->getValue("password");
    if($userPass != $pass)
    {
      $this->m_error = "Password doesn't match.";
      return NULL;
    }

    $userID   = $user->getValue("id");
    $session  = $this->createSessionID();
    $IP       = $_SERVER['REMOTE_ADDR'];

    // print("updating user session and updating values... ");
    $user->setValue("session",  $session);
    $user->setValue("IP",       $IP);
    $elementsUpdated = $user->updateValues();
    $this->createCookie($userID, $session);

    return $user;
  }

  /*
    createSessionID: create a unique 32 character hex value to use as a session id.
      output: (String): 32 character Hex value based on the unix timestamp.
  */
  private function createSessionID()
  {
    return md5(time());
  }

  /*
    createCookie: call the function setcookie with the given id and session, for a domain-wide cookie that expires at the end of a session.
      input:  (int)id: id of the user.
              (String)session: string value representation of the session.
      output: (boolean) true:  setting cookie was a succes.
                        false: something went wrong sending the cookie. probably the headers already sent?
  */
  private function createCookie($id, $session)
  {
    return setcookie("session", "$id;$session", 0, "/");
  }

  /*
    logout: End the session by pressing 'logout'. Rewrites the session cookie.
      output: (boolean) true:  logout successfull. Cookie has been set to new values.
                        false: Something went wrong writing the new values to the cookie. :(
  */
  public function logout()
  {
    return $this->createCookie(0, 0);
  }
}
?>