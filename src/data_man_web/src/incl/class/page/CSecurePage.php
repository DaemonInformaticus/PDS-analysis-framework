<?php

include_once("CPage.php");
include_once("incl/class/data/CDataUser.php");

class CSecurePage extends CPage
{
  private $m_user;          // CDataUser object of user.

  private $m_arrAccessReq;  // Array of access elements required to access this page.
  public  $m_isSecure;      // Boolean showing if page is secure.
  // private $m_session;

  function __construct($envs, $arrAccessReq = array(), $bLoadLanguage = true)
  {
    CPage::__construct($envs, $bLoadLanguage);

    $this->m_isSecure     = false;
    $this->m_arrAccessReq = $arrAccessReq;

    // See if there's a valid session.
    $this->resolveSession();

    // if session was resolved successfully, set isSecure to true
    if($this->m_user != NULL && $this->checkPageAccess())
      $this->m_isSecure = true;
  }

  /*
    getUserAccess: Return user->m_access object.
    Output: On no user object: return 0
            On success: return user->m_access
  */
  public function getUserAccess()
  {
    if($this->m_user != NULL)
      return $this->m_user->m_access;

    return NULL;
  }

  protected function getUserID()
  {
    if($this->m_user != NULL)
      return $this->m_user->getValue("id");

    return 0;

  }

  /*
    resolveSession: Get the session cookie and resolve the session.
                    On Success, $this->m_user contains a user object.
  */
  private function resolveSession()
  {
    if(!isset($_COOKIE['session']))
      return;

    // print("I found a cookie! ".$_COOKIE['session']."<br>");
    $id       = $this->getSessionValueByIndex($_COOKIE['session'], 0);
    $session  = $this->getSessionValueByIndex($_COOKIE['session'], 1);

    $user         = new CDataUser($id, false, $this->m_envs);
    $userSession  = $user->getValue("session");

    // print("id: $id<br>session: $session<br><br>");
    // print("user session according to database: $userSession<br>");

    if($userSession == "")
      return;

    if($session != $userSession)
      return;

    $this->m_user = $user;
  }

  /*
    getSessionValueByIndex: Takes a value and index. Explodes the value using ';' as a delimiter. returns the element by index.
    Input:
      $cki: Value to explode.
      $index: index of element to return (0-based).
    Output:
      on empty value, or fewer elements than the index, return "";
      on success: element indicated by index.
  */
  private function getSessionValueByIndex($cki, $index)
  {
    $arrCki = explode(";", $cki);
    if($index >= count($arrCki))
      return "";

    return $arrCki[$index];
  }

  /*
    check page access: Check whether the user has the access elements for this page.
    Output:
      false: User misses one or more access keys.
      true: User has access to the page.
  */
  private function checkPageAccess()
  {
    // If there is no user: return false;
    if($this->m_user == NULL)
      return false;

    // If there are no elements in the pageAccess array: return true;
    // go through all the required access elements
    for($i = 0; $i < count($this->m_arrAccessReq); $i++)
    {
      // get the element at i
      $accessName = $this->m_arrAccessReq[$i];

      // See if the user has access.
      if(!$this->m_user->m_access->hasAccessTo($accessName))
        return false;
    }

    // The user passed all specified requirements: return true
    return true;
  }

  /*
    checkUserAccess: Check to see if logged in user has access to specific elements.

      input:  (string)$access:  name of the access element.

      output: (boolean) true:   user has access.
                        false:  user does not have access.
  */
  protected function checkUserAccess($access)
  {
    if($this->m_user == NULL)
      return false;

    if(!$this->m_user->m_access->hasAccessTo($access))
        return false;
    return true;
  }
}
?>