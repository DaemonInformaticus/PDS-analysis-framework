<?php
include_once("CLogic.php");
include_once("incl/class/data/CDataUserGroupSet.php");
include_once("incl/class/data/CDataAccessElementSet.php");


class CLogicGroupMan extends CLogic
{
  function __construct($envs)
  {
    CLogic::__construct($envs);
  }

  /*
    getGroupList: generate a list of groups.

      input:  (string)$filter:  if specified, the groupnames are filtered on this input with a 'LIKE'.

      output: (array(int, CDataUserGroup) list of usergroups.
  */
  public function getGroupList($filter)
  {
    $pUserGroupSet = new CDataUserGroupSet($this->m_envs);
    $arrGroups = $pUserGroupSet->getGroups($filter);

    return $arrGroups;
  }

  /*
    storeNewGroup: Store a new group to the database.

      input:  (string)$name:    name of the new group.
              (int)$createdBy:  id of the user storing the new group.

      output: (boolean)success.
  */
  public function storeNewGroup($name, $createdBy)
  {
    $pGroup = new CDataUserGroup(0, false, $this->m_envs);

    // validate name input.
    if($name == "")
    {
      $this->addError("Empty group name.");
      return false;
    }

    // see if the name already exists.
    if($pGroup->getGroupByName($name))
    {
      $this->addError("A group with $name already exists");
      return false;
    }
    /*
    tblUserGroup
    - id        INT PRIMARY KEY AUTO_INCREMENT
    - name      TEXT
    - created   DATETIME
    - updated   DATETIME
    - createdBy INT
    - updatedBy INT
    */
    // Add values to new object.
    $created = date("Y-m-d H:i");

    $pGroup->setValue("name",       $name,      true);
    $pGroup->setValue("created",    $created,   true);
    $pGroup->setValue("createdBy",  $createdBy, true);

    // insert values to database.
    if(!$pGroup->insertValues())
    {
      $this->addError("Could not add group $name");
      return false;
    }

    // return success.
    return true;
  }

  /*
    storeEditGroup: Store new values to an existing group.

      input:  (int)$id:         database id of the group's row to update.
              (string)$name:    (new?)name of the group.
              (int)$updatedBy:  id of the user updating the row.

      output: (boolean)success: true:   OK.
                                false:  error updating.
  */
  public function storeEditGroup($id, $name, $updatedBy)
  {
    // get the group:
    $pGroup   = new CDataUserGroup($id, false, $this->m_envs);

    // write the new values:
    $updated  = date("Y-m-d H:i");
    $pGroup->setValue("name",       $name);
    $pGroup->setValue("updated",    $updated);
    $pGroup->setValue("updatedBy",  $updatedBy);

    // store the new values to the database.
    if(!$pGroup->updateValues())
    {
      $this->addError("Could not update group.");
      return false;
    }

    // return success.
    return true;
  }

  /*
    removeGroup: remove a given usergroup.

      input:  (int)$groupID: id of the group to remove.

      output: (boolean)success: true: OK.
  */
  public function removeGroup($groupID)
  {
    $pGroup = new CDataUserGroup($groupID, false, $this->m_envs);
    $pGroup->deleteRow();

    return true;
  }

  /*
    getElementsByGroup: Get all access elements for a given group.

      input:  (int)$groupID: id of the group.

      output: (array(int, CDataAccessElement)): array of access elements for this group.
  */
  public function getElementsByGroup($groupID)
  {
    $arr    = array();
    $pGroup = new CDataUserGroup($groupID, false, $this->m_envs);
    $arr    = $pGroup->getElements();

    return $arr;
  }

  /*
    getElementsOutsideGroup: Get all elements that are NOT in the given group.

      input:  (int)$groupID:  id of the group for which to get the not-elements.

      output: (array(int, CDataAccessElement)): array of elements not in the given group.
  */
  public function getElementsOutsideGroup($groupID)
  {
    $arrNotInGroup      = array();
    // Get groupelements and elements in the entire set.
    $pAccessElementSet  = new CDataAccessElementSet($this->m_envs);
    $groupElements      = $pAccessElementSet->getElements($groupID);
    $allElements        = $pAccessElementSet->getElements(0);
    $lenAllElements     = count($allElements);
    $lenGroupElements   = count($groupElements);
    $index              = 0;

    // for each element in the entire set:
    while($index < $lenAllElements)
    {
      $pElement   = $allElements[$index];
      $elementID  = $pElement->getValue("id");
      $bFound     = false;

      // see if the element is in the set of group elements.
      for($i = 0; $i < $lenGroupElements; $i++)
      {
        $pGroupElement  = $groupElements[$i];
        $idGroupElement = $pGroupElement->getValue("id");

        if($idGroupElement == $elementID)
        {
          // element found. Mark as found and break the for-loop.
          $bFound = true;
          break;
        }
      }

      // if the element was not found in the current group:
      if(!$bFound)
      {
        // add to the result array.
        array_push($arrNotInGroup, $pElement);
      }

      $index++; // next element
    }

    // return Result Set.
    return $arrNotInGroup;
  }

  /*
    addElementToGroup: Add a given access element to a specified usergroup

      input:  (int)$elementID:  id of the access element to add to a group.
              (int)$groupID:    id of the user group to which to add the element.
              (int)$createdBy:  id of the user adding the element to the group.

      output: (boolean)success.
  */
  public function addElementToGroup($elementID, $groupID, $createdBy)
  {
    // verify the link does not exist yet.
    $pGroupAccess = new CDataGroupToAccess(0, false, $this->m_envs);
    if($pGroupAccess->getIDByRef($groupID, $elementID))
    {
      $this->addError("Reference already exists.");
      return false;
    }

    // create new group-to-access object.
    $pGroupAccess = new CDataGroupToAccess(0, false, $this->m_envs);

    // set values.
    $created = date("Y-m-d H:i");

    $pGroupAccess->setValue("groupID",    $groupID,   true);
    $pGroupAccess->setValue("accessID",   $elementID, true);
    $pGroupAccess->setValue("created",    $created,   true);
    $pGroupAccess->setValue("createdBy",  $createdBy, true);

    // insert row.
    // return success
    $bSuccess = $pGroupAccess->insertValues();
    if(!$bSuccess)
    {
      $this->addError("Could not add row.");
    }

    return $bSuccess;
  }

  /*
    removeElementFromGroup: Remove a given element from the group.

      input:  (int)$elementID:  id of the access element that has to be removed from the group.
              (int)$groupID:    id of the group from which the element must be removed.

      output: (boolean)success.
  */
  public function removeElementFromGroup($elementID, $groupID)
  {
    // find element.
    $pGroupAccess = new CDataGroupToAccess(0, false, $this->m_envs);
    if(!$pGroupAccess->getIDByRef($groupID, $elementID))
    {
      $this->addError("Could not find reference.");
    }

    // remove element from table.
    $pGroupAccess->deleteRow();

    // return success
    return true;
  }

  public function storeNewAccessElement($name, $description, $createdBy)
  {
    // if an element with this name already exists.
    $pElement = new CDataAccessElement(0, false, $this->m_envs);
    if($pElement->getAccessByName($name))
    {
      // set error line.
      $this->addError("Element with this name already exists.");

      // return fail
      return false;
    }

    // create new Element.
    $pElement = new CDataAccessElement(0, false, $this->m_envs);
    /*
    tblAccessElements
    - id          INT PRIMARY KEY AUTO_INCREMENT
    - name        TEXT
    - description TEXT
    - active      INT
    - created     DATETIME
    - updated     DATETIME
    - createdBy   INT
    - updatedBy   INT
    */
    $created = date("Y-m-d H:i");

    $pElement->setValue("name",         $name,        true);
    $pElement->setValue("description",  $description, true);
    $pElement->setValue("active",       1,            true);
    $pElement->setValue("created",      $created,     true);
    $pElement->setValue("createdBy",    $createdBy,   true);
    $pElement->setValue("updatedBy",    0,            true);

    $bResult = $pElement->insertValues();

    if(!$bResult)
    {
      $this->addError("Could not store element.");
    }

    // return success.
    return $bResult;
  }

  public function storeEditElement($id, $name, $description, $updatedBy)
  {
    // get element by name.
    $pElement = new CDataAccessElement($id, true, $this->m_envs);

    // if element does not exist:
    if(!$pElement->isValid())
    {
      // write error.
      $this->addError("Element does not exist.");

      // return false.
      return false;
    }

    // write new values to element.
    $pElement->setValue("name",         $name);
    $pElement->setValue("description",  $description);

    // store element.
    $bResult = $pElement->updateValues();

    if(!$bResult)
    {
      $this->addError("Could not store new values for element.");
    }

    // return success.
    return $bResult;
  }

  public function getAllElements()
  {
    $pAccessElementSet  = new CDataAccessElementSet($this->m_envs);
    $arrElements        = $pAccessElementSet->getElements();

    return $arrElements;
  }
}
?>