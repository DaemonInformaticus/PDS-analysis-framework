<?php
include("incl/const.php");
include_once("incl/class/page/CPageLogin.php");

// print("dbHost: $value");
$page = new CPageLogin($env);

switch($page->m_cid)
{
  case 1:
    // process login
    $validParameters = $page->fetchParameters();
    $didLogin = false;
    if($validParameters == true)
    {
      $didLogin = $page->login();
    }
    break; // end of case 1
}

$page->showHeader(0);
?>
Object oriented development template, Version 0.1<br><br>
<?php
$pass = md5($page->getMD5Salt()."passwd");
// print("login.php: pass: $pass<br><br>");
// print("cid = ".$page->m_cid."<br>");

switch($page->m_cid)
{
  case 0:
    // Point of entry. Show login box:
    $page->showLoginBox();
    break; // end of case 0
  case 1:
    // Show login result:
    if($didLogin == true)
    {
      $username = $page->getUserName();
      $name     = $page->getName();

      print("Login successfull!<br>");

      print("name: $name<br>username: $username<br><br>Have a nice day.");
      print("Click <a href=\"index.php\">here</a> to continue.");
    }
    else
    {
      print($page->m_pageError."<br>Click <a href=\"login.php\">here</a> to try again.<br>");
    }
    break; // end of case 1
}

$page->showFooter();
?>
