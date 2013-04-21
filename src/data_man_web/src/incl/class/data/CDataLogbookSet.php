<?php
include_once("CDataLogbook.php");

class CDataLogbookSet
{
  private $m_dbConn;
  private $m_arrLogbook;
  private $m_arrFilters;
  public  $m_page;
  private $m_length;

  function __construct($dbConn, $length)
  {
    $this->m_dbConn = $dbConn;
    $this->m_arrLogbook = array();
    $this->m_arrFilters = array();
    $m_length = $length;
  }

  /*
    setFilter: add a filter line to use in the query.
      input:  (String)$key:   Name of the colum
              (String)$value: Value to filter on.
  */
  public function setFilter($key, $value)
  {
    $sqlFilter = "";
    if(count($this->m_ArrFilters) > 0)
      $sqlFilter = " AND";
    if(is_numeric($value))
      $sqlFilter .= "$key=$value";
     else
      $sqlFilter .= "$key='$value'";
    array_push($m_arrFilters, $sqlFilter);
  }

  /*
    generateData: Execute the actual query.
  */
  public function generateData()
  {
    $sql = "SELECT id FROM tblLogbook ";

    if(count($this->arrFilters) > 0)
      $sql .= "WHERE ";

    for($i = 0; $i < count($this->arrFilters); $i++)
    {
      $filter  = $this->arrFilters[$i];
      $sql    .= "$filter ";

      if($i < (count($this->arrFilters) - 1))
        $sql .= " AND "
    }

    $sql    .= " ORDER BY created DESC ";

    $length  = $this->m_length;
    $start   = $this->m_page * $length;

    $sql    .= "LIMIT $start, $length;";
    $qry     = mysql_query($sql, $this->m_dbConn);

    while($row = mysql_query($qry))
    {
      $id       = $row[0];
      $logbook  = new CDataLogbook($id, true, $this->m_dbConn);

      array_push($this->m_ArrLogbook, $logbook);
    }
  }
}
?>