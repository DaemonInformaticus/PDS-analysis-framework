<?php
include("incl/const.php");
include_once("incl/class/page/CSecurePage.php");

$arrAccElem = array("page1");
$page       = new CSecurePage($env, $arrAccElem);

$access     = $page->getUserAccess();

$page->showHeader($access);
if(!$page->m_isSecure)
  $page->m_cid = 1000;


switch($page->m_cid)
{
  case 0:
    // point of entry
    print("welcome to page 1, case 0");
    break; // end of case 0
  case 1000:
    // Access denied!
    $page->showAccessDenied();
    break; // end of case 1000
}
$page->showFooter();
?>