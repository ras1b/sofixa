<?php
if (!get("server")) {
  $firstServer = $db->query("SELECT serverSlug FROM Leaderboards ORDER BY id DESC LIMIT 1");
  $readFirstServer = $firstServer->fetch();
  if ($firstServer->rowCount() > 0) {
    go("/leaderboards/".$readFirstServer["serverSlug"]);
  }
}

require_once(__ROOT__.'/apps/main/private/packages/class/extraresources/extraresources.php');
$extraResourcesJS = new ExtraResources('js');
$extraResourcesJS->addResource('/apps/main/themes/sofixa/public/assets/js/loader.js');
$extraResourcesJS->addResource('/apps/main/themes/sofixa/public/assets/js/leaderboards.js');

$leaderboards = $db->prepare("SELECT * FROM Leaderboards WHERE serverSlug = ?");
$leaderboards->execute(array(get("server")));
$readLeaderboards = $leaderboards->fetch();
?>
<section class="section leaderboards-section">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><?php e__('Home') ?></a></li>
            <?php if (get("server")): ?>
              <li class="breadcrumb-item"><a href="/leaderboards"><?php e__('Leaderboards') ?></a></li>
              <?php if ($leaderboards->rowCount() > 0): ?>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $readLeaderboards["serverName"]; ?></li>
              <?php endif; ?>
            <?php else: ?>
              <li class="breadcrumb-item active" aria-current="page"><?php e__('Leaderboards') ?></li>
            <?php endif; ?>
          </ol>
        </nav>
      </div>
      <div class="col-md-3">
        <?php $leaderboardServers = $db->query("SELECT serverName, serverSlug FROM Leaderboards ORDER BY id DESC"); ?>
        <?php if ($leaderboardServers->rowCount() > 0): ?>
          <div class="card">
            <div class="card-header">
              <?php e__('Servers') ?>
            </div>
            <div class="card-body p-0">
              <ul class="list-group list-group-flush">
                <?php foreach ($leaderboardServers as $readLeaderboardServers): ?>
                  <li class="list-group-item <?php echo (($readLeaderboardServers["serverSlug"] == get("server")) ? "active" : null); ?>">
                    <a href="/leaderboards/<?php echo $readLeaderboardServers["serverSlug"]; ?>">
                      <?php echo $readLeaderboardServers["serverName"]; ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        <?php else: ?>
          <?php echo alertError(t__('Server not found!')); ?>
        <?php endif; ?>
      </div>

      <div class="col-md-9">
        <?php if ($leaderboards->rowCount() > 0): ?>
          <?php
          $mysqlTable       = $readLeaderboards["mysqlTable"];
          $sorter           = $readLeaderboards["sorter"];
          $dataLimit        = $readLeaderboards["dataLimit"];
          $usernameColumn   = $readLeaderboards["usernameColumn"];

          $tableData          = $readLeaderboards["tableData"];
          $tableTitles        = $readLeaderboards["tableTitles"];
          $tableTitlesArray   = explode(",", $tableTitles);
          $tableDataArray     = explode(",", $tableData);

          if ($readLeaderboards["mysqlServer"] == '0') {
            $leaderboard = $db->prepare("SELECT $usernameColumn, $tableData FROM $mysqlTable ORDER BY $sorter DESC LIMIT $dataLimit");
            $leaderboard->execute();
          }
          else {
            try {
              $newDB = new PDO("mysql:host=".$readLeaderboards["mysqlServer"]."; port=".$readLeaderboards["mysqlPort"]."; dbname=".$readLeaderboards["mysqlDatabase"]."; charset=utf8", $readLeaderboards["mysqlUsername"], $readLeaderboards["mysqlPassword"]);
            }
            catch (PDOException $e) {
              die("<strong>MySQL error:</strong> ".utf8_encode($e->getMessage()));
            }
            $leaderboard = $newDB->prepare("SELECT $usernameColumn, $tableData FROM $mysqlTable ORDER BY $sorter DESC LIMIT $dataLimit");
            $leaderboard->execute();
          }
          ?>
          <?php if ($leaderboard->rowCount() > 0): ?>
            <div id="searchAlert" style="display: none;"></div>
            <div class="card">
              <div class="card-header">
                <?php e__('Leaderboard') ?>
              </div>
              <div id="loader" class="card-body p-0 is-loading">
                <div id="spinner">
                  <div class="spinner-border text-default" role="status">
                    <span class="sr-only">-/-</span>
                  </div>
                </div>
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th class="text-center" style="width: 40px;"><?php e__('Rank') ?></th>
                        <th class="text-center" style="width: 20px;">#</th>
                        <th><?php e__('Username') ?></th>
                        <?php foreach ($tableTitlesArray as $readTableTitles): ?>
                          <th class="text-center"><?php echo $readTableTitles; ?></th>
                        <?php endforeach; ?>
                      </tr>
                    </thead>
                    <tbody id="searchResult" style="display: none;"></tbody>
                    <tbody id="leaderboards">
                      <?php $rank = 1; ?>
                      <?php foreach ($leaderboard as $readLeaderboard): ?>
                        <tr <?php echo (isset($_SESSION["login"]) && ($readLeaderboard[$usernameColumn] == $readAccount["realname"])) ? 'class="active"':null; ?>>
                          <td class="text-center" style="width: 40px;">
                            <?php if ($rank == 1): ?>
                              <strong class="text-success">1</strong>
                            <?php elseif ($rank == 2): ?>
                              <strong class="text-warning">2</strong>
                            <?php elseif ($rank == 3): ?>
                              <strong class="text-danger">3</strong>
                            <?php else: ?>
                              <?php echo $rank; ?>
                            <?php endif; ?>
                          </td>
                          <td class="text-center" style="width: 20px;">
                            <?php echo minecraftHead($readSettings["avatarAPI"], $readLeaderboard[$usernameColumn], 20); ?>
                          </td>
                          <td>
                            <a rel="external" href="/player/<?php echo $readLeaderboard[$usernameColumn]; ?>"><?php echo $readLeaderboard[$usernameColumn]; ?></a>
                          </td>
                          <?php foreach ($tableDataArray as $readTableData): ?>
                            <td class="text-center"><?php echo $readLeaderboard[$readTableData]; ?></td>
                          <?php endforeach; ?>
                        </tr>
                        <?php $rank++; ?>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <script type="text/javascript">
              var leaderboardsID = <?php echo $readLeaderboards["id"]; ?>;
            </script>
          <?php else: ?>
            <?php echo alertError(t__('No ranking data found for this server!')); ?>
          <?php endif; ?>
        <?php else: ?>
          <?php echo alertError(t__('Server not found!')); ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
