<?php
header('HTTP/1.1 503 Service Temporarily Unavailable');
header('Status: 503 Service Temporarily Unavailable');
header('Retry-After: 300');
?>
<style>
  body {
    background: url(/apps/main/themes/sofixa/public/assets/img/extras/header.gif);
    background-size: cover;
    backdrop-filter: blur(4px) brightness(0.8);
  }
  body:before {
    content: "";
    position: absolute;
    width: 100%;
    height: 100%;
    background: rgba(var(--header-banner-background));
    opacity: 0.3;
    filter: blur(-6vh);
  }
  .header {
    display: none;
  }
  .header-banner {
    display: none;
  }
  .broadcast {
    display: none;
  }
  .footer {
    display: none;
  }
  .error-404-section {
    height: 85vh !important;
  }
</style>
<section class="section error-404-section">
  <div class="container">
    <div class="row">
      <div class="col-md-12 text-center maintenance-header">
        <h1 class="maintenance-header-text"><?php e__('Maintenance') ?></h1>
        <p><?php e__('Our website is currently under maintenance, please try again later!') ?></p>
        <?php if (isset($_SESSION["login"])): ?>
          <?php if ($readAccount["permission"] == 1 || $readAccount["permission"] == 2 || $readAccount["permission"] == 3 || $readAccount["permission"] == 4 || $readAccount["permission"] == 5): ?>
            <a class="btn btn-rounded btn-banner-bg w-25" href="/dashboard"><?php e__('Dashboard') ?></a>
          <?php endif; ?>
        <?php endif; ?>
      </div>
      <?php if (!isset($_SESSION["login"])): ?>
        <div class="w-100 text-center">
          <a class="btn btn-rounded btn-banner-bg w-25" href="/login"><?php e__('Login') ?></a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
