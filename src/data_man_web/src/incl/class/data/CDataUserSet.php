<?php
include_once("CDataUser.php");

class CDataUserSet
{
  // private $m_dbConn;
  private $m_userSet;
  private $m_envs;

  function __construct($envs)
  {
    // $this->m_dbConn   = $dbConn;
    $this->m_envs     = $envs;
    $this->m_userSet  = array();

    $this->generateUserSet();
  }


  public function getUserSet() { return $this->m_userSet; }

  public function getUserByName($name) { return $this->m_userSet[$name]; }

  /*
    generateUserSet: initialize the set of users from the database. The result is a name / object map in m_userSet.
  */
  private function generateUserSet()
  {
    $sql = "SELECT id FROM tblUser ORDER BY name ASC;";
    $qry = mysql_query($sql, $this->m_envs['dbConn']);

    while($row = mysql_fetch_row($qry))
    {
      $id   = $row[0];
      $user = new CDataUser($id, false, $this->m_envs);
      $name = $user->getValue("name");

      $this->m_userSet[$name] = $user;
    }
  }

  /*
    getUserByID: Return a user object by a given ID.
    input: (int) id: database id of the User.
    output: (CDataUser) object, or NULL if not found.
  */
  public function getUserByID($id)
  {
    $user = new CDataUser($id, true, $this->m_dbConn);
    if(!$user->isValid())
      return NULL;

    return $user;
  }
}
?>