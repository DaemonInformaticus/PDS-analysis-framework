<?php

class CLeftMenu
{
  private $m_arrMenuItems;    // Array of menu items to show.
  // private $m_accessLevel;
  private $m_activeItem;
  private $m_accessElements;  // Object of type CDataAccess

  function __construct($access, $active = 0)
  {
    $this->m_accessElements = $access;
    $this->m_activeItem     = $active;

    $this->initMenuItems();
  }

  /*
    showLeftMenu: Draw a menu component on the spot.
  */
  public function showLeftMenu()
  {
    ?>
    <table border="1" width="100%">
      <?php
      for($i = 0; $i < count($this->m_arrMenuItems); $i++)
      {
        $item = $this->m_arrMenuItems[$i];
        $title = $item[0];
        $link = $item[1];
        if($link == "")
          print("<tr><td>$title</td></tr>\n");
         else
          print("<tr><td><a href=\"$link\">$title</td></tr>\n");
      }
      ?>
    </table>
    <?php
  }


  /*
    initMenuItems: Build a list of menu items for this user's interface.
  */
  private function initMenuItems()
  {
    $this->m_arrMenuItems = array();
    // print_r($this->m_accessElements);

    // No access elements: No Login. Login / register
    if($this->m_accessElements == NULL)
    {
      array_push($this->m_arrMenuItems, array("Login",    "login.php"));
      array_push($this->m_arrMenuItems, array("Register", "register.php"));
    }

    // There's a set of access elements: Basic functions are accessable by everybody:
    if($this->m_accessElements != NULL)
    {
      //   array_push($this->m_arrMenuItems, array("Option 1", "page1.php"));
      // for every further access element: Check with the access elements dataset first.
      if($this->m_accessElements->hasAccessTo("adm-groupman"))
      {
        array_push($this->m_arrMenuItems, array("Group management", "adm_groups.php"));
      }
      if($this->m_accessElements->hasAccessTo("adm-userman"))
      {
        array_push($this->m_arrMenuItems, array("User management", "adm_users.php"));
      }

      // There's a set of access elements: At the end of the menu: Show a logout button:

      array_push($this->m_arrMenuItems, array("Logout", "logout.php"));
    }
  }
}
?>