<?php
/* ********************************************************************************** */
/* filename: CData.php                                                                */
/* ********************************************************************************** */
/* description: represents a row in a specified table. CData is abstract, meaning     */
/*   that for each table, a new class should be created that extends CData. The       */
/*   new class specifies what columns the table is constructed from.                  */
/*                                                                                    */
/*   Any table represented by CData should at least contain the following columns.    */
/*   - id: INT PRIMAIRY KEY: id of the row.                                           */
/*   - created: DATETIME date and time of creation of the row.                        */
/*   - updated: DATETIME date and time the row was updated. ("" by default)           */
/*   - createdBy: INT id of user / agent creating the row.                            */
/*   - updatedBy: INT id of user / agent updating the row.                            */
/*                                                                                    */
/*   Optionally, the row can be lockable. This means that only the userID that set    */
/*   the lock, has access to write / update the row. To allow for lock, the table     */
/*   should implement the following columns:                                          */
/*   - lockID INT: userID of the user / agent that activates the lock.                */
/*   - lockDate DATETIME: date and time of when the lock was set.                     */
/*                                                                                    */
/*                                                                                    */
/*                                                                                    */
/*                                                                                    */
/* ********************************************************************************** */
/* changelog:                                                                         */
/* date:      | description:                                                          */
/* 2012-03-22 | added description of data.                                            */
/*                                                                                    */
/*                                                                                    */
/* ********************************************************************************** */
abstract class CData
{
  private   $m_oldValues;
  private   $m_newValues;
  private   $m_dbConn0;
  private   $m_tblName;
  private   $m_isValid;
  protected $m_envs;
  protected $m_arrColNames;
  private   $m_bLockable;

  function __construct($id, $bPrefetchAll, $tblName, $envs, $arrColNames)
  {
    $oldValues = array();
    $newValues = array();

    $this->m_isValid      = true;
    $this->m_tblName      = $tblName;
    $this->m_envs         = $envs;
    $this->m_arrColNames  = $arrColNames;
    $this->m_bLockable    = $this->checkLockable();

    // force id to be a number, or default to 0. This way we avoid SQL injection through this value.
    if(is_numeric($id) == false)
      $id = 0;

    if(isset($envs['dbConn']))
      $this->m_dbConn0      = $envs['dbConn'];

      // print("CData onstructor: dbConn: $dbConn <br>");
    // print("CData onstructor: m_dbConn0: $this->m_dbConn0 <br>");
    $this->setValue("id", $id, true);

    if($bPrefetchAll)
      $this->prefetchAllValues();
  }

  private function checkLockable()
  {
    // print("CData::checkLockable: checking lockable<br>\n");
    $lenColNames = count($this->m_arrColNames);

    for($i = 0; $i < $lenColNames; $i++)
    {
      $colName = $this->m_arrColNames[$i];
      // print("CData::checkLockable: checking colName: $colName<br>\n");
      if($colName == "lockID")
        return true;
    }

    return false;
  }

  /*
    isValid: returns the state of this object's data.
      output: true: This is a valid object and you should not distrust the data.
              false: for some reason the data is invalid. Don't count on usability of the data.
  */
  public function isValid() { return $this->m_isValid; }

  /*
    getTblName: Return the tablename related to this data object.
      output: (String): table name.
  */
  public function getTblName() { return $this->m_tblName; }

  /*
    getConnection: Get a connection to the database. If one is already made for this object, return it. Otherwise create new connection.
      output(database connection resource) connection to use in mysql commands.
  */
  public function getConnection()
  {
    // See if a connection already exists:
    // No: create new connection
    if($this->m_dbConn0 == NULL)
    {
      // print("CData::getConnection: Creating new connection...<br>\n");
      $dbHost = $this->m_envs['dbHost'];
      $dbUser = $this->m_envs['dbUser'];
      $dbPass = $this->m_envs['dbPass'];
      $dbName = $this->m_envs['dbName'];

      /*if($dbName == "")
      {
        print("<br>--------------------------------<br>");
        print_r(debug_backtrace());
        print("<br>--------------------------------<br>");
      }*/
      //print("CData::getConnection: Creating connection...<br>");

      $this->m_dbConn0 = mysql_connect($dbHost, $dbUser, $dbPass);

      if(!$this->m_dbConn0)
        return NULL;

      // print("CData::getConnection: Selecting database $dbName...<br>");
      mysql_select_db($dbName, $this->m_dbConn0);
    }

    // return connection
    // print("CData::getConnection: Returning connection: $this->m_dbConn0<br>");
    return $this->m_dbConn0;
  }

  /*
    getValue: return a value from the new values dataset, given a key.
    input:  (String) $key: key for the associative array.
    output: (String) : value associated with the key.
  */
  public function getValue($key)
  {
    /*print("<br>");
    print_r($this->m_newValues);
    print("<br>");*/
    if(isset($this->m_newValues[$key]))
    {
      // print("key found<br>");
      return stripslashes($this->m_newValues[$key]);
    }
    elseif($this->qryValue($key))
    {
      return stripslashes($this->m_newValues[$key]);
    }

    return "";
  }

  /*
    setValue: Set a value in the associative arrays.
    input:
      - (String) key: Key name of the associative array
      - value: Value associated with the key.
      - (Boolean) bIsNewValue: if this parameter is true, the value given is added to both the old and the new array.
  */
  public function setValue($key, $value, $bIsNewValue = false)
  {
    $value = addslashes($value);

    $this->m_newValues[$key] = $value;
    if($bIsNewValue)
    {
      $this->m_oldValues[$key] = $value;
    }
  }

  /*
    qryValue: get a given value from the database.
    input:  (String) key: the name of the column.
    output: (Boolean): true on success. otherwise false.
  */
  private function qryValue($key)
  {
    // print("CData::qryValue: <br>\n");
    $tblName  = $this->m_tblName;

    // -------- Debug code to fix nesting: ---------------

    /*if($key == "title")
    {
      print("CData::qryValue: table name: $tblName : key: $key<br>\n");
      print("CData::qryValue: debug backtrack: <br>\n");
      print_r(debug_backtrace());
      print("<br>");
    }*/
    // ---------------------------------------------------

    $id       = $this->getValue("id");

    if($id == 0)
      return false;


    $sql      = "SELECT $key FROM $tblName WHERE id=$id;";
    $qry      = mysql_query("$sql", $this->getConnection());

    // print("CData::qryValue: sql: $sql<br>");
    /*if($key == "title")
    {
      $conn     = $this->getConnection();
      print("CData: qryValue: sql: $sql<br>conn: $conn <br>qry: $qry<br>");
    }*/

    if($row = mysql_fetch_row($qry))
    {
      $value = stripslashes($row[0]);
      // print("CData: qryValue: $key -> sql: $sql result: $value");
      $this->setValue($key, $value, true);
      return true;
    }

    return false;
  }

  /*
    updateValues: Write all updates to the database. Each value that is not equal to the original value is updated.
  */
  public function updateValues()
  {
    // print("CData::updateValues: calling updatevalues.<br>\n");
    // Get keys from the oldvalues array
    $arrKeys      = array_keys($this->m_newValues);
    $id           = $this->getValue("id");
    $counter      = 0;
    $bFirstValue  = true;
    $updated      = date("Y-m-d H:i");
    $lockID       = $this->m_bLockable ? $this->getValue("lockID") : 0;

    // print("CData::updateValues: lockID: $lockID.<br>\n");
    // If the row is locked:
    if($lockID > 0)
    {
      // get the updatedBy value.
      $updatedBy = $this->getValue("updatedBy");

      // if the updatedBy value is not equal to lockID:
      if($updatedBy != $lockID)
      {
        // return 0;
        // print("CData::updateValues: User has no exclusive rights! bugger off!<br>\n");
        return 0;
      }
    }

    $this->setValue("updated", $updated);

    // start sql construction
    $sqlUpdate = "UPDATE $this->m_tblName SET ";

    // for each key found:
    for($i = 0; $i < count($arrKeys); $i++)
    {
      $key = $arrKeys[$i];

      // Is the new value different from the old value?
      $newValue = $this->m_newValues[$key];
      $oldValue = !isset($this->m_oldValues[$key]) ? "" : $this->m_oldValues[$key];

      // print("<br>CData::updateValues: Evaluating key: $key: oldValue: $oldValue and newValue: $newValue<br>");

      if($newValue != $oldValue)
      {
        $bNumeric = true;
        if(!is_numeric($newValue))
          $bNumeric = false;

        if($bFirstValue == false)
          $sqlUpdate .= ", ";
         else
          $bFirstValue = false;

        // add to the update sql.
        $sqlUpdate .= "$key=";
        if(!$bNumeric)
          $sqlUpdate .= "'$newValue'";
         else
          $sqlUpdate .= "$newValue";

        // update counter with 1, but only if it's not the 'updated' key, because this is always updated.
        if($key != "updated")
          $counter++;

        $this->m_oldValues[$key] = $this->m_newValues[$key];
      }
    }
    // Finish the update sql
    $sqlUpdate .= " WHERE id=$id;";
    // print("CData::updateValues: sqlUpdate: $sqlUpdate<br>counter: $counter<br>connection:".$this->getConnection()."<br>");

    // Execute the update sql, but only if it's needed:
    if($counter > 0)
    {
      // print("CData::updateValues: executing sqlUpdate: $sqlUpdate<br>\n");
      mysql_query($sqlUpdate, $this->getConnection());
    }

    return $counter;
  }

  /*
    prefetchAllValues: Initialize the dataset to all values in the database, given the id.
                        This is optionally executed from the constructor. If it's not executed,
                        getValue will call qryValue for each value that doesn't exist in the dataset yet.
  */
  public function prefetchAllValues()
  {
    // print("CData::prefetchAllValues: <br>\n");
    // print("CData::prefetchAllValues: Prefetching all values.<br>\n");
    $id = $this->getValue("id");
    // print("CData::prefetchAllValues: id: $id<br>\n");
    if($id == 0)
    {
      $this->m_isValid = false;
      return 0;
    }

    // Reset the dataset.
    $this->m_oldValues = array();
    $this->m_newValues = array();
    $this->setValue("id", $id, true);

    $counter  = 0;
    $sql      = "SELECT * FROM $this->m_tblName WHERE id=$id;";
    $qry      = mysql_query($sql, $this->getConnection());
    // print("CData::prefetchAllValues: sql: $sql<br>\n");

    if(!($row = mysql_fetch_assoc($qry)))
    {
      $this->m_isValid = false;
      return 0;
    }
    // print("CData::prefetchAllValues: row found<br>\n");

    $arrKeys = array_keys($row);

    for($i = 0; $i < count($arrKeys); $i++)
    {
      $key = $arrKeys[$i];
      $value = $row[$key];
      $this->setValue($key, $value, true);
      $counter++;
    }

    return $counter;
  }

  /*
    insertValues: insert all values in the array. Unfortunatly, the common CData object doesn't know what a particular table looks like exactly.
                  So as a parameter we pass the columns, in the sequence they appear in the table.

      Output: (boolean):  true: insert success. (false otherwise).
  */
  public function insertValues()
  {
    // start construction of the sql.
    $sql = "INSERT INTO $this->m_tblName VALUES(";
    // for each name in the arrNames variable:
    for($i = 0; $i < count($this->m_arrColNames); $i++)
    {
      // Get the associated value from the array.
      $key = $this->m_arrColNames[$i];
      $value = $this->getValue($key);
      // add to the sql.
      if(is_numeric($value))
        $sql .= "$value";
       else
        $sql .= "'$value'";

      if($i < (count($this->m_arrColNames) - 1))
        $sql .= ", ";
    }

    // finish up the sql.
    $sql .= ");";

    // execute the sql.
    // print("CData: insertValues sql: $sql<br>");
    $result = mysql_query($sql, $this->getConnection());

    // if successful:
    if($result)
      $this->setValue("id", mysql_insert_id($this->getConnection()), true);
     else
      $this->m_isValid = false;

    return $this->m_isValid;
  }

  /*
    dumpValues: Debug function to be able to at any time dump the values for old and new data.
  */
  public function dumpValues()
  {
    print("<br>");
    print("oldValues: ");
    print_r($this->m_oldValues);
    print("<br>");
    print("newValues: ");
    print_r($this->m_newValues);
    print("<br>");
  }

  /*
    deleteRow: delete's the row to which this data object pertains.
  */
  public function deleteRow()
  {
    $tblName  = $this->getTblName();
    $id       = $this->getValue("id");

    $sql = "DELETE FROM $tblName WHERE id=$id;";
    mysql_query($sql, $this->getConnection());

    $this->m_isValid = false;
  }

  /*
    lockTimeStamp: calculate the lock-time in unix-seconds:

      output: (int)Unix timestamp of the lock-datetime.
  */
  private function lockTimeStamp()
  {
    $ts               = 0;
    $lockdate         = $this->getValue("lockDate");
    $arrLockDateTime  = explode(" ", $lockdate);
    $arrLockDate      = explode("-", $arrLockDateTime[0]);
    $arrLockTime      = explode(":", $arrLockDateTime[1]);
    $year             = $arrLockDate[0];
    $month            = $arrLockDate[1];
    $day              = $arrLockDate[2];
    $hour             = $arrLockTime[0];
    $minutes          = $arrLockTime[1];

    $ts = mktime($hour, $minutes, 0, $month, $day, $year);

    return $ts;
  }

  /*
    setLock: set the lock for this row.

      input:  (int)$userID: User for which to set the lock.

      output: (boolean) true:   lock set successfully.
                        false:  Either the row isn't lockable, or the row is already locked and the time isn't up yet.
  */
  public function setLock($userID)
  {
    // If this row cannot be locked: return false;
    // print("CData::setLock: checking for lockable<br>\n");

    if(!$this->m_bLockable)
      return false;

    // print("CData::setLock: row is lockable.<br>\n");

    // See if the row is locked and since when.
    // print("CData::setLock: checking for lock<br>\n");
    if($this->isLocked())
      return false;

    // print("CData::setLock: Row is not locked.<br>\n");

    // lock the row for this user.
    $lockDate = date("Y-m-d H:i");

    $this->setValue("lockID",   $userID);
    $this->setValue("lockDate", $lockDate);
    CData::updateValues();

    // return true
    return true;
  }

  /*
    isLocked: See if this row is locked.
  */
  public function isLocked()
  {
    // if the row isn't lockable, obviously it's not locked.
    if(!$this->m_bLockable)
      return false;

    // if the lockID == 0: It's not locked.
    if($this->getValue("lockID") == 0)
      return false;

    // get the lock's timestamp.
    $lockTS     = $this->lockTimeStamp();
    // add the maximum amount of time a lock may exist.
    $maxLockTS  = $lockTS + $this->m_envs['lock_max_length'];
    // get the current time.
    $currTS     = time();

    // if the maximum time has not elapsed yet:
    if($currTS <= $maxLockTS)
    {
      // return true;
      return true;
    }

    // no lock.
    return false;
  }

  /*
    unlock: set the lockID of this row to '0'.
  */
  public function unlock()
  {
    $this->setValue("lockID", 0);

    return CData::updateValues() > 0;
  }

  protected function resetNewValues()
  {
    $this->m_newValues = array();
  }
}
?>