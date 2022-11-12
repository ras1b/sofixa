<?php
if (!isset($_SESSION["login"])) {
  go("/giris-yap");
}
require_once(__ROOT__.'/apps/main/private/packages/class/extraresources/extraresources.php');
$extraResourcesJS = new ExtraResources('js');
$extraResourcesJS->addResource('/apps/main/themes/sofixa/public/assets/js/chest.js');
?>
<section class="section credit-section">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><?php e__('Home') ?></a></li>
            <?php if (get("target") == 'chest'): ?>
              <?php if (get("action") == 'getAll'): ?>
                <li class="breadcrumb-item active" aria-current="page"><?php e__('Chest') ?></li>
              <?php elseif (get("action") == 'gift'): ?>
                <li class="breadcrumb-item"><a href="/chest"><?php e__('Chest') ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php e__('Send Gift') ?></li>
              <?php else: ?>
                <li class="breadcrumb-item active" aria-current="page"><?php e__('Error!') ?></li>
              <?php endif; ?>
            <?php else: ?>
              <li class="breadcrumb-item active" aria-current="page"><?php e__('Error!') ?></li>
            <?php endif; ?>
          </ol>
        </nav>
      </div>
      <div class="col-md-8">
        <?php if (get("target") == 'chest'): ?>
          <?php if (get("action") == 'getAll'): ?>
            <?php
            $chests = $db->prepare("SELECT C.*, P.name as productName, S.name as serverName, P.imageID as imageID, P.imageType as imageType FROM Chests C INNER JOIN Products P ON C.productID = P.id INNER JOIN Servers S ON P.serverID = S.id WHERE C.accountID = ? AND C.status = ? ORDER BY C.id DESC");
            $chests->execute(array($readAccount["id"], 0));
            ?>
            <?php if ($chests->rowCount() > 0): ?>
              <div class="card">
                <div class="card-header">
                  <?php e__('Chest') ?> (<?php echo $chestCount; ?>)
                </div>

                <div class="card-body p-0">
                  <div class="col-md-12 d-flex flex-wrap:wrap">
                    <?php foreach ($chests as $readChests): ?>
                      <div class="col-md-6">
                        <div class="card mt-4 sfx-chest-card">
                          <div class="card-body">
                           <div class="w-100 text-center sfx-store-back-of-photo">
                             <img class="w-50 m-2 sfx-br-10" src="/apps/main/public/assets/img/store/products/<?php echo $readChests["imageID"].".".$readChests["imageType"] ?>" alt="">
                           </div>
                           <div class="w-100 mt-2">
                            <p class="text-center chest-title"><?php echo $readChests["productName"]; ?></p> 
                          </div>
                          <div class="w-100 text-center mt-2">
                           <button class="btn btn-banner-bg deliverButton" data-chest="<?php echo $readChests["id"]; ?>"><?php e__('Take Delivery') ?></button>
                           <?php if ($readSettings["giftStatus"] == 1): ?>
                             <a class="btn btn-banner-bg" href="/chest/gift/<?php echo $readChests["id"]; ?>"><?php e__('Send as Gift') ?></a>
                           <?php endif; ?>
                         </div>

                       </div>
                     </div>
                   </div>
                 <?php endforeach; ?>
               </div>
                </div>
              </div>
            <?php else: ?>
              <?php echo alertError(t__('No items found in your chest!')); ?>
            <?php endif; ?>
          <?php elseif (get("action") == 'gift' && $readSettings["giftStatus"] == 1): ?>
            <?php
            $chest = $db->prepare("SELECT C.*, P.name as productName FROM Chests C INNER JOIN Products P ON C.productID = P.id WHERE C.accountID = ? AND C.id = ? AND C.status = ?");
            $chest->execute(array($readAccount["id"], get("id"), 0));
            $readChest = $chest->fetch();
            ?>
            <?php if ($chest->rowCount() > 0): ?>
              <?php
              require_once(__ROOT__."/apps/main/private/packages/class/csrf/csrf.php");
              $csrf = new CSRF('csrf-sessions', 'csrf-token');
              if (isset($_POST["sendGift"])) {
                if (!$csrf->validate('sendGift')) {
                  echo alertError(t__('Something went wrong! Please try again later.'));
                }
                else if (post("username") == null) {
                  echo alertError(t__('Please fill all the fields!'));
                }
                  //else if ($readAccount["realname"] == post("username")) {
                else if (strtolower($readAccount["realname"]) == strtolower(post("username"))) {
                  echo alertError(t__("You can't send yourself a gift!"));
                }
                else {
                  $checkAccount = $db->prepare("SELECT id FROM Accounts WHERE realname = ?");
                  $checkAccount->execute(array(post("username")));
                  $readCheckedAccount = $checkAccount->fetch();
                  if ($checkAccount->rowCount() > 0) {
                    $updateChest = $db->prepare("UPDATE Chests SET accountID = ? WHERE id = ?");
                    $updateChest->execute(array($readCheckedAccount["id"], $readChest["id"]));

                    $insertChestHistory = $db->prepare("INSERT INTO ChestsHistory (accountID, chestID, type, creationDate) VALUES (?, ?, ?, ?)");
                    $insertChestHistory->execute(array($readAccount["id"], $readChest["id"], 2, date("Y-m-d H:i:s")));
                    $insertChestHistory->execute(array($readCheckedAccount["id"], $readChest["id"], 3, date("Y-m-d H:i:s")));

                    echo alertSuccess('Gift has been successfully sent to %username%', ['%username%', post("username")]);
                  }
                  else {
                    echo alertError(t__('User not found!'));
                  }
                }
              }
              ?>
              <div class="card">
                <div class="card-header">
                  <?php e__('Send Gift') ?>
                </div>
                <div class="card-body">
                  <form action="" method="post">
                    <div class="form-group row">
                      <label for="inputProduct" class="col-sm-2 col-form-label"><?php e__('Product') ?>:</label>
                      <div class="col-sm-10">
                        <input type="text" id="inputProduct" class="form-control" value="<?php echo $readChest["productName"]; ?>" readonly>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="inputUsername" class="col-sm-2 col-form-label"><?php e__('Username') ?>:</label>
                      <div class="col-sm-10">
                        <input type="text" id="inputUsername" class="form-control" name="username" placeholder="<?php e__('Enter the username') ?>">
                      </div>
                    </div>
                    <?php echo $csrf->input('sendGift'); ?>
                    <div class="clearfix">
                      <div class="float-right">
                        <button type="submit" class="btn btn-rounded btn-success" name="sendGift" onclick="return confirm('<?php e__('Are you sure you want to send the gift to this player?') ?>')"><?php e__('Send') ?></button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            <?php else: ?>
              <?php echo alertError(t__('Chest item not found!')); ?>
            <?php endif; ?>
          <?php else: ?>
            <?php go("/404"); ?>
          <?php endif; ?>
        <?php else: ?>
          <?php go("/404"); ?>
        <?php endif; ?>
      </div>

      <div class="col-md-4">
        <div class="row">
          <div class="col-md-12">
            <?php
            $chestsHistory = $db->prepare("SELECT CH.*, P.name as productName FROM ChestsHistory CH INNER JOIN Chests C ON CH.chestID = C.id INNER JOIN Products P ON C.productID = P.id WHERE CH.accountID = ? ORDER BY CH.id DESC LIMIT 5");
            $chestsHistory->execute(array($readAccount["id"]));
            ?>
            <?php if ($chestsHistory->rowCount() > 0): ?>
              <div class="card">
                <div class="card-header">
                  <div class="row">
                    <div class="col">
                      <span><?php e__('Chest History') ?></span>
                    </div>
                    <div class="col-auto">
                      <a class="text-white" href="/profile"><?php e__('View All') ?></a>
                    </div>
                  </div>
                </div>
                <div class="card-body p-0">
                  <div class="table-responsive">
                    <table class="table table-hover">
                      <thead>
                        <tr>
                          <th class="text-center">#</th>
                          <th><?php e__('Username') ?></th>
                          <th class="text-center"><?php e__('Product') ?></th>
                          <th class="text-center"><?php e__('Type') ?></th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($chestsHistory as $readChestsHistory): ?>
                          <tr>
                            <td class="text-center">
                              <img class="rounded-circle" src="https://minotar.net/avatar/<?php echo $readAccount["realname"]; ?>/20.png" alt="<?php echo $serverName." Oyuncu - ".$readAccount["realname"]; ?>">
                            </td>
                            <td>
                              <?php echo $readAccount["realname"]; ?>
                              <?php echo verifiedCircle($readAccount["permission"]); ?>
                            </td>
                            <td class="text-center"><?php echo $readChestsHistory["productName"]; ?></td>
                            <td class="text-center">
                              <?php if ($readChestsHistory["type"] == 1): ?>
                                <i class="fa fa-check" data-toggle="tooltip" data-placement="top" title="<?php e__('Delivery') ?>"></i>
                              <?php elseif ($readChestsHistory["type"] == 2): ?>
                                <i class="fa fa-gift" data-toggle="tooltip" data-placement="top" title="<?php e__('Gift (Giver)') ?>"></i>
                              <?php elseif ($readChestsHistory["type"] == 3): ?>
                                <i class="fa fa-gift" data-toggle="tooltip" data-placement="top" title="<?php e__('Gift (Receiver)') ?>"></i>
                              <?php else: ?>
                                <i class="fa fa-check"></i>
                              <?php endif; ?>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            <?php else: ?>
              <?php echo alertError(t__('History not found!')); ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>