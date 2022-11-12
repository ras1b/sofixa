<?php
$servers = $db->query("SELECT * FROM Servers");

if (get("action") == "getAll") {
  if ($servers->rowCount() == 1) {
    go("/store/".$servers->fetch()["slug"]);
  }
}
if (get("action") == "get") {
  if (get("server")) {
    $thisServer = $db->prepare("SELECT * FROM Servers WHERE slug = ?");
    $thisServer->execute(array(get("server")));
    $readThisServer = $thisServer->fetch();
    if ($thisServer->rowCount() > 0) {
      $categoryID = "0";
      if (get("category")) {
        $thisCategory = $db->prepare("SELECT * FROM ProductCategories WHERE serverID = ? AND slug = ?");
        $thisCategory->execute(array($readThisServer["id"], get("category")));
        $readThisCategory = $thisCategory->fetch();
        if ($thisCategory->rowCount() > 0) {
          $categoryID = $readThisCategory["id"];
        }
      }
      else {
        $_GET["category"] = "0";
        $categoryID = get("category");
      }
      $productCategories = $db->prepare("SELECT * FROM ProductCategories WHERE serverID = ? AND parentID = ?");
      $productCategories->execute(array($readThisServer["id"], $categoryID));
    }
  }

  $discountProducts = explode(",", $readSettings["storeDiscountProducts"]);
  require_once(__ROOT__.'/apps/main/private/packages/class/extraresources/extraresources.php');
  $extraResourcesJS = new ExtraResources('js');
  $extraResourcesJS->addResource('/apps/main/themes/sofixa/public/assets/js/store.js');
}
?>
<section class="section store-section">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><?php e__('Home') ?></a></li>
            <?php if (get("server")): ?>
              <?php if ($thisServer->rowCount() > 0): ?>
                <?php if (get("category") == "0"): ?>
                  <li class="breadcrumb-item"><a href="/store"><?php e__('Store') ?></a></li>
                  <li class="breadcrumb-item active" aria-current="page"><?php echo $readThisServer["name"]; ?></li>
                <?php else: ?>
                  <li class="breadcrumb-item"><a href="/store"><?php e__('Store') ?></a></li>
                  <li class="breadcrumb-item"><a href="/store/<?php echo $readThisServer["slug"]; ?>"><?php echo $readThisServer["name"]; ?></a></li>
                  <li class="breadcrumb-item active" aria-current="page"><?php echo $readThisCategory["name"]; ?></li>
                <?php endif; ?>
              <?php else: ?>
                <li class="breadcrumb-item active" aria-current="page"><?php e__('Store') ?></li>
              <?php endif; ?>
            <?php else: ?>
              <li class="breadcrumb-item active" aria-current="page"><?php e__('Store') ?></li>
            <?php endif; ?>
          </ol>
        </nav>
      </div>
    </div>
    <?php if (get("action") == "getAll"): ?>
      <div class="row justify-content-center">
        <?php if ($servers->rowCount() > 0): ?>
          <?php foreach ($servers as $readServers): ?>
            <div class="col-md-3">
              <div class="img-card-wrapper">
                <div class="img-container">
                  <a class="img-card" href="/store/<?php echo $readServers["slug"]; ?>">
                    <img class="card-img-top lazyload" data-src="/apps/main/public/assets/img/servers/<?php echo $readServers["imageID"].'.'.$readServers["imageType"]; ?>" src="/apps/main/public/assets/img/loaders/server.png" alt="<?php echo $serverName." Sunucu - ".$readServers["name"]; ?>">
                  </a>
                  <div class="img-card-center">
                    <h5 class="mb-0">
                      <a class="text-white" href="/magaza/<?php echo $readServers["slug"]; ?>">
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
    <?php elseif (get("action") == "get" && get("server")): ?>
    <div class="row">
      <div id="modalBox"></div>
      <div class="col-md-12">
        <?php if (get("server") && $thisServer->rowCount() > 0): ?>
        <?php if ($readSettings["topSalesStatus"] == 1): ?>
          <?php
          $topSales = $db->prepare("SELECT P.*, COUNT(*) AS productCount FROM StoreHistory SH INNER JOIN Products P ON SH.productID = P.id WHERE P.serverID = ? AND P.categoryID = ? GROUP BY P.id ORDER BY productCount DESC LIMIT 4");
          $topSales->execute(array($readThisServer["id"], $categoryID));
          ?>
          <div class="w-100 text-right  ">
            <div class="btn btn-banner-bg mb-2">
              <a class="text-white" href="/store">
                <i class="bi bi-arrow-left"></i>
                <?php e__('Servers') ?>
              </a>
            </div>
          </div>
          <?php if ($topSales->rowCount() > 0): ?>
            <div class="card">
              <div class="card-header">
                <?php e__('Bestseller') ?>
              </div>
              <div class="card-body">
                <div class="row store-cards">
                  <?php foreach ($topSales as $readTopSales): ?>
                    <?php $discountedPriceStatus = ($readTopSales["discountedPrice"] != 0 && ($readTopSales["discountExpiryDate"] > date("Y-m-d H:i:s") || $readTopSales["discountExpiryDate"] == '1000-01-01 00:00:00')); ?>
                    <?php $storeDiscountStatus = ($readSettings["storeDiscount"] != 0 && (in_array($readTopSales["id"], $discountProducts) || $readSettings["storeDiscountProducts"] == '0') && ($readSettings["storeDiscountExpiryDate"] > date("Y-m-d H:i:s") || $readSettings["storeDiscountExpiryDate"] == '1000-01-01 00:00:00')); ?>
                    <div class="col-lg-3 col-md-4">
                      <div class="card">
                        <div class="card-body">
                          <div class="store-card">
                            <?php if ($readTopSales["stock"] != -1): ?>
                              <div class="store-card-stock <?php echo ($readTopSales["stock"] == 0) ? "stock-out" : "have-stock"; ?>">
                                <?php if ($readTopSales["stock"] == 0): ?>
                                  <?php e__('Out of Stock!') ?>
                                <?php else : ?>
                                  <?php e__('Limited Stock!') ?>
                                <?php endif; ?>
                              </div>
                            <?php endif; ?>
                            <?php if ($discountedPriceStatus == true || $storeDiscountStatus == true): ?>
                              <?php $discountPercent = (($storeDiscountStatus == true) ? $readSettings["storeDiscount"] : round((($readTopSales["price"]-$readTopSales["discountedPrice"])*100)/($readTopSales["price"]))); ?>
                              <div class="store-card-discount">
                                <span><?php echo $discountPercent; ?>%</span>
                              </div>
                            <?php endif; ?>
                            <div class="sfx-store-back-of-photo">
                              <img class="lazyload w-100 p-3 sfx-br-25" data-src="/apps/main/public/assets/img/store/products/<?php echo $readTopSales["imageID"].'.'.$readTopSales["imageType"]; ?>" src="/apps/main/public/assets/img/loaders/store.png" alt="<?php echo $serverName." Ürün - ".$readTopSales["name"]." Satın Al"; ?>">
                            </div>
                            <div class="row store-card-text">
                              <div class="col">
                                <span><?php echo $readTopSales["name"]; ?></span>
                              </div>
                              <div class="col-auto">
                                <?php if ($discountedPriceStatus == true || $storeDiscountStatus == true): ?>
                                  <span class="old-price"><?php echo $readTopSales["price"]; ?><?php echo $readSettings["creditIcon"] ?></span>
                                  <small>/</small>
                                  <?php $newPrice = (($storeDiscountStatus == true) ? round(($readTopSales["price"]*(100-$readSettings["storeDiscount"]))/100) : $readTopSales["discountedPrice"]); ?>
                                  <span class="price"><?php echo $newPrice; ?><?php echo $readSettings["creditIcon"] ?></span>
                                <?php else: ?>
                                  <span class="price"><?php echo $readTopSales["price"]; ?><?php echo $readSettings["creditIcon"] ?></span>
                                <?php endif; ?>
                              </div>
                            </div>
                            <div class="store-card-button">
                              <?php if ($readTopSales["stock"] == 0): ?>
                                <button class="btn btn-danger w-100 stretched-link disabled"><?php e__('Out of Stock!') ?></button>
                              <?php else: ?>
                                <button class="btn btn-banner-bg w-100 stretched-link openBuyModal" product-id="<?php echo $readTopSales["id"]; ?>"><i class="bi bi-cart-fill"></i> <?php e__('Buy Now') ?></button>
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          <?php endif; ?>
        <?php endif; ?>

        <?php if ($productCategories->rowCount() > 0): ?>
          <div class="card">
            <div class="card-header">
              <?php e__('Categories') ?>
            </div>
            <div class="card-body">
              <div class="row store-cards">
                <?php foreach ($productCategories as $readProductCategories): ?>
                  <div class="col-lg-3 col-md-4">
                    <div class="card">
                      <div class="card-body">
                        <div class="store-card">
                          <div class="sfx-store-back-of-photo">
                            <img class="lazyload w-100 p-3 loaded sfx-br-25" data-src="/apps/main/public/assets/img/store/categories/<?php echo $readProductCategories["imageID"].'.'.$readProductCategories["imageType"]; ?>" src="/apps/main/public/assets/img/loaders/store.png" alt="<?php echo $serverName." Kategori - ".$readProductCategories["name"]." Ürünlerini Görüntüle"; ?>">
                          </div>
                          <div class="store-card-text d-flex justify-content-center">
                            <span><?php echo $readProductCategories["name"]; ?></span>
                          </div>
                          <a class="btn btn-banner-bg w-100 stretched-link store-card-button" href="/store/<?php echo $readThisServer["slug"]; ?>/<?php echo $readProductCategories["slug"]; ?>"><?php e__('View') ?></a>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <?php
        $products = $db->prepare("SELECT * FROM Products WHERE serverID = ? AND categoryID = ?");
        $products->execute(array($readThisServer["id"], $categoryID));
        ?>
        <?php if ($products->rowCount() > 0): ?>
          <div class="card">
            <div class="card-header">
              <?php e__('Products') ?>
            </div>
            <div class="card-body">
              <div class="row store-cards">
                <?php foreach ($products as $readProducts): ?>
                  <?php $discountedPriceStatus = ($readProducts["discountedPrice"] != 0 && ($readProducts["discountExpiryDate"] > date("Y-m-d H:i:s") || $readProducts["discountExpiryDate"] == '1000-01-01 00:00:00')); ?>
                  <?php $storeDiscountStatus = ($readSettings["storeDiscount"] != 0 && (in_array($readProducts["id"], $discountProducts) || $readSettings["storeDiscountProducts"] == '0') && ($readSettings["storeDiscountExpiryDate"] > date("Y-m-d H:i:s") || $readSettings["storeDiscountExpiryDate"] == '1000-01-01 00:00:00')); ?>
                  <div class="col-lg-3 col-md-4">
                    <div class="card">
                      <div class="card-body">
                       <div class="store-card">
                        <?php if ($readProducts["stock"] != -1): ?>
                          <div class="store-card-stock <?php echo ($readProducts["stock"] == 0) ? "stock-out" : "have-stock"; ?>">
                            <?php if ($readProducts["stock"] == 0): ?>
                              <?php e__('Out of Stock!') ?>
                            <?php else : ?>
                              <?php e__('Limited Stock!') ?>
                            <?php endif; ?>
                          </div>
                        <?php endif; ?>
                        <?php if ($discountedPriceStatus == true || $storeDiscountStatus == true): ?>
                          <?php $discountPercent = (($storeDiscountStatus == true) ? $readSettings["storeDiscount"] : round((($readProducts["price"]-$readProducts["discountedPrice"])*100)/($readProducts["price"]))); ?>
                          <div class="store-card-discount">
                            <span>%<?php echo $discountPercent; ?></span>
                          </div>
                        <?php endif; ?>
                        <div class="sfx-store-back-of-photo">
                          <img class="lazyload w-100 p-3 sfx-br-25" data-src="/apps/main/public/assets/img/store/products/<?php echo $readProducts["imageID"].'.'.$readProducts["imageType"]; ?>" src="/apps/main/public/assets/img/loaders/store.png" alt="<?php echo $serverName." Ürün - ".$readProducts["name"]." Satın Al"; ?>">
                        </div>

                        <div class="row store-card-text">
                          <div class="col">
                            <span><?php echo $readProducts["name"]; ?></span>
                          </div>
                          <div class="col-auto">
                            <?php if ($discountedPriceStatus == true || $storeDiscountStatus == true): ?>
                              <span class="old-price"><?php echo $readProducts["price"]; ?><?php echo $readSettings["creditIcon"]; ?></span>
                              <small>/</small>
                              <?php $newPrice = (($storeDiscountStatus == true) ? round(($readProducts["price"]*(100-$readSettings["storeDiscount"]))/100) : $readProducts["discountedPrice"]); ?>
                              <span class="price"><?php echo $newPrice; ?><?php echo $readSettings["creditIcon"]; ?></span>
                            <?php else: ?>
                              <span class="price"><?php echo $readProducts["price"]; ?><?php echo $readSettings["creditIcon"]; ?></span>
                            <?php endif; ?>
                          </div>
                        </div>
                        <div class="store-card-button">
                          <?php if ($readProducts["stock"] == 0): ?>
                            <button class="btn btn-danger w-100 stretched-link disabled"><?php e__('Out of Stock!') ?></button>
                          <?php else: ?>
                            <button class="btn btn-banner-bg w-100 stretched-link openBuyModal" product-id="<?php echo $readProducts["id"]; ?>"><i class="bi bi-cart-fill"></i>&nbsp;<?php e__('Buy Now') ?></button>
                          <?php endif; ?>
                        </div>
                      </div> 
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        <?php else: ?>
          <?php if ($productCategories->rowCount() == 0): ?>
            <?php echo alertError(t__('No product were found!')); ?>
          <?php endif; ?>
        <?php endif; ?>
      <?php else: ?>
        <?php echo alertError("No data were found!"); ?>
      <?php endif; ?>
    </div>
  </div>
<?php else: ?>
  <?php go("/404"); ?>
<?php endif; ?>
</div>
</section>
