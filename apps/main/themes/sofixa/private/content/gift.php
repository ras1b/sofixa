<?php
  if (!isset($_SESSION["login"])) {
    go("/login");
  }
  if (get("action") == 'use' && get("id")) {
    $productGift = $db->prepare("SELECT * FROM ProductGifts WHERE name = ?");
    $productGift->execute(array(get("id")));
    $readProductGift = $productGift->fetch();
  }
?>
<section class="section credit-section">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><?php e__('Home') ?></a></li>
            <?php if (get("target") == 'gift'): ?>
              <?php if (get("action") == 'coupon'): ?>
                <li class="breadcrumb-item active" aria-current="page"><?php e__('Gift') ?></li>
              <?php elseif (get("action") == 'use'): ?>
                <li class="breadcrumb-item"><a href="/gift"><?php e__('Gift') ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo (($productGift->rowCount() > 0) ? $readProductGift["name"] : t__('Not Found!')); ?></li>
              <?php else: ?>
                <?php go("/404"); ?>
              <?php endif; ?>
            <?php else: ?>
              <?php go("/404"); ?>
            <?php endif; ?>
          </ol>
        </nav>
      </div>
      <div class="col-md-8">
        <?php if (get("target") == 'gift'): ?>
          <?php if (get("action") == 'coupon'): ?>
            <?php
              require_once(__ROOT__."/apps/main/private/packages/class/csrf/csrf.php");
              $csrf = new CSRF('csrf-sessions', 'csrf-token');
              if (isset($_POST["useGiftCoupon"])) {
                $productGift = $db->prepare("SELECT * FROM ProductGifts WHERE name = ?");
                $productGift->execute(array(post("giftName")));
                $readProductGift = $productGift->fetch();
                $productGiftsHistory = $db->prepare("SELECT * FROM ProductGiftsHistory WHERE giftID = ?");
                $productGiftsHistory->execute(array($readProductGift["id"]));
                $myProductGiftsHistory = $db->prepare("SELECT * FROM ProductGiftsHistory WHERE accountID = ? AND giftID = ?");
                $myProductGiftsHistory->execute(array($readAccount["id"], $readProductGift["id"]));
                if (!$csrf->validate('useGiftCoupon')) {
                  echo alertError(t__('Something went wrong! Please try again later.'));
                }
                else if (post("giftName") == null ) {
                  echo alertError(t__('Please fill all the fields!'));
                }
                else if ($productGift->rowCount() == 0) {
                  echo alertError(t__('Gift not found!'));
                }
                else if ($myProductGiftsHistory->rowCount() > 0) {
                  echo alertError(t__('You already used this code!'));
                }
                else if ($readProductGift["expiryDate"] < date("Y-m-d H:i:s") && $readProductGift["expiryDate"] != '1000-01-01 00:00:00') {
                  echo alertError(t__('This code has expired!'));
                }
                else if ($readProductGift["piece"] <= $productGiftsHistory->rowCount() && $readProductGift["piece"] != 0) {
                  echo alertError(t__('This code has expired!'));
                }
                else {
                  go('/gift/'.$readProductGift["name"]);
                }
              }
            ?>
            <div class="card">
              <div class="card-header">
                <?php e__('Get the Gift') ?>
              </div>
              <div class="card-body">
                <form action="" method="post">
                  <div class="form-group row">
                    <label for="inputGiftName" class="col-sm-2 col-form-label"><?php e__('Code') ?>:</label>
                    <div class="col-sm-10">
                      <input type="text" id="inputGiftName" class="form-control" name="giftName" placeholder="<?php e__('Enter the gift code.') ?>">
                    </div>
                  </div>
                  <?php echo $csrf->input('useGiftCoupon'); ?>
                  <div class="clearfix">
                    <div class="float-right">
                      <button type="submit" class="btn btn-rounded btn-success" name="useGiftCoupon"><?php e__('Get the Gift') ?></button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          <?php elseif (get("action") == 'use' && get("id")): ?>
            <?php
              require_once(__ROOT__."/apps/main/private/packages/class/csrf/csrf.php");
              $csrf = new CSRF('csrf-sessions', 'csrf-token');
              if (isset($_POST["getGift"])) {
                $productGiftsHistory = $db->prepare("SELECT * FROM ProductGiftsHistory WHERE giftID = ?");
                $productGiftsHistory->execute(array($readProductGift["id"]));
                $myProductGiftsHistory = $db->prepare("SELECT * FROM ProductGiftsHistory WHERE accountID = ? AND giftID = ?");
                $myProductGiftsHistory->execute(array($readAccount["id"], $readProductGift["id"]));
                if (!$csrf->validate('getGift')) {
                  echo alertError(t__('Something went wrong! Please try again later.'));
                }
                else if (get("id") == null ) {
                  echo alertError(t__('Please fill all the fields!'));
                }
                else if ($productGift->rowCount() == 0) {
                  echo alertError(t__('Gift not found!'));
                }
                else if ($myProductGiftsHistory->rowCount() > 0) {
                  echo alertError(t__('You already used this code!'));
                }
                else if ($readProductGift["expiryDate"] < date("Y-m-d H:i:s") && $readProductGift["expiryDate"] != '1000-01-01 00:00:00') {
                  echo alertError(t__('This code has expired!'));
                }
                else if ($readProductGift["piece"] <= $productGiftsHistory->rowCount() && $readProductGift["piece"] != 0) {
                  echo alertError(t__('This code has expired!'));
                }
                else {
                  if ($readProductGift["giftType"] == 1) {
                    $insertChests = $db->prepare("INSERT INTO Chests (accountID, productID, status, creationDate) VALUES (?, ?, ?, ?)");
                    $insertChests->execute(array($readAccount["id"], $readProductGift["gift"], 0, date("Y-m-d H:i:s")));
                  }
                  else {
                    $updateAccount =$db->prepare("UPDATE Accounts SET credit = ? WHERE id = ?");
                    $updateAccount->execute(array($readAccount["credit"]+$readProductGift["gift"], $readAccount["id"]));
                    $insertCreditHistory = $db->prepare("INSERT INTO CreditHistory (accountID, paymentID, paymentStatus, type, price, earnings, creationDate) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $insertCreditHistory->execute(array($readAccount["id"], 0, 1, 4, $readProductGift["gift"], 0, date("Y-m-d H:i:s")));
                  }

                  $insertProductGiftsHistory = $db->prepare("INSERT INTO ProductGiftsHistory (accountID, giftID, creationDate) VALUES (?, ?, ?)");
                  $insertProductGiftsHistory->execute(array($readAccount["id"], $readProductGift["id"], date("Y-m-d H:i:s")));
                  echo alertSuccess(t__('The gift has been successfully added to your chest!'));
                }
              }
            ?>
            <?php if ($productGift->rowCount() > 0): ?>
              <div class="card">
                <div class="card-header">
                  <?php e__('Get the Gift') ?>
                </div>
                <div class="card-body">
                  <div class="row">
                    <?php if ($readProductGift["giftType"] == 1): ?>
                      <?php
                        $product = $db->prepare("SELECT P.*, S.ip as serverIP, S.name as serverName FROM Products P INNER JOIN Servers S ON P.serverID = S.id WHERE P.id = ?");
                        $product->execute(array($readProductGift["gift"]));
                        $readProduct = $product->fetch();
                      ?>
                      <div class="col-md-12">
                        <div class="title background mt-0"><span><?php e__('Product Details') ?></span></div>
                      </div>
                      <div class="col-4">
                        <div class="sfx-store-back-of-photo">
                          <img class="lazyload w-100 p-3 sfx-br-25" src="/apps/main/public/assets/img/store/products/<?php echo $readProduct["imageID"].'.'.$readProduct["imageType"]; ?>" alt="<?php echo $serverName." Ürün - ".$readProduct["name"]." Satın Al"; ?>">
                        </div>
                      </div>
                      <div class="col-8">
                        <div class="row mb-1">
                          <span class="col-sm-4 font-weight-bold"><?php e__('Name') ?>:</span>
                          <span class="col-sm-8"><?php echo $readProduct["name"]; ?></span>
                        </div>
                        <div class="row mb-1">
                          <span class="col-sm-4 font-weight-bold"><?php e__('Server') ?>:</span>
                          <span class="col-sm-8"><?php echo $readProduct["serverName"]; ?></span>
                        </div>
                        <div class="row mb-1">
                          <span class="col-sm-4 font-weight-bold"><?php e__('Category') ?>:</span>
                          <span class="col-sm-8">
                            <?php if ($readProduct["categoryID"] == 0): ?>
                              -
                            <?php else : ?>
                              <?php
                                $productCategory = $db->prepare("SELECT name FROM ProductCategories WHERE id = ?");
                                $productCategory->execute(array($readProduct["categoryID"]));
                                $readProductCategory = $productCategory->fetch();
                              ?>
                              <?php if ($productCategory->rowCount() > 0): ?>
                                <?php echo $readProductCategory["name"]; ?>
                              <?php else : ?>
                                -
                              <?php endif; ?>
                            <?php endif; ?>
                          </span>
                        </div>
                        <div class="row mb-1">
                          <span class="col-sm-4 font-weight-bold"><?php e__('Price') ?>:</span>
                          <span class="col-sm-8 text-success">
                            <?php e__('Free') ?>
                          </span>
                        </div>
                        <div class="row mb-1">
                          <span class="col-sm-4 font-weight-bold"><?php e__('Duration') ?>:</span>
                          <span class="col-sm-8">
                            <?php if ($readProduct["duration"] == 0): ?>
                              <?php e__('Unlimited') ?>
                            <?php elseif ($readProduct["duration"] == -1): ?>
                              <?php e__('One-Time') ?>
                            <?php else : ?>
                              <?php e__('%day% day(s)', ['%day%', $readProduct["duration"]]) ?>
                            <?php endif; ?>
                          </span>
                        </div>
                        <div class="mt-4">
                          <form action="" method="post">
                            <?php echo $csrf->input('getGift'); ?>
                            <button type="submit" class="btn btn-banner-bg w-100" name="getGift"><?php e__('Get the Gift') ?></button>
                          </form>
                        </div>
                      </div>
                    <?php else: ?>
                      <div class="col-md-12">
                        <form action="" method="post">
                          <div class="form-group">
                            <p><?php e__('When you click the "Get the Gift" button, <strong>%credit% credit(s)</strong> will be given to your account.', ['%credit%', $readProductGift["gift"]]) ?></p>
                          </div>
                          <?php echo $csrf->input('getGift'); ?>
                          <div class="clearfix">
                            <div class="float-right">
                              <button type="submit" class="btn btn-success btn-rounded w-100" name="getGift"><?php e__('Get the Gift') ?></button>
                            </div>
                          </div>
                        </form>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php else: ?>
              <?php echo alertError(t__('Gift not found!')); ?>
            <?php endif; ?>
          <?php else: ?>
            <?php go("/404"); ?>
          <?php endif; ?>
        <?php else: ?>
          <?php go('/404'); ?>
        <?php endif; ?>
      </div>
      <div class="col-md-4">
        <div class="row">
          <div class="col-md-12">
            <?php
              $productGiftsHistory = $db->prepare("SELECT PGH.*, PG.name as giftName FROM ProductGiftsHistory PGH INNER JOIN ProductGifts PG ON PGH.giftID = PG.id WHERE PGH.accountID = ? ORDER by PGH.id DESC LIMIT 5");
              $productGiftsHistory->execute(array($readAccount["id"]));
            ?>
            <?php if ($productGiftsHistory->rowCount() > 0): ?>
              <div class="card mb-3">
                <div class="card-header">
                  <div class="row">
                    <div class="col">
                      <span><?php e__('Gift History') ?></span>
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
                          <th class="text-center"><?php e__('Code') ?></th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($productGiftsHistory as $productGiftsHistory): ?>
                          <tr>
                            <td class="text-center">
                              <img class="rounded-circle" src="https://minotar.net/avatar/<?php echo $readAccount["realname"]; ?>/20.png" alt="<?php echo $serverName." Oyuncu - ".$readAccount["realname"]; ?>">
                            </td>
                            <td>
                              <?php echo $readAccount["realname"]; ?>
                              <?php echo verifiedCircle($readAccount["permission"]); ?>
                            </td>
                            <td class="text-center"><?php echo $productGiftsHistory["giftName"]; ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            <?php else : ?>
              <?php echo alertError(t__('History not found!')); ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
