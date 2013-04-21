<?php
include_once("incl/const.php");

$dataSetID = 1;
// get unique set of row indexes.
$sqlRows = "SELECT rowIndex FROM tblColumnValue WHERE datasetID=$dataSetID GROUP BY rowIndex;";
$qryRows = mysql_query($sqlRows, $env['dbConn']);

print("<table border=\"1\">");

$sqlColNames = "SELECT stringValue FROM tblcolumndescriptor WHERE datasetID=$dataSetID AND stringKey='NAME' ORDER BY colIndex ASC;";
$qryColNames = mysql_query($sqlColNames, $env['dbConn']);
// print("sqlColNames: $sqlColNames<br />\n");

print("<tr>");
while($rowColNames = mysql_fetch_row($qryColNames))
{
  $colName = $rowColNames[0];
  print("<td>$colName</td>");
}

print("</tr>");
// for each row index for the given datasetID:


while($rowRows = mysql_fetch_row($qryRows))
{
  print("<tr>");
  $rowID = $rowRows[0];

  // get the data.
  $sql = "SELECT stringValue FROM tblColumnValue WHERE rowIndex=$rowID ORDER BY columnIndex ASC;";
  $qry = mysql_query($sql, $env['dbConn']);

  while($row = mysql_fetch_row($qry))
  {
    $value = $row[0];

    print("<td>$value</td>");
  }

  print("</tr>");
}

print("</table>");
?>