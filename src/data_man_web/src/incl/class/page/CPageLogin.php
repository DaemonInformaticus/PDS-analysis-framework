<?php
include_once("incl/class/data/CDataUser.php");
include_once("incl/class/logic/CLogicLogin.php");
include_once("CPage.php");

class CPageLogin extends CPage
{
  private $m_logicLogin;
  private $m_username;
  private $m_password;
  public  $m_pageError;
  public  $m_user;

  function __construct($envs)
  {
    CPage::__construct($envs);
    $this->m_pageError = "";

  }

  public function getUserName() { return $this->m_username; }

  public function getName() { return $this->m_user->getValue("name"); }

  /*
    showLoginBox: Show the login box.
  */
  public function showLoginBox()
  {
    ?>
    <form name="login" action="login.php?cid=1" method="post">
      <table border="1">
        <tr><td>Username:</td><td><input type="text" name="name"></td></tr>
        <tr><td>Password:</td><td><input type="password" name="pass"></td></tr>
      </table>
      <input type="submit" name="submit" value="Login">
    </form>

    <?php
  }

  /*
    login: Called when a user submits a login. Calls the logic's 'authenticate'.
      output: (boolean) true:   authentication successful.
                        false:  Authentication failed. (See $this->m_pageError for any indication as to 'why'.
  */
  public function login()
  {
    $this->m_logicLogin = new CLogicLogin($this->m_envs);

    // print("CPageLogin::login(): name / pass: ". $this->m_username." / ".$this->m_password."<br>");
    $this->m_user = $this->m_logicLogin->authenticate($this->m_username, $this->m_password);
    if($this->m_user == NULL)
      $this->m_pageError = $this->m_logicLogin->m_error;

    return ($this->m_user != NULL);
  }

  /*
    fetchParameters: Get the parameters for login and password from the POST var.
      output: (boolean) true:   parameters retrieved successfully.
                        false:  could not find a value.
  */
  public function fetchParameters()
  {
    if(isset($_POST['pass']))
      $this->m_password  = $_POST['pass'];
     else
      $this->m_pageError = "Password field is not defined.";

    if(isset($_POST['name']))
      $this->m_username = $_POST['name'];
     else
      $this->m_pageError = "Username field is not defined.";

    if(!empty($this->m_pageError))
      return false;

    return true;

  }
}
?>