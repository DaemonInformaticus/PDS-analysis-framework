<?php
class CLogic
{
  protected $m_envs;
  protected $m_arrErrors;

  function __construct($envs)
  {
    $this->m_envs       = $envs;
    $this->m_arrErrors  = array();
  }

  public function getErrors()
  {
    return $this->m_arrErrors;
  }

  protected function addError($strError)
  {
    array_push($this->m_arrErrors, $strError);
  }

  public function getNameByID($userID)
  {
    $pUser = new CDataUser($userID, false, $this->m_envs);
    $name = $pUser->getValue("name");

    return $name;
  }
}
?>