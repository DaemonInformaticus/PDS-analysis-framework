<?php
class CTableRef
{
  public $m_tblName;
  public $m_colName;

  function __construct($tblName, $colName)
  {
    $this->m_tblName = $tblName;
    $this->m_colName = $colName;
  }
}
?>