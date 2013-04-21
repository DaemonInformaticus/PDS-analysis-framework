<?php
include("incl/const.php");
include("incl/class/page/CPage.php");

$page   = new CPage($env);

$sql    = array();
$entry  = array();

// array_push($sql, "");
array_push($sql, "CREATE TABLE tblUser(id INT PRIMARY KEY AUTO_INCREMENT, name TEXT, username TEXT, password TEXT, email TEXT, state INT, type INT, session TEXT, languageID INT, userGroup TEXT, IP TEXT, created DATETIME, updated DATETIME, createdBy INT, updatedBy INT);");
array_push($sql, "CREATE TABLE tblUserGroup(id INT PRIMARY KEY AUTO_INCREMENT, name TEXT, created DATETIME, updated DATETIME, createdBy INT, updatedBy INT);");
array_push($sql, "CREATE TABLE tblAccessElements(id INT PRIMARY KEY AUTO_INCREMENT, name TEXT, description TEXT, active INT, created DATETIME, updated DATETIME, createdBy INT, updatedBy INT);");
array_push($sql, "CREATE TABLE tblGroupToAccess(id INT PRIMARY KEY AUTO_INCREMENT, groupID INT, accessID INT, created DATETIME, updated DATETIME, createdBy INT, updatedBy INT);");
array_push($sql, "CREATE TABLE tblUserToAccess(id INT PRIMARY KEY AUTO_INCREMENT, userID INT, accessID INT, created DATETIME, updated DATETIME, createdBy INT, updatedBy INT);");
array_push($sql, "CREATE TABLE tblLogbook(id INT PRIMARY KEY AUTO_INCREMENT, IP TEXT, description TEXT, type INT, website TEXT, created DATETIME, updated DATETIME, createdBy INT, updatedBy INT);");
array_push($sql, "CREATE TABLE tblLanguage(id INT PRIMARY KEY AUTO_INCREMENT, name TEXT, description TEXT, active INT, created DATETIME, updated DATETIME, createdBy INT, updatedBy INT);");
array_push($sql, "CREATE TABLE tblLanguageLine(id INT PRIMARY KEY AUTO_INCREMENT, languageID INT, fieldname TEXT, value TEXT, active INT, created DATETIME, updated DATETIME, createdBy INT, updatedBy INT);");
array_push($sql, "CREATE TABLE tblDataSet(id INT PRIMARY KEY AUTO_INCREMENT, setGroup TEXT, setIdentifier TEXT, created DATETIME, updated DATETIME, createdBy INT, updatedBy INT);");
array_push($sql, "CREATE TABLE tblDataSetDescription(id INT PRIMARY KEY AUTO_INCREMENT, datasetID LONG, stringKey TEXT, stringValue TEXT, created DATETIME, updated DATETIME, createdBy INT, updatedBy INT);");
array_push($sql, "CREATE TABLE tblColumnDescriptor(id INT PRIMARY KEY AUTO_INCREMENT, datasetID INT, colIndex INT, stringKey TEXT, stringValue TEXT, created DATETIME, updated DATETIME, createdBy INT, updatedBy INT);");
array_push($sql, "CREATE TABLE tblColumnValue(id INT PRIMARY KEY AUTO_INCREMENT, datasetID INT, columnIndex INT, rowIndex INT, stringValue VARCHAR(24), created DATETIME, updated DATETIME, createdBy INT, updatedBy INT);");

// array_push($entry, "");
/********************************/
// Initial entries for this database.

/******* tblUser: ***************/
/*
tblUser
- id          INT PRIMAIRY KEY AUTO_INCREMENT
- name        TEXT
- username    TEXT
- password    TEXT
- email       TEXT
- state       INT
- type        INT
- session     TEXT
- languageID  INT
- userGroup   TEXT
- IP          TEXT
- created     DATETIME
- updated     DATETIME
- createdBy   INT
- updatedBy   INT
*/

$name       = "Martin Stam";
$username   = "martin";
$salt       = $page->getMD5Salt();
$passwd     = md5($salt."passwd");
$email      = "m.stam@subtracers.nl";
$state      = 1;
$type       = 1;
$languageID = 1;
$userGroup  = "administrator";
$created    = date("Y-m-d H:i");
$createdBy  = 1;

array_push($entry, "INSERT INTO tblUser VALUES(0, '$name', '$username', '$passwd', '$email', $state, $type, '', $languageID, '$userGroup', '', '$created', '', $createdBy, 0);");


$page->showHeaderNoMenu();

switch($page->m_cid)
{
  case 0:
    ?>
    <form name="install" action="setup.php?cid=1" method="post">
      Make database: <input type="submit" name="submit" value="Make">
    </form>
    <?php
    break; // end of case 0
  case 1:
    print("Building tables: <br>");
    // $data = new CData(0, false, "", $env);
    for($i = 0; $i < count($sql); $i++)
    {
      print("Building table: $sql[$i]... ");

      mysql_query($sql[$i], $env['dbConn']) or die("Error in sql $sql[$i]<br>");
      print("done.<br>");
    }
    print("Done building tables.<br> executing entries:<br>");
    for($i = 0; $i < count($entry); $i++)
    {
      print("Executing entry: $entry[$i]... ");
      mysql_query($entry[$i], $env['dbConn']) or die("Error in entry $entry[$i]<br>");
      print("done.<br>");
    }

    print("<br><br>Database installation complete. Have a nice day.");
    break; // end of case 1
}

$page->showFooter();
?>