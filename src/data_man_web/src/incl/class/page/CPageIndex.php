<?php
include_once("CPage.php");
include_once("incl/class/logic/CLogicIndex.php");

class CPageIndex extends CPage
{
  private $m_logicIndex;

  function __construct($env)
  {
    CPage::__construct($env);

    $this->m_logicIndex = new CLogicIndex($this->m_envs);
  }

  public function messageOfTheDay()
  {
    $username = $this->m_logicIndex->getUserName();

    if($username == "")
      return "Master, I am here to serve you!";
     else
      return "Master $username, I am here to serve you!";
  }

  public function getUserAccess() { return $this->m_logicIndex->getUserAccess(); }

  public function getUser() { return $this->m_logicIndex->getUser(); }
}

?>