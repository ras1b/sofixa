<?php
  $games = $db->query("SELECT * FROM Games ORDER BY id DESC");

  if (get("action") == "getAll" && $games->rowCount() == 1) {
    go("/games/".$games->fetch()["slug"]);
  }
  if (get("action") == "get" && get("game")) {
    $game = $db->prepare("SELECT * FROM Games WHERE slug = ?");
    $game->execute(array(get("game")));
    $readGame = $game->fetch();
  }
?>
<section class="section page-section">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><?php e__('Home') ?></a></li>
            <?php if (get("action") == "getAll"): ?>
                <li class="breadcrumb-item active" aria-current="page"><?php e__('Games') ?></li>
            <?php elseif (get("action") == "get" && get("game")): ?>
              <li class="breadcrumb-item"><a href="/games"><?php e__('Games') ?></a></li>
              <?php if ($game->rowCount() > 0): ?>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $readGame["title"]; ?></li>
              <?php else: ?>
                <li class="breadcrumb-item active" aria-current="page"><?php e__('Not Found!') ?></li>
              <?php endif; ?>
            <?php else: ?>
              <li class="breadcrumb-item active" aria-current="page"><?php e__('Error!') ?></li>
            <?php endif; ?>
          </ol>
        </nav>
      </div>
    </div>

    <?php if (get("action") == "getAll"): ?>
      <div class="row">
        <?php if ($games->rowCount() > 0): ?>
          <?php foreach ($games as $readGames): ?>
            <div class="col-md-3">
              <div class="img-card-wrapper">
                <div class="img-container games-container">
                  <a class="img-card" href="/games/<?php echo $readGames["slug"]; ?>">
                    <img class="card-img-top lazyload" data-src="/apps/main/public/assets/img/games/<?php echo $readGames["imageID"].'.'.$readGames["imageType"]; ?>" src="/apps/main/public/assets/img/loaders/server.png" alt="<?php echo $serverName." Oyun - ".$readGames["title"]; ?>">
                  </a>
                  <div class="img-card-bottom">
                    <h5 class="mb-0">
                      <a class="text-white" href="/games/<?php echo $readGames["slug"]; ?>">
                        <?php echo $readGames["title"]; ?>
                      </a>
                    </h5>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-md-12">
            <?php echo alertError(t__('No data has been added to the site yet.')); ?>
          </div>
        <?php endif; ?>
      </div>
    <?php elseif (get("action") == "get" && get("game")): ?>
      <div class="row">
        <div class="col-md-3">
          <?php if ($games->rowCount() > 0): ?>
            <div class="card">
              <div class="card-header">
                <?php e__('Games') ?>
              </div>
              <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                  <?php foreach ($games as $readGames): ?>
                    <li class="list-group-item <?php echo ($readGames["slug"] == get("game")) ? "active" : null; ?>">
                      <a href="/games/<?php echo $readGames["slug"]; ?>">
                        <?php echo $readGames["title"]; ?>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          <?php else: ?>
            <?php echo alertError(t__('Game not found!')); ?>
          <?php endif; ?>
        </div>
        <div class="col-md-9">
          <?php if ($game->rowCount() > 0): ?>
            <div class="card">
              <div class="card-header">
                <?php echo $readGame["title"]; ?>
              </div>
              <div class="card-body">
                <?php echo $readGame["content"]; ?>
              </div>
            </div>
          <?php else: ?>
            <?php echo alertError(t__('Game not found!')); ?>
          <?php endif; ?>
        </div>
      </div>
    <?php else: ?>
      <?php go("/404"); ?>
    <?php endif; ?>
  </div>
</section>
