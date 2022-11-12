<?php
  use Phelium\Component\reCAPTCHA;
  require_once(__ROOT__.'/apps/main/private/packages/class/extraresources/extraresources.php');
  $extraResourcesJS = new ExtraResources('js');
  $extraResourcesJS->addResource('/apps/main/themes/sofixa/public/assets/js/loader.js');
  $recaptchaPagesStatusJSON = $readSettings["recaptchaPagesStatus"];
  $recaptchaPagesStatus = json_decode($recaptchaPagesStatusJSON, true);
  $recaptchaStatus = $readSettings["recaptchaPublicKey"] != '0' && $readSettings["recaptchaPrivateKey"] != '0' && $recaptchaPagesStatus["newsPage"] == 1;
  if ($recaptchaStatus) {
    require_once(__ROOT__.'/apps/main/private/packages/class/recaptcha/recaptcha.php');
    $reCAPTCHA = new reCAPTCHA($readSettings["recaptchaPublicKey"], $readSettings["recaptchaPrivateKey"]);
    $reCAPTCHA->setRemoteIp(getIP());
    $reCAPTCHA->setLanguage("tr");
    $reCAPTCHA->setTheme(($readTheme["recaptchaThemeID"] == 1) ? "light" : (($readTheme["recaptchaThemeID"] == 2) ? "dark" : "light"));
    $extraResourcesJS->addResource($reCAPTCHA->getScriptURL(), true, true);
  }
  $news = $db->prepare("SELECT N.*, A.realname, A.permission, NC.name as categoryName, NC.slug as categorySlug FROM News N INNER JOIN Accounts A ON N.accountID = A.id INNER JOIN NewsCategories NC ON N.categoryID = NC.id WHERE N.id = ?");
  $news->execute(array(get("id")));
  $readNews = $news->fetch();
?>
<section class="section news-section">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><?php e__('Home') ?></a></li>
            <?php if (isset($_GET["id"])): ?>
              <?php if ($news->rowCount() > 0): ?>
                <li class="breadcrumb-item"><a href="/categories/<?php echo $readNews["categorySlug"]; ?>"><?php echo $readNews["categoryName"]; ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $readNews["title"]; ?></li>
              <?php else: ?>
                <li class="breadcrumb-item active" aria-current="page"><?php e__('Not Found!') ?></li>
              <?php endif; ?>
            <?php else: ?>
              <li class="breadcrumb-item active" aria-current="page"><?php e__('Blog') ?></li>
            <?php endif; ?>
          </ol>
        </nav>
      </div>
      <div class="col-md-8 col-news">
        <?php if ($news->rowCount() > 0): ?>
          <?php if (!isset($_COOKIE["newsID"])): ?>
            <?php
              $updateNews = $db->prepare("UPDATE News SET views = views + 1 WHERE id = ?");
              $updateNews->execute(array($readNews["id"]));
              setcookie("newsID", $readNews["id"]);
            ?>
          <?php endif; ?>
          <?php
            $newsComments = $db->prepare("SELECT NC.*, A.realname, A.permission FROM NewsComments NC INNER JOIN Accounts A ON NC.accountID = A.id WHERE NC.newsID = ? AND NC.status = ? ORDER BY NC.id DESC");
            $newsComments->execute(array($readNews["id"], 1));
          ?>
          <div class="card mb-4">
            <div class="card-header">
              <?php echo $readNews["title"]; ?>
            </div>
            <div class="card-body">
              <div class="news-info mb-4">
                <div class="news-author float-left pl:11">
                  <div class="author-img float-left mr-2">
                    <a href="/player/<?php echo $readNews["realname"]; ?>">
                      <?php echo minecraftHead($readSettings["avatarAPI"], $readNews["realname"], 34); ?>
                    </a>
                  </div>
                  <div class="author-info float-left" style="line-height: 1.125rem;">
                    <a href="/player/<?php echo $readNews["realname"]; ?>">
                      <span class="d-block" style="font-weight: 600;">
                        <?php echo $readNews["realname"]; ?>
                        <?php echo verifiedCircle($readNews["permission"]); ?>
                      </span>
                    </a>
                    <span><?php echo convertTime($readNews["creationDate"], 2, true); ?></span>
                  </div>
                </div>
                <div class="float-right">
                  <label class="mr-2" data-toggle="tooltip" data-placement="top" title="<?php e__('Views') ?>"><i class="fa fa-eye"></i> <?php echo $readNews["views"]; ?></label>
                  <label data-toggle="tooltip" data-placement="top" title="<?php e__('Comments') ?>"><i class="fa fa-comments"></i> <?php echo $newsComments->rowCount(); ?></label>
                </div>
                <div class="clearfix"></div>
              </div>
              <div class="news-content mb-4">
                <?php echo showEmoji(hashtag(hashtag($readNews["content"], "@", "/player"), "#", "/tags")); ?>
              </div>
              <div class="news-tags">
                <span style="font-weight: 600;"><?php e__('Tags') ?>:</span>
                <?php
                  $newsTags = $db->prepare("SELECT NT.* FROM NewsTags NT INNER JOIN News N ON NT.newsID = N.id WHERE NT.newsID = ?");
                  $newsTags->execute(array($readNews["id"]));
                  if ($newsTags->rowCount() > 0) {
                    foreach ($newsTags as $readNewsTags) {
                      echo '<a class="theme-color btn btn-tag news-tag-in-content btn-primary btn-rounded" href="/tags/'.$readNewsTags["slug"].'">'.$readNewsTags["name"].'</a>';
                    }
                  }
                  else {
                    echo "-";
                  }
                ?>
              </div>
            </div>
          </div>
          <?php if ($readSettings["commentsStatus"] == 1 && $readNews["commentsStatus"] == 1): ?>
            <?php if (isset($_SESSION["login"])): ?>
              <?php
                require_once(__ROOT__."/apps/main/private/packages/class/csrf/csrf.php");
                $csrf = new CSRF('csrf-sessions', 'csrf-token');
                if (isset($_POST["insertNewsComments"])) {
                  if (!$csrf->validate('insertNewsComments')) {
                    echo alertError(t__('Something went wrong! Please try again later.'));
                  }
                  else if ($recaptchaStatus && post("g-recaptcha-response") == null) {
                    echo alertError(t__('Please verify you are not a robot.'));
                  }
                  else if ($recaptchaStatus && !$reCAPTCHA->isValid(post("g-recaptcha-response"))) {
                    // Hata Tespit
                    //var_dump($reCAPTCHA->getErrorCodes());
                    echo alertError(t__('Spam detected!'));
                  }
                  else if (post("message") == null) {
                    echo alertError(t__('Please fill all the fields!'));
                  }
                  else {
                    $commentBannedStatus = $db->prepare("SELECT * FROM BannedAccounts WHERE accountID = ? AND categoryID = ? AND (expiryDate > ? OR expiryDate = ?)");
                    $commentBannedStatus->execute(array($readAccount["id"], 3, date("Y-m-d H:i:s"), '1000-01-01 00:00:00'));
                    if ($commentBannedStatus->rowCount() > 0) {
                      echo alertError(t__('You are banned from commenting.'));
                    }
                    else {
                      if ($readAccount["permission"] == 1 || $readAccount["permission"] == 2 || $readAccount["permission"] == 5) {
                        $status = 1;
                        echo alertSuccess(t__('Your comment has been succesfully sent.'));
                      }
                      else {
                        $status = 0;
                        echo alertSuccess(t__('Your comment will be visible to public after a mod-check.'));
                      }
                      $insertNewsComments = $db->prepare("INSERT INTO NewsComments (accountID, message, newsID, status, creationDate) VALUES (?, ?, ?, ?, ?)");
                      $insertNewsComments->execute(array($readAccount["id"], post("message"), get("id"), $status, date("Y-m-d H:i:s")));
                      $notificationsVariables = $db->lastInsertId();
                      $insertNotifications = $db->prepare("INSERT INTO Notifications (accountID, type, variables, creationDate) VALUES (?, ?, ?, ?)");
                      $insertNotifications->execute(array($readAccount["id"], 2, $notificationsVariables, date("Y-m-d H:i:s")));

                      $websiteURL = ((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === 'on' ? "https" : "http")."://".$_SERVER["SERVER_NAME"]);
                      if ($readSettings["webhookNewsURL"] != '0') {
                        require_once(__ROOT__."/apps/main/private/packages/class/webhook/webhook.php");
                        $search = array("%username%", "%panelurl%", "%posturl%");
                        $replace = array($readAccount["realname"], "$websiteURL/dashboard/blog/comments/edit/$notificationsVariables", "$websiteURL/posts/$readNews[id]/$readNews[slug]");
                        $webhookMessage = $readSettings["webhookNewsMessage"];
                        $webhookEmbed = $readSettings["webhookNewsEmbed"];
                        $postFields = (array(
                          'content'     => ($webhookMessage != '0') ? str_replace($search, $replace, $webhookMessage) : null,
                          'avatar_url'  => 'https://minotar.net/avatar/'.$readAccount["realname"].'/256.png',
                          'tts'         => false,
                          'embeds'      => array(
                            array(
                              'type'        => 'rich',
                              'title'       => $readSettings["webhookNewsTitle"],
                              'color'       => hexdec($readSettings["webhookNewsColor"]),
                              'description' => str_replace($search, $replace, $webhookEmbed),
                              'image'       => array(
                                'url' => ($readSettings["webhookNewsImage"] != '0') ? $readSettings["webhookNewsImage"] : null
                              ),
                              'footer'      =>
                              ($readSettings["webhookNewsAdStatus"] == 1) ? array(
                                'text'      => 'Powered by LeaderOS',
                                'icon_url'  => 'https://i.ibb.co/wNHKQ7B/leaderos-logo.png'
                              ) : array()
                            )
                          )
                        ));
                        $curl = new \LeaderOS\Http\Webhook($readSettings["webhookNewsURL"]);
                        $curl(json_encode($postFields, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                      }

                      if ($readSettings["oneSignalAppID"] != '0' && $readSettings["oneSignalAPIKey"] != '0') {
                        require_once(__ROOT__."/apps/main/private/packages/class/onesignal/onesignal.php");
                        $adminAccounts = $db->prepare("SELECT AOSI.oneSignalID FROM Accounts A INNER JOIN AccountOneSignalInfo AOSI ON A.id = AOSI.accountID WHERE A.permission IN (?, ?, ?, ?)");
                        $adminAccounts->execute(array(1, 2, 3, 4));
                        if ($adminAccounts->rowCount() > 0) {
                          $oneSignalIDList = array();
                          foreach ($adminAccounts as $readAdminAccounts) {
                            array_push($oneSignalIDList, $readAdminAccounts["oneSignalID"]);
                          }
                          $oneSignal = new OneSignal($readSettings["oneSignalAppID"], $readSettings["oneSignalAPIKey"], $oneSignalIDList);
                          $oneSignal->sendMessage(t__('LeaderOS Notifications'), t__('%username% left a comment.', ['%username%' => $readAccount["realname"]]), '/dashboard/blog/comments/edit/'.$notificationsVariables);
                        }
                      }
                    }
                  }
                }
              ?>
              <div class="card mb-4">
                <div class="card-header">
                  <?php e__('Leave a Reply') ?>
                </div>
                <div class="card-body">
                  <form action="" method="post">
                    <div class="message">
                      <div class="message-img">
                        <?php echo minecraftHead($readSettings["avatarAPI"], $readAccount["realname"], 40, "float-left"); ?>
                      </div>
                      <div class="message-content">
                        <div class="message-body">
                          <textarea class="form-control" name="message" rows="3" placeholder="<?php e__('Write your comment.') ?>"></textarea>
                        </div>
                        <?php if ($recaptchaStatus): ?>
                          <div class="d-flex justify-content-end mt-3">
                            <?php echo $reCAPTCHA->getHtml(); ?>
                          </div>
                        <?php endif; ?>
                        <div class="message-footer">
                          <?php echo $csrf->input('insertNewsComments'); ?>
                          <div class="clearfix">
                            <div class="float-right">
                              <button type="submit" class="btn btn-success btn-rounded" name="insertNewsComments"><?php e__('Send') ?></button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            <?php else: ?>
              <?php echo alertError(t__('You need to be signed in to comment.')); ?>
            <?php endif; ?>
            <?php if ($newsComments->rowCount() > 0): ?>
              <div class="card mb-4">
                <div class="card-header">
                  <?php e__('Comments') ?>
                </div>
                <div id="loader" class="card-body is-loading">
                  <div id="spinner">
                    <div class="spinner-border" role="status">
                      <span class="sr-only">-/-</span>
                    </div>
                  </div>
                  <?php foreach ($newsComments as $readNewsComments): ?>
                    <div class="message">
                      <div class="message-img">
                        <a href="/player/<?php echo $readNewsComments["realname"]; ?>">
                          <?php echo minecraftHead($readSettings["avatarAPI"], $readNewsComments["realname"], 40, "float-left"); ?>
                        </a>
                      </div>
                      <div class="message-content">
                        <div class="message-header">
                          <div class="message-username">
                            <a style="font-weight: 600;" href="/player/<?php echo $readNewsComments["realname"]; ?>">
                              <?php echo $readNewsComments["realname"]; ?>
                            </a>
                            <?php echo verifiedCircle($readNewsComments["permission"]); ?>
                          </div>
                          <div class="message-date">
                            <?php echo convertTime($readNewsComments["creationDate"]); ?>
                          </div>
                        </div>
                        <div class="message-body">
                          <p>
                            <?php echo showEmoji(urlContent(hashtag(hashtag($readNewsComments["message"], "@", "/player"), "#", "/tags"))); ?>
                          </p>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
              </div>
            </div>
            <?php else: ?>
              <?php if (isset($_SESSION["login"])): ?>
                <?php echo alertWarning(t__('Hey, no comments yet! Would you like to comment first?')); ?>
              <?php endif; ?>
            <?php endif; ?>
          <?php else: ?>
            <?php echo alertWarning(t__('Comments are closed!')); ?>
          <?php endif; ?>
        <?php else: ?>
          <?php echo alertError(t__('Post not found!')); ?>
        <?php endif; ?>
      </div>

      <div class="col-md-4 col-other-news">
        <?php
          $otherNews = $db->prepare("SELECT N.id, N.title, N.slug, N.content, N.views, N.imageID, N.imageType, N.creationDate, NC.name as categoryName, NC.slug as categorySlug from News N INNER JOIN Accounts A ON N.accountID = A.id INNER JOIN NewsCategories NC ON N.categoryID = NC.id WHERE N.id != ? ORDER BY N.id DESC LIMIT 5");
          $otherNews->execute(array($readNews["id"]));
        ?>
        <?php if ($otherNews->rowCount() > 0): ?>
          <?php foreach ($otherNews as $readOtherNews): ?>
            <?php
              $otherNewsComments = $db->prepare("SELECT NC.id FROM NewsComments NC INNER JOIN Accounts A ON NC.accountID = A.id WHERE NC.newsID = ? AND NC.status = ?");
              $otherNewsComments->execute(array($readOtherNews["id"], 1));
            ?>
            <article class="news">
              <div class="img-card-wrapper">
                <div class="img-container">
                  <a class="img-card" href="/posts/<?php echo $readOtherNews["id"]; ?>/<?php echo $readOtherNews["slug"]; ?>">
                    <img class="card-img-top lazyload" data-src="/apps/main/public/assets/img/news/<?php echo $readOtherNews["imageID"].'.'.$readOtherNews["imageType"]; ?>" src="/apps/main/public/assets/img/loaders/news.png" alt="<?php echo $serverName." Haber - ".$readOtherNews["title"]; ?>">
                  </a>
                  <div class="img-card-tl">
                    <a href="/categories/<?php echo $readOtherNews["categorySlug"]; ?>">
                      <span class="theme-color badge badge-pill badge-primary"><?php echo $readOtherNews["categoryName"]; ?></span>
                    </a>
                    <a href="/posts/<?php echo $readOtherNews["id"]; ?>/<?php echo $readOtherNews["slug"]; ?>">
                      <span class="theme-color badge badge-pill badge-primary"><i class="fa fa-eye"></i> <?php echo $readOtherNews["views"]; ?></span>
                    </a>
                    <a href="/posts/<?php echo $readOtherNews["id"]; ?>/<?php echo $readOtherNews["slug"]; ?>">
                      <span class="theme-color badge badge-pill badge-primary"><i class="fa fa-comments"></i> <?php echo $otherNewsComments->rowCount(); ?></span>
                    </a>
                  </div>
                  <div class="img-card-tr">
                    <a href="/posts/<?php echo $readOtherNews["id"]; ?>/<?php echo $readOtherNews["slug"]; ?>">
                      <span class="theme-color badge badge-pill badge-primary"><?php echo convertTime($readOtherNews["creationDate"], 1); ?></span>
                    </a>
                  </div>
                  <div class="img-card-bottom">
                    <h5 class="mb-0">
                      <a class="text-white" href="/posts/<?php echo $readOtherNews["id"]; ?>/<?php echo $readOtherNews["slug"]; ?>">
                        <?php echo $readOtherNews["title"]; ?>
                      </a>
                    </h5>
                  </div>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        <?php else: ?>
          <?php echo alertError(t__('No related posts!')); ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
