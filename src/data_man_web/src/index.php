<?php
include("incl/const.php");
include("incl/class/page/CPageIndex.php");

$page = new CPageIndex($env);
$userAccess = 0;

// $username   = $page->m_pageUser->getValue("name");

$access = $page->getUserAccess();

$page->showHeader($access);
print($page->messageOfTheDay());
$page->showFooter();
?>