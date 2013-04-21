<?php
include("incl/const.php");
include_once("incl/class/page/CPageSecure.php");

// $arrAccElem = array("page1");
$page       = new CSecurePage($env);

$access     = $page->getUserAccess();

$page->showHeader($access);
if(!$page->m_isSecure)
  $page->m_cid = 1000;


switch($page->m_cid)
{
  case 0:
    // point of entry
    // print("welcome to page, case 0");

    break; // end of case 0
  case 1000:
    // Access denied!
    $page->showAccessDenied();
    break; // end of case 1000
}
$page->showFooter();
?>