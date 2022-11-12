<?php
  if (isset($_SESSION["login"])) {
    $deleteAccountSessions = $db->prepare("DELETE FROM AccountSessions WHERE accountID = ? AND loginToken = ? AND creationIP = ?");
    $deleteAccountSessions->execute(array($readAccount["id"], $_SESSION["login"], getIP()));
    removeCookie("rememberMe");
    session_destroy();
  }
  go('/');
?>
