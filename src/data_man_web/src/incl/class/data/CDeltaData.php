<?php
include_once("CData.php");
/* ********************************************************************************** */
/* filename: CDeltaData.php                                                           */
/* ********************************************************************************** */
/* description: Extends CData. CDeltaData, upon updating values in a row, creates a   */
/*  new row and sets visibility of the updated row to '0'.                            */
/*  A new row gets the visibility value '1' on creation.                              */
/*                                                                                    */
/*  A table represented by CDeltaData should at least implement the following columns */
/*  - prevID: INT: will contain the id of the row it evolved from (0 if new row)      */
/*  - visible: Will be set to 0 if a newer row exists. 1 by default.                  */
/*                                                                                    */
/*                                                                                    */
/*                                                                                    */
/*                                                                                    */
/* ********************************************************************************** */
/* changelog:                                                                         */
/* date:      | description:                                                          */
/* 2012-03-22 | added description of delta-data.                                      */
/*                                                                                    */
/*                                                                                    */
/* ********************************************************************************** */

class CDeltaData extends CData
{
  private $m_arrTableRef;

  function __construct($id, $bPrefetchAll, $tblName, $envs, $arrColNames, $arrTableRef = array())
  {
    // function __construct($id, $bPrefetchAll, $tblName, $envs, $arrColNames)
    CData::__construct($id, $bPrefetchAll, $tblName, $envs, $arrColNames);

    $this->m_tableRef = $arrTableRef;

    // if this is a new line (id == 0), the default value for visible is '1'.
    if($id == 0)
      $this->setValue("visible", 1, true);
  }

  /*
    deleteRow: Override of CData's deleteRow: DeltaData tables have a 'visible' column. If the deleteRow is a 'soft-delete', the 'visible' column value is set to 0.

      input:  (boolean)$bHardDelete:  'false':  don't actually delete the row, but set visible to false and update the updated and updatedBy values.
                                      'true:    delete the row, calling CData::deleteRow

              (int)$updatedBy:                  userID of the person (soft)deleting the value.

      output: (boolean) success.
  */
  public function deleteRow($bHardDelete = false, $updatedBy = 0)
  {
    // if it's a soft delete (i.e. not a hard-delete)
    if(!$bHardDelete)
    {
      // update the row to visible = false and updated and updatedBy accordingly.
      $id = $this->getValue("id");

      $this->resetNewValues();
      $this->setValue("id",         $id);
      $this->setValue("visible",    0);
      $this->setValue("updated",    date("Y-m-d H:i"));
      $this->setValue("updatedBy",  $updatedBy);

      return CData::updateValues();
    }
    else
    {
      // call parent-class 'deleteRow'. This executes a DELETE sql-statement.
      return CData::deleteRow();
    }

    return false;
  }

  /*
    updateValues: Override CData::updateValues. CDataValues just rewrites the row with specified id. Since we want to have a completly new row,
                  We're going to have to query the entire row with all values so far, create a new row and store it with insertValues.
                  Then we're going to have to set the visibility of this current row to '0'.
                  Also keep in mind that the new row has a 'prevID' with the id of the current row. The updated and updatedBy values have
                  to be updated accordingly.

      input:  (int)updatedBy:   UserID of the user updating the row.

      output: (int)id:          id of the new row.

  */
  public function updateValues($updatedBy = 0)
  {
    // print("CDeltaData::updateValues: $updatedBy is calling updateValues<br>\n");
    // before anything else: if the row we're updating is locked:
    if($this->isLocked())
    {
      // if we're not the one locking the ****** row:
      $lockID = $this->getValue("lockID");
     //  print("CDeltaData::updateValues: Row is locked by user: $lockID<br>\n");
      if($updatedBy != $lockID)
      {
        // print("CDeltaData::updateValues: Current updater ($updatedBy) is not the owner of the lock!<br>\n");
        // return 0
        return 0;
      }
    }

    print("CDeltaData::updateValues: No lock problems<br>\n");

    // get table name for this object:
    $tblName = $this->getTblname();

    // get column names for this object.
    $arrColNames = $this->m_arrColNames;
    $lenColNames = count($arrColNames);

    // get all values for the current object.
    $arrValues = array();
    for($i = 0; $i < $lenColNames; $i++)
    {
      $arrValues[$arrColNames[$i]] = $this->getValue($arrColNames[$i]);

    }

    // print("CDeltaData::updateValues: creating new object for table $tblName<br>\n");
    // create new object.
    $pDeltaData = new CDeltaData(0, false, $tblName, $this->m_envs, $arrColNames);

    // write values of the origional object.
    // print("CDeltaData::updateValues: Writing values to new object. <br>\n");
    for($i = 0; $i < $lenColNames; $i++)
    {
      $pDeltaData->setValue($arrColNames[$i], $arrValues[$arrColNames[$i]], true);
    }

    // Rewrite id column to '0'! Otherwise the insert will try to insert the row with the specified id. The id of the origional row!
    // Bad idea, because the insert will fail. (duplicate primary key).
    $pDeltaData->setValue("id",         0,                  true);
    $pDeltaData->setValue("prevID",     $arrValues['id'],   true);
    $pDeltaData->setValue("updated",    date("Y-m-d H:i"),  true);
    $pDeltaData->setValue("updatedBy",  $updatedBy,         true);

    // print("CDeltaData::updateValues: values have been written to new object.<br>\n");

    // insert values of new object.
    $pDeltaData->insertValues();
    $deltaID = $pDeltaData->getValue("id");
    // print("CDeltaData::updateValues: id of the new object: $deltaID<br>\n");

    // update all references in corresponding tables.
    if(count($this->m_arrTableRef) > 0)
    {
      $this->updateReferences($deltaID, $arrValues['id'], $updatedBy);
    }

    // (soft)delete old object.
    $this->deleteRow(false, $updatedBy);

    // return id of the new object.
    return $deltaID;
  }


  /*
    updateReferences: After updating a record by creating a new row, the id-references in other tables' FK's should be updated as well.
                      references to tables and columns are stored in this object under 'm_arrTableRef'. This is an array containing
                      CTableRef objects. For each object CTableRef, a table is updated with columns setting 'value', origionally referencing to 'oldValue' ID's.

      input:  (int)$value:      The new id of the row we updated (created a new record for.)
              (int)$oldValue:   The id of the previous record, to which we want to write the new id to.
              (int)$updatedBy:  the userID of the user updating the new record.
  */
  private function updateReferences($value, $oldValue, $updatedBy)
  {
    $updated      = date("Y-m-d H:i");
    $arrTableRef  = $this->m_arrTableRef;
    $lenTableRef  = count($arrTableRef);

    for($i = 0; $i < $lenTableRef; $i++)
    {
      $pTableRef  = $arrTableRef[$i];
      $tblName    = $pTableRef->m_tblName;
      $ColName    = $pTableRef->m_colName;
      $sql        = "UPDATE $tblName SET $colName=$value, updated='$updated', updatedBy=$updatedBy WHERE $colName=$oldValue;";

      mysql_query($sql, $this->getConnection());
    }
  }
}
?>