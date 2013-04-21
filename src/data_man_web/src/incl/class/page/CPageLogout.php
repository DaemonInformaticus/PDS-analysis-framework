<?php
include_once("CPage.php");
include_once("incl/class/logic/CLogicLogin.php");

class CPageLogout extends CPage
{
  private $m_logicLogin;
  private $m_bLogoutSuccess;

  function __construct($env)
  {
    CPage::__construct($env);

    $this->m_logicLogin     = new CLogicLogin($env);
    $this->m_bLogoutSuccess = false;
  }

  /*
    showLogout: Show the result of the logout.
      output: (String) html response on logout.
  */
  public function showLogout()
  {
    $code = "";

    if($this->m_bLogoutSuccess == false)
    {
      $code .= "Something went wrong in the attempt to logout! Contact the administrator.<br>\n";
    }
    else
    {
      $code .= "Logout successfull. Click here to continue.<br>\n";
    }

    return $code;
  }


  /*
    call logic's 'logout'.
  */
  public function logout()
  {
    $this->m_bLogoutSuccess = $this->m_logicLogin->logout();
  }
}
?>