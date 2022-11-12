<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
  <?php require_once(__ROOT__."/apps/main/themes/sofixa/private/layouts/head.php"); ?>
</head>
<body style="<?php echo (($readSettings["preloaderStatus"] == 1) ? 'overflow: hidden;' : 'overflow: auto;'); ?>">
<?php require_once(__ROOT__."/apps/main/themes/sofixa/private/layouts/header.php"); ?>
<main class="main" role="main">
  <?php include $routeFile; ?>
</main>
<?php require_once(__ROOT__."/apps/main/themes/sofixa/private/layouts/footer.php"); ?>
<?php require_once(__ROOT__."/apps/main/themes/sofixa/private/layouts/scripts.php"); ?>
</body>
</html>
<?php ob_end_flush(); ?>
