<?php
include_once("CDataUToAccElement.php");
include_once("CDataAccessElement.php");

class CDataUserToAccess
{
  private $m_elements;
  private $m_dbConn;
  private $m_userID;
  private $m_envs;

  function __construct($envs, $userID)
  {
    $this->m_envs     = $envs;
    $this->m_userID   = $userID;
    $this->m_elements = array();

    if($userID > 0)
      $this->initialize();
  }

  /*
    hasAccessTo: Checks to see if the user has access to a specified element.
      input:  (String)$elementName: Name of the element.
      output: (boolean) true: User has access.
                        false: user has no access.
  */
  public function hasAccessTo($elementName) { return isset( $this->m_elements[$elementName] ); }

  /*
    revokeAccessTo: Remove the access for a user, by the given access element's name.
      input:  (String)$elementName: name of the element to remove for this user.
      output: (boolean) success.
  */
  public function revokeAccessTo($elementName)
  {
    if(!isset($this->m_elements[$elementName]))
      return false;

    $userID   = $this->m_userID;
    $element  = $this->m_elements[$elementName];
    $accessID = $element->getValue("id");

    $sql = "DELETE FROM tblUserToAccess WHERE accessID=$accessID AND userID=$userID;";
    mysql_query($sql, $element->getConnection());

    return true;
  }

  /*
    addAccessTo: Adds access element by name to this user's access element set.
    input:  (String)$elementName: name of the element to add for this user.
            (int)$createdBy:      id of the user doing the adding. This is written to the row in the database.
  */
  public function addAccessTo($elementName, $createdBy)
  {
    // print("CDataAccess::addAccessTo: elementName: $elementName<br>\n");

    // Get element ID
    $element = new CDataAccessElement(0, false, $this->m_envs);
    if(!($element->getAccessByName($elementName)))
      return false;

    // create userToAccessElement
    $accessID = $element->getValue("id");
    $userID   = $this->m_userID;
    $created  = date("Y-m-d H:i");

    // print("CDataAccess::addAccessTo: accessID: $accessID<br>\n");
    // print("CDataAccess::addAccessTo: userID: $userID<br>\n");

    $uToAccElement = new CDataUToAccElement(0, false, $this->m_envs);
    $uToAccElement->setValue("userID",    $userID,    true);
    $uToAccElement->setValue("accessID",  $accessID,  true);
    $uToAccElement->setValue("created",   $created,   true);
    $uToAccElement->setValue("createdBy", $createdBy, true);

    // Store UserToAccessElement.
    return $uToAccElement->insertValues();
  }

  /*
    clearAccess: Remove all access elements for this user.
  */
  public function clearAccess()
  {
    $userID = $this->m_userID;

    $sql = "DELETE FROM tblUserToAccess WHERE userID=$userID;";
    mysql_query($sql, $this->m_envs['dbConn']);
  }

  /*
    initialize: Called on construction. Get all references to access elements for this user and dd them to the map.
  */
  private function initialize()
  {
    $userID = $this->m_userID;
    // $data   = new CData(0, false, "", $this->m_envs);

    // print("CDataAccess::initialize: backtrace:<br>\n");
    // print_r($this->m_envs);
    // print_r(debug_backtrace());

    $sql    = "SELECT id FROM tblUserToAccess WHERE userID=$userID;";
    $qry    = mysql_query($sql, $this->m_envs['dbConn']);

    while($row = mysql_fetch_row($qry))
    {
      $UToAcc                 = new CDataUToAccElement($row[0], false, $this->m_envs);
      $accessID               = $UToAcc->getValue("accessID");
      $accessElement          = new CDataAccessElement($accessID, false, $this->m_envs);

      $key                    = $accessElement->getValue("name");
      $this->m_elements[$key] = $accessElement;
    }
  }
}

?>