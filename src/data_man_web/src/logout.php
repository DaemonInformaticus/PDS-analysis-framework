<?php
include("incl/const.php");
include_once("incl/class/page/CPageLogout.php");

$page = new CPageLogout($env);

$page->logout();

$page->showHeader(0);

print($page->showLogout());

$page->showFooter();
?>