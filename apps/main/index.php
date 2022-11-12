<?php
  define("__ROOT__", $_SERVER["DOCUMENT_ROOT"]);
  require_once(__ROOT__."/apps/main/private/config/settings.php");
?>
<?php if (preg_match('/^[A-Za-z0-9\.\-]+$/', get("route"))): ?>
  <?php $routeFile = __ROOT__."/apps/main/themes/sofixa/private/content/".get("route").".php"; ?>
  <?php if (file_exists($routeFile)): ?>
    <?php include __ROOT__."/apps/main/themes/sofixa/index.php"; ?>
  <?php else: ?>
    <?php go("/404"); ?>
  <?php endif; ?>
<?php else: ?>
  <?php go("/404"); ?>
<?php endif; ?>
<?php ob_end_flush(); ?>