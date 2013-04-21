<?php
include_once("CLeftMenu.php");
include_once("incl/class/logic/CLogicLanguage.php");

class CPage
{
  public    $m_cid;       // cid, commonly used in a webpage ro display case's in a switch.

  protected $m_envs;      // Key / value array with environment variables for the website.
  protected $m_language;  // CLogicLanguage object
  private   $m_arrErrors;
  private   $m_pCMSContentMenu;

  function __construct($envs, $bLoadLanguage = true)
  {
    $this->m_envs       = $envs;
    $this->m_arrErrors  = array();

    if(isset($_GET['cid']))
      $this->m_cid = $_GET['cid'];
     else
      $this->m_cid = 0;


    if($bLoadLanguage == true)
    {
      // First try and see if the user selected a new language.
      if(!$this->resolveLanguageSel())
      {
        // If not, select a language from a different source.
        $this->loadLanguage();
      }
    }
  }

  // public function closeDBConn() { return mysql_close($this->m_dbConn); }

  public function getTitle() { return $this->m_envs['title']; }

  public function getMD5Salt() { return $this->m_envs['md5salt']; }

  public function getLanguageID() { return $this->m_language->getLanguageID(); }

  public function translate($key)
  {
    // print("CPage::translate: ". $this->m_language->translate($key)."<br>\n" );
    return $this->m_language->translate($key);
  }

  /*
    showHeader: Show the framework for the page, and include a menu structure somewhere.
      input:  $objAccess: reference to a CDataUserToAccess object.
              $meta:      A set of tags that go into the 'head' of the page.
              $script:    Any (java) script elements that go into the head of a page.
  */
  public function showHeader($objAccess, $meta = "", $script = "")
  {
    $title      = "<title>".$this->m_envs['title']."</title>";

    if(!empty($this->m_envs['background']))
      $background = "background=\"".$this->m_envs['background']."\"";
     else
      $background = "";

    if(empty($this->m_envs['bgcolor']))
      $this->m_envs['bgcolor'] = "#ffffff";

    $bgcolor    = $this->m_envs['bgcolor'];

    if(empty($this->m_envs['banner']))
      $banner = "Banner";
     else
      $banner = "<img src=\"".$this->m_envs['banner']."\">";

    $menu = new CLeftMenu($objAccess);

    ?>
    <html>
      <head>
        <?php
        print("$title\n");
        print("$meta\n");
        print("$script\n");
        ?>
      </head>
      <?php
      print("<body $background $bgcolor>");
      ?>
        <table border="1" height="100%" width="100%">
          <tr height="10%"><td valign="top"><a href="index.php"><?php print("$banner"); ?></a></td></tr>
          <tr>
            <td width="15%">
              <table border="1" height="100%" width="100%">
                <tr>
                  <td width="10%" valign="top">
                    <?php
                    $menu->showLeftMenu();
                    ?>
                  </td>
                  <td valign="top">
                    <!-- Main -->
                    <?php

  }

  /*
    showHeaderNoMenu: Show header without a menu.
  */
  public function showHeaderNoMenu()
  {
    $title      = "<title>".$this->m_envs['title']."</title>";

    if(!empty($this->m_envs['background']))
      $background = "background=\"".$this->m_envs['background']."\"";
     else
      $background = "";

    if(empty($this->m_envs['bgcolor']))
      $this->m_envs['bgcolor'] = "#ffffff";

    $bgcolor    = $this->m_envs['bgcolor'];

    if(empty($this->m_envs['banner']))
      $banner = "Banner";
     else
      $banner = "<img src=\"".$this->m_envs['banner']."\">";

    ?>
    <html>
      <head>
        <?php
        print("$title\n");
        ?>
      </head>
      <?php
      print("<body $background $bgcolor>");
      ?>
        <table border="1" height="100%" width="100%">
          <tr height="10%"><td valign="top"><a href="index.php"><?php print("$banner"); ?></a></td></tr>
          <tr>
            <td valign="top">
              <!-- Main -->

              <?php
  }

  public function showFooterNoMenu()
  {
    ?>
              <!-- End of main-->
            </td>
          </tr>
        </table>
      </body>
    </html>
    <?php
  }
  public function showFooter()
  {
    ?>
                  <!-- End of main-->
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </body>
    </html>
    <?php
  }

  /*
    showAccessDenied: Message that is shown when a user has no access to a given page.
  */
  public function showAccessDenied()
  {
    print("<h2>Access Denied!</h2>");
  }

  private function resolveLanguageSel()
  {
    if(isset($_GET['lang']))
    {
      $languageID = addslashes($_GET['lang']);
      setcookie("lang", $languageID, 0, "/");
      $this->m_language = new CLogicLanguage($this->m_envs, $languageID);

      return true;
    }

    return false;
  }

  /*
    loadLanguage: Figure out which language to load, and instantiate 'CLogicLanguage' accordingly.
  */
  private function loadLanguage()
  {
    // On an unmoderated page, load the standard language:
    if(isset($_COOKIE['lang']))
      $languageID = $_COOKIE['lang'];
     else
      $languageID = $this->m_envs['default_language_id'];
    $this->m_language = new CLogicLanguage($this->m_envs, $languageID);
  }

  protected function addError($error)
  {
    array_push($this->m_arrErrors, $error);
  }

  public function getErrors()
  {
    return $this->m_arrErrors;
  }
}
?>