<?php
header('HTTP/1.1 404 Not Found');
header('Status: 404 Not Found');
?>
<section class="section error-404-section">
  <div class="container">
    <div class="row">
      <div class="col-md-12 text-center">
        <h1>404</h1>
        <p><?php e__('Page not found!') ?></p>
        <a class="btn btn-banner-bg" href="/"><?php e__('Home') ?></a>
        <?php if (isset($_SESSION["login"])): ?>
          <?php if ($readAccount["permission"] == 1 || $readAccount["permission"] == 2 || $readAccount["permission"] == 3 || $readAccount["permission"] == 4 || $readAccount["permission"] == 5): ?>
            <a class="btn btn-banner-bg" href="/dashboard"><?php e__('Dashboard') ?></a>
          <?php endif; ?>
        <?php endif; ?>
        <div class="mb-4 sss-header-div mt-3">
          <div class="content-box position-relative">
            <span class="h2 sss-header-text"><?php e__('Servers') ?></span>
          </div>
        </div>
        <?php $servers = $db->query("SELECT * FROM Servers"); ?>
        <div class="row justify-content-center">
          <?php if ($servers->rowCount() > 0): ?>
            <?php foreach ($servers as $readServers): ?>
              <div class="col-md-3">
                <div class="img-card-wrapper">
                  <div class="img-container error-container">
                    <a class="img-card" href="/store/<?php echo $readServers["slug"]; ?>">
                      <img class="card-img-top lazyload" data-src="/apps/main/public/assets/img/servers/<?php echo $readServers["imageID"].'.'.$readServers["imageType"]; ?>" src="/apps/main/public/assets/img/loaders/server.png" alt="<?php echo $serverName." Sunucu - ".$readServers["name"]; ?>">
                    </a>
                    <div class="img-card-center">
                      <h5 class="mb-0">
                        <a class="text-white" href="/store/<?php echo $readServers["slug"]; ?>">
                          <?php echo $readServers["name"]; ?>
                        </a>
                      </h5>
                    </div>
                    <div class="img-card-bottom">
                      <h5 class="mb-0">
                        <a class="btn btn-banner-bg mt-2 w-100" href="/store/<?php echo $readServers["slug"]; ?>">
                          <?php e__('View') ?>
                        </a>
                      </h5>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-md-12">
              <?php echo alertError(t__('No server were found!')); ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
