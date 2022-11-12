<?php
  if (!isset($_SESSION["login"])) {
    go("/login");
  }
  use Phelium\Component\reCAPTCHA;
  if (get("target") == 'support' && (get("action") == 'insert' || get("action") == 'get')) {
    require_once(__ROOT__.'/apps/main/private/packages/class/extraresources/extraresources.php');
    $extraResourcesJS = new ExtraResources('js');
    $extraResourcesJS->addResource('/apps/main/themes/sofixa/public/assets/js/support.js');
    if (get("action") == 'insert') {
      $extraResourcesJS->addResource('/apps/main/themes/sofixa/public/assets/js/support.open.js');
    }
    $recaptchaPagesStatusJSON = $readSettings["recaptchaPagesStatus"];
    $recaptchaPagesStatus = json_decode($recaptchaPagesStatusJSON, true);
    $recaptchaStatus = $readSettings["recaptchaPublicKey"] != '0' && $readSettings["recaptchaPrivateKey"] != '0' && $recaptchaPagesStatus["supportPage"] == 1;
    if ($recaptchaStatus) {
      require_once(__ROOT__.'/apps/main/private/packages/class/recaptcha/recaptcha.php');
      $reCAPTCHA = new reCAPTCHA($readSettings["recaptchaPublicKey"], $readSettings["recaptchaPrivateKey"]);
      $reCAPTCHA->setRemoteIp(getIP());
      $reCAPTCHA->setLanguage("tr");
      $reCAPTCHA->setTheme(($readTheme["recaptchaThemeID"] == 1) ? "light" : (($readTheme["recaptchaThemeID"] == 2) ? "dark" : "light"));
      $extraResourcesJS->addResource($reCAPTCHA->getScriptURL(), true, true);
    }
  }
?>
<section class="section support-section">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><?php e__('Home') ?></a></li>
            <li class="breadcrumb-item"><a href="/support"><?php e__('Support') ?></a></li>
            <?php if (get("target") == 'support'): ?>
              <?php if (get("action") == 'getAll'): ?>
                <li class="breadcrumb-item active" aria-current="page"><?php e__('Tickets') ?></li>
              <?php elseif (get("action") == 'insert'): ?>
                <li class="breadcrumb-item active" aria-current="page"><?php e__('Open Ticket') ?></li>
              <?php elseif (get("action") == 'get'): ?>
                <li class="breadcrumb-item active" aria-current="page"><?php e__('View') ?></li>
              <?php else: ?>
                <li class="breadcrumb-item active" aria-current="page"><?php e__('Error!') ?></li>
              <?php endif; ?>
            <?php else: ?>
              <li class="breadcrumb-item active" aria-current="page"><?php e__('Support') ?></li>
            <?php endif; ?>
          </ol>
        </nav>
      </div>
      <div class="col-md-8">
        <?php if (get("target") == 'support'): ?>
          <?php if (get("action") == 'getAll'): ?>
            <?php
              $supports = $db->prepare("SELECT S.*, SC.name as categoryName, Se.name as serverName FROM Supports S INNER JOIN SupportCategories SC ON S.categoryID = SC.id INNER JOIN Servers Se ON S.serverID = Se.id WHERE S.accountID = ? ORDER BY S.updateDate DESC");
              $supports->execute(array($readAccount["id"]));
            ?>
            <a class="btn btn-banner-bg w-100 mb-3" href="/support/create"><?php e__('Open Ticket') ?></a>
            <?php if ($supports->rowCount() > 0): ?>
              <div class="card">
                <div class="card-header">
                  <?php e__('Tickets') ?>
                </div>
                <div class="card-body p-0">
                  <div class="table-responsive">
                    <table class="table table-hover">
                      <thead>
                        <tr>
                          <th class="text-center" style="width: 40px;">#ID</th>
                          <th><?php e__('Title') ?></th>
                          <th><?php e__('Category') ?></th>
                          <th><?php e__('Last Updated') ?></th>
                          <th class="text-center"><?php e__('Status') ?></th>
                          <th class="text-center"> </th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($supports as $readSupports): ?>
                          <tr>
                            <td class="text-center" style="width: 40px;">
                              <a href="/support/view/<?php echo $readSupports["id"]; ?>">
                                #<?php echo $readSupports["id"]; ?>
                              </a>
                            </td>
                            <td>
                              <a href="/support/view/<?php echo $readSupports["id"]; ?>">
                                <?php echo $readSupports["title"]; ?>
                              </a>
                            </td>
                            <td><?php echo $readSupports["categoryName"]; ?></td>
                            <td><?php echo convertTime($readSupports["updateDate"]); ?></td>
                            <td class="text-center">
                              <?php if ($readSupports["statusID"] == 1): ?>
                                <span class="badge badge-pill badge-info"><?php e__('Open') ?></span>
                              <?php elseif ($readSupports["statusID"] == 2): ?>
                                <span class="badge badge-pill badge-success"><?php e__('Answered') ?></span>
                              <?php elseif ($readSupports["statusID"] == 3): ?>
                                <span class="badge badge-pill badge-warning"><?php e__('User-Reply') ?></span>
                              <?php elseif ($readSupports["statusID"] == 4): ?>
                                <span class="badge badge-pill badge-danger"><?php e__('Closed') ?></span>
                              <?php else: ?>
                                <span class="badge badge-pill badge-danger"><?php e__('Error!') ?></span>
                              <?php endif; ?>
                            </td>
                            <td class="text-center">
                              <a class="btn btn-success btn-circle" href="/support/view/<?php echo $readSupports["id"]; ?>" data-toggle="tooltip" data-placement="top" title="<?php e__('View') ?>">
                                <i class="fa fa-eye"></i>
                              </a>
                              <a class="btn btn-danger btn-circle clickdelete" href="/support/delete/<?php echo $readSupports["id"]; ?>" data-toggle="tooltip" data-placement="top" title="<?php e__('Delete') ?>">
                                <i class="fa fa-trash"></i>
                              </a>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            <?php else: ?>
              <?php echo alertError(t__('No tickets were found!')); ?>
            <?php endif; ?>
          <?php elseif (get("action") == 'insert'): ?>
            <?php
              require_once(__ROOT__."/apps/main/private/packages/class/csrf/csrf.php");
              $csrf = new CSRF('csrf-sessions', 'csrf-token');
              if (isset($_POST["insertSupports"])) {
                if (!$csrf->validate('insertSupports')) {
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
                else if ((post("title") == null) || (post("serverID") == null) || (post("categoryID") == null) || (post("message") == null)) {
                  echo alertError(t__('Please fill all the fields!'));
                }
                else {
                  $supportBannedStatus = $db->prepare("SELECT * FROM BannedAccounts WHERE accountID = ? AND categoryID = ? AND (expiryDate > ? OR expiryDate = ?)");
                  $supportBannedStatus->execute(array($readAccount["id"], 2, date("Y-m-d H:i:s"), '1000-01-01 00:00:00'));
                  if ($supportBannedStatus->rowCount() > 0) {
                    echo alertError(t__('You are banned from support!'));
                  }
                  else {
                    $insertSupports = $db->prepare("INSERT INTO Supports (title, accountID, message, serverID, categoryID, statusID, readStatus, updateDate, creationDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $insertSupports->execute(array(post("title"), $readAccount["id"], post("message"), post("serverID"), post("categoryID"), 1, 1, date("Y-m-d H:i:s"), date("Y-m-d H:i:s")));
                    $notificationsVariables = $db->lastInsertId();
                    $insertNotifications = $db->prepare("INSERT INTO Notifications (accountID, type, variables, creationDate) VALUES (?, ?, ?, ?)");
                    $insertNotifications->execute(array($readAccount["id"], 1, $notificationsVariables, date("Y-m-d H:i:s")));

                    $websiteURL = ((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === 'on' ? "https" : "http")."://".$_SERVER["SERVER_NAME"]);
                    if ($readSettings["webhookSupportURL"] != '0') {
                      require_once(__ROOT__."/apps/main/private/packages/class/webhook/webhook.php");
                      $search = array("%username%", "%panelurl%");
                      $replace = array($readAccount["realname"], "$websiteURL/dashboard/support/view/$notificationsVariables");
                      $webhookMessage = $readSettings["webhookSupportMessage"];
                      $webhookEmbed = $readSettings["webhookSupportEmbed"];
                      $postFields = (array(
                        'content'     => ($webhookMessage != '0') ? str_replace($search, $replace, $webhookMessage) : null,
                        'avatar_url'  => 'https://minotar.net/avatar/'.$readAccount["realname"].'/256.png',
                        'tts'         => false,
                        'embeds'      => array(
                          array(
                            'type'        => 'rich',
                            'title'       => $readSettings["webhookSupportTitle"],
                            'color'       => hexdec($readSettings["webhookSupportColor"]),
                            'description' => str_replace($search, $replace, $webhookEmbed),
                            'image'       => array(
                              'url' => ($readSettings["webhookSupportImage"] != '0') ? $readSettings["webhookSupportImage"] : null
                            ),
                            'footer'      =>
                            ($readSettings["webhookSupportAdStatus"] == 1) ? array(
                              'text'      => 'Powered by LeaderOS',
                              'icon_url'  => 'https://i.ibb.co/wNHKQ7B/leaderos-logo.png'
                            ) : array()
                          )
                        )
                      ));
                      $curl = new \LeaderOS\Http\Webhook($readSettings["webhookSupportURL"]);
                      $curl(json_encode($postFields, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                    }

                    if ($readSettings["oneSignalAppID"] != '0' && $readSettings["oneSignalAPIKey"] != '0') {
                      require_once(__ROOT__."/apps/main/private/packages/class/onesignal/onesignal.php");
                      $adminAccounts = $db->prepare("SELECT AOSI.oneSignalID FROM Accounts A INNER JOIN AccountOneSignalInfo AOSI ON A.id = AOSI.accountID WHERE A.permission IN (?, ?, ?, ?)");
                      $adminAccounts->execute(array(1, 2, 3, 5));
                      if ($adminAccounts->rowCount() > 0) {
                        $oneSignalIDList = array();
                        foreach ($adminAccounts as $readAdminAccounts) {
                          array_push($oneSignalIDList, $readAdminAccounts["oneSignalID"]);
                        }
                        $oneSignal = new OneSignal($readSettings["oneSignalAppID"], $readSettings["oneSignalAPIKey"], $oneSignalIDList);
                        $oneSignal->sendMessage(t__('LeaderOS Notifications'), t__('%username% sent a support message.', ['%username%' => $readAccount["realname"]]), '/dashboard/support/view/'.$notificationsVariables);
                      }
                    }
                    echo alertSuccess(t__('Support message sent successfully. It will be answered by the staffs as soon as possible.'));
                    echo goDelay("/support", 2);
                  }
                }
              }
            ?>
            <div class="card">
              <div class="card-header">
                <?php e__('Open Ticket') ?>
              </div>
              <div class="card-body">
                <form action="" method="post">
                  <div class="form-group row">
                    <label for="inputTitle" class="col-sm-2 col-form-label"><?php e__('Title') ?>:</label>
                    <div class="col-sm-10">
                      <input type="text" id="inputTitle" class="form-control" name="title" placeholder="<?php e__('Enter a title.') ?>">
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="selectServer" class="col-sm-2 col-form-label"><?php e__('Server') ?>:</label>
                    <div class="col-sm-10">
                      <?php
                        $servers = $db->query("SELECT * FROM Servers");
                      ?>
                      <select id="selectServer" class="form-control" name="serverID" data-toggle="select2" <?php echo ($servers->rowCount() == 0) ? "disabled" : null; ?>>
                        <?php if ($servers->rowCount() > 0): ?>
                          <?php foreach ($servers as $readServers): ?>
                            <option value="<?php echo $readServers["id"]; ?>"><?php echo $readServers["name"]; ?></option>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <option><?php e__('No server were found!') ?></option>
                        <?php endif; ?>
                      </select>
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="selectCategory" class="col-sm-2 col-form-label"><?php e__('Category') ?>:</label>
                    <div class="col-sm-10">
                      <?php
                        $supportCategories = $db->query("SELECT * FROM SupportCategories");
                      ?>
                      <select id="selectCategory" class="form-control" name="categoryID" data-toggle="select2" <?php echo ($supportCategories->rowCount() == 0) ? "disabled" : null; ?>>
                        <?php if ($supportCategories->rowCount() > 0): ?>
                          <option disabled selected><?php e__('Select category.') ?></option>
                          <?php foreach ($supportCategories as $readSupportCategories): ?>
                            <option value="<?php echo $readSupportCategories["id"]; ?>" data-template="<?php echo $readSupportCategories["userTemplate"] ?>"><?php echo $readSupportCategories["name"]; ?></option>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <option><?php e__('No category were found!') ?></option>
                        <?php endif; ?>
                      </select>
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="textareaMessage" class="col-sm-2 col-form-label"><?php e__('Message') ?>:</label>
                    <div class="col-sm-10">
                      <textarea id="textareaMessage" class="form-control" rows="6" name="message" placeholder="<?php e__('Type the message you want to forward to our support team.') ?>"></textarea>
                    </div>
                  </div>
                  <?php if ($recaptchaStatus): ?>
                    <div class="form-group d-flex justify-content-end">
                      <?php echo $reCAPTCHA->getHtml(); ?>
                    </div>
                  <?php endif; ?>
                  <?php echo $csrf->input('insertSupports'); ?>
                  <div class="clearfix">
                    <div class="float-right">
                      <button type="submit" class="btn btn-banner-bg btn-rounded" name="insertSupports"><?php e__('Send') ?></button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          <?php elseif (get("action") == 'get' && isset($_GET["id"])): ?>
            <?php
              $support = $db->prepare("SELECT S.*, A.realname, A.permission, Se.name as serverName, SC.name as categoryName FROM Supports S INNER JOIN Accounts A ON S.accountID = A.id INNER JOIN Servers Se ON S.serverID = Se.id INNER JOIN SupportCategories SC ON S.categoryID = SC.id WHERE S.id = ? AND S.accountID = ?");
              $support->execute(array(get("id"), $readAccount["id"]));
              $readSupport = $support->fetch();
            ?>
            <?php if ($support->rowCount() > 0): ?>
              <?php
                require_once(__ROOT__."/apps/main/private/packages/class/csrf/csrf.php");
                $csrf = new CSRF('csrf-sessions', 'csrf-token');

                $updateSupports = $db->prepare("UPDATE Supports SET readStatus = ? WHERE id = ? AND accountID = ?");
                $updateSupports->execute(array(1, get("id"), $readAccount["id"]));

                if (isset($_POST["insertSupportMessages"])) {
                  if (!$csrf->validate('insertSupportMessages')) {
                    echo '<div class="mb-3">'.alertError(t__('Something went wrong! Please try again later.')).'</div>';
                  }
                  else if (post("message") == null) {
                    echo '<div class="mb-3">'.alertError(t__('Please fill all the fields!')).'</div>';
                  }
                  else {
                    $supportBannedStatus = $db->prepare("SELECT * FROM BannedAccounts WHERE accountID = ? AND categoryID = ? AND (expiryDate > ? OR expiryDate = ?)");
                    $supportBannedStatus->execute(array($readAccount["id"], 2, date("Y-m-d H:i:s"), '1000-01-01 00:00:00'));
                    if ($supportBannedStatus->rowCount() > 0) {
                      echo alertError(t__('You are banned from support!'));
                    }
                    else {
                      $insertSupportMessages = $db->prepare("INSERT INTO SupportMessages (accountID, message, supportID, writeLocation, creationDate) VALUES (?, ?, ?, ?, ?)");
                      $insertSupportMessages->execute(array($readAccount["id"], post("message"), get("id"), 1, date("Y-m-d H:i:s")));
                      $updateSupports = $db->prepare("UPDATE Supports SET updateDate = ?, statusID = ?, readStatus = ? WHERE id = ? AND accountID = ?");
                      $updateSupports->execute(array(date("Y-m-d H:i:s"), 3, 1, get("id"), $readAccount["id"]));
                      $insertNotifications = $db->prepare("INSERT INTO Notifications (accountID, type, variables, creationDate) VALUES (?, ?, ?, ?)");
                      $insertNotifications->execute(array($readAccount["id"], 1, $readSupport["id"], date("Y-m-d H:i:s")));

                      $websiteURL = ((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === 'on' ? "https" : "http")."://".$_SERVER["SERVER_NAME"]);
                      if ($readSettings["webhookSupportURL"] != '0') {
                        require_once(__ROOT__."/apps/main/private/packages/class/webhook/webhook.php");
                        $search = array("%username%", "%panelurl%");
                        $replace = array($readAccount["realname"], "$websiteURL/dashboard/support/view/$readSupport[id]");
                        $webhookMessage = $readSettings["webhookSupportMessage"];
                        $webhookEmbed = $readSettings["webhookSupportEmbed"];
                        $postFields = (array(
                          'content'     => ($webhookMessage != '0') ? str_replace($search, $replace, $webhookMessage) : null,
                          'avatar_url'  => 'https://minotar.net/avatar/'.$readAccount["realname"].'/256.png',
                          'tts'         => false,
                          'embeds'      => array(
                            array(
                              'type'        => 'rich',
                              'title'       => $readSettings["webhookSupportTitle"],
                              'color'       => hexdec($readSettings["webhookSupportColor"]),
                              'description' => str_replace($search, $replace, $webhookEmbed),
                              'image'       => array(
                                'url' => ($readSettings["webhookSupportImage"] != '0') ? $readSettings["webhookSupportImage"] : null
                              ),
                              'footer'      =>
                              ($readSettings["webhookSupportAdStatus"] == 1) ? array(
                                'text'      => 'Powered by LeaderOS',
                                'icon_url'  => 'https://i.ibb.co/wNHKQ7B/leaderos-logo.png'
                              ) : array()
                            )
                          )
                        ));
                        $curl = new \LeaderOS\Http\Webhook($readSettings["webhookSupportURL"]);
                        $curl(json_encode($postFields, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                      }

                      if ($readSettings["oneSignalAppID"] != '0' && $readSettings["oneSignalAPIKey"] != '0') {
                        require_once(__ROOT__."/apps/main/private/packages/class/onesignal/onesignal.php");
                        $adminAccounts = $db->prepare("SELECT AOSI.oneSignalID FROM Accounts A INNER JOIN AccountOneSignalInfo AOSI ON A.id = AOSI.accountID WHERE A.permission IN (?, ?, ?, ?)");
                        $adminAccounts->execute(array(1, 2, 3, 5));
                        if ($adminAccounts->rowCount() > 0) {
                          $oneSignalIDList = array();
                          foreach ($adminAccounts as $readAdminAccounts) {
                            array_push($oneSignalIDList, $readAdminAccounts["oneSignalID"]);
                          }
                          $oneSignal = new OneSignal($readSettings["oneSignalAppID"], $readSettings["oneSignalAPIKey"], $oneSignalIDList);
                          $oneSignal->sendMessage(t__('LeaderOS Notifications'), t__('%username% sent a support message.', ['%username%' => $readAccount["realname"]]), '/dashboard/support/view/'.$readSupport["id"]);
                        }
                      }
                      echo '<div class="mb-3">'.alertSuccess(t__('Your message has been sent to us successfully. It will be answered by the staffs as soon as possible.')).'</div>';
                    }
                  }
                }
              ?>
              <div class="card">
                <div class="card-header">
                  <div class="row">
                    <div class="col">
                      <?php echo limitedContent($readSupport["title"], 50); ?>
                    </div>
                    <div class="col-auto">
                      <span class="badge badge-pill badge-default" data-toggle="tooltip" data-placement="top" data-original-title="<?php e__('Server') ?>">
                        <?php echo $readSupport["serverName"]; ?>
                      </span>
                      <span class="badge badge-pill badge-default" data-toggle="tooltip" data-placement="top" data-original-title="<?php e__('Category') ?>">
                        <?php echo $readSupport["categoryName"]; ?>
                      </span>
                      <span class="badge badge-pill badge-default" data-toggle="tooltip" data-placement="top" data-original-title="<?php e__('Date') ?>">
                        <?php echo convertTime($readSupport["creationDate"], 2, true); ?>
                      </span>
                      <?php if ($readSupport["statusID"] == 1): ?>
                        <span class="badge badge-pill badge-info" data-toggle="tooltip" data-placement="top" data-original-title="<?php e__('Status') ?>"><?php e__('Open') ?></span>
                      <?php elseif ($readSupport["statusID"] == 2): ?>
                        <span class="badge badge-pill badge-success" data-toggle="tooltip" data-placement="top" data-original-title="<?php e__('Status') ?>"><?php e__('Answered') ?></span>
                      <?php elseif ($readSupport["statusID"] == 3): ?>
                        <span class="badge badge-pill badge-warning" data-toggle="tooltip" data-placement="top" data-original-title="<?php e__('Status') ?>"><?php e__('User-Reply') ?></span>
                      <?php elseif ($readSupport["statusID"] == 4): ?>
                        <span class="badge badge-pill badge-danger" data-toggle="tooltip" data-placement="top" data-original-title="<?php e__('Status') ?>"><?php e__('Closed') ?></span>
                      <?php else: ?>
                        <span class="badge badge-pill badge-danger" data-toggle="tooltip" data-placement="top" data-original-title="<?php e__('Status') ?>"><?php e__('Error!') ?></span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
                <div id="messagesBox" class="card-body pb-0" style="overflow: auto; max-height: 500px;">
                  <div class="message">
                    <div class="message-img">
                      <a href="/player/<?php echo $readSupport["realname"]; ?>">
                        <?php echo minecraftHead($readSettings["avatarAPI"], $readSupport["realname"], 40, "float-left"); ?>
                      </a>
                    </div>
                    <div class="message-content">
                      <div class="message-header">
                        <div class="message-username">
                          <a href="/player/<?php echo $readSupport["realname"]; ?>">
                            <?php echo $readSupport["realname"]; ?>
                          </a>
                          <?php echo verifiedCircle($readSupport["permission"]); ?>
                        </div>
                        <div class="message-date">
                          <?php echo convertTime($readSupport["creationDate"], 2, true); ?>
                        </div>
                      </div>
                      <div class="message-body">
                        <p>
                          <?php echo showEmoji(urlContent(hashtag(hashtag($readSupport["message"], "@", "/player"), "#", "/tags"))); ?>
                        </p>
                      </div>
                    </div>
                  </div>
                <?php
                  $supportMessages = $db->prepare("SELECT SM.*, A.realname, A.permission FROM SupportMessages SM INNER JOIN Accounts A ON SM.accountID = A.id WHERE SM.supportID = ? ORDER BY SM.id ASC");
                  $supportMessages->execute(array(get("id")));
                ?>
                <?php if ($supportMessages->rowCount() > 0): ?>
                  <?php foreach ($supportMessages as $readSupportMessages): ?>
                    <div class="message">
                      <div class="message-img">
                        <a href="/player/<?php echo $readSupportMessages["realname"]; ?>">
                          <?php echo minecraftHead($readSettings["avatarAPI"], $readSupportMessages["realname"], 40, "float-left"); ?>
                        </a>
                      </div>
                      <div class="message-content">
                        <div class="message-header">
                          <div class="message-username">
                            <a href="/player/<?php echo $readSupportMessages["realname"]; ?>">
                              <?php echo $readSupportMessages["realname"]; ?>
                            </a>
                            <?php echo verifiedCircle($readSupportMessages["permission"]); ?>
                          </div>
                          <div class="message-date">
                            <?php echo convertTime($readSupportMessages["creationDate"], 2, true); ?>
                          </div>
                        </div>
                        <div class="message-body">
                          <p>
                            <?php
                              if ($readSupportMessages["writeLocation"] == 1) {
                                echo showEmoji(urlContent(hashtag(hashtag($readSupportMessages["message"], "@", "/player"), "#", "/tags")));
                              }
                              else {
                                $message = showEmoji(hashtag(hashtag($readSupportMessages["message"], "@", "/player"), "#", "/tags"));
                                $search = array("%username%", "%message%", "%servername%", "%serverip%", "%serverversion%");
                                $replace = array($readSupport["realname"], $message, $serverName, $serverIP, $serverVersion);
                                $template = $readSettings["supportMessageTemplate"];
                                echo str_replace($search, $replace, $template);
                              }
                            ?>
                          </p>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
                </div>
                <?php if ($readSupport["statusID"] != 4): ?>
                  <div class="card-footer">
                    <form action="" method="post">
                      <div class="message">
                        <div class="message-img">
                          <?php echo minecraftHead($readSettings["avatarAPI"], $readAccount["realname"], 40, "float-left"); ?>
                        </div>
                        <div class="message-content">
                          <div class="message-body">
                            <textarea class="form-control" name="message" rows="3" placeholder="<?php e__('Write your message...') ?>"></textarea>
                          </div>
                          <div class="message-footer">
                            <?php echo $csrf->input('insertSupportMessages'); ?>
                            <div class="clearfix">
                              <div class="float-right">
                                <button type="submit" class="btn btn-success btn-rounded" name="insertSupportMessages"><?php e__('Send') ?></button>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </form>
                  </div>
                <?php endif; ?>
              </div>
            <?php else: ?>
              <?php echo alertError(t__('Ticket not found!')); ?>
            <?php endif; ?>
          <?php elseif (get("action") == 'delete' && isset($_GET["id"])): ?>
            <?php
              $closeSupport = $db->prepare("UPDATE Supports SET statusID = ? WHERE id = ? AND accountID = ?");
              $closeSupport->execute(array(4, get("id"), $readAccount["id"]));
              go('/support');
            ?>
          <?php else: ?>
            <?php go('/404'); ?>
          <?php endif; ?>
        <?php else: ?>
          <?php go('/404'); ?>
        <?php endif; ?>
      </div>
      <div class="col-md-4">
        <?php
          $onlineAccountsHistory = $db->prepare("SELECT OAH.*, A.realname, A.permission FROM OnlineAccountsHistory OAH INNER JOIN Accounts A ON OAH.accountID = A.id WHERE OAH.expiryDate > ?");
          $onlineAccountsHistory->execute(array(date("Y-m-d H:i:s")));
        ?>
        <?php if ($onlineAccountsHistory->rowCount() > 0): ?>
          <div class="card">
            <div class="card-header">
              <?php e__('Online Staffs') ?>
            </div>
            <div class="card-body p-0">
              <ul class="list-group list-group-flush">
                <?php foreach ($onlineAccountsHistory as $readOnlineAccountsHistory): ?>
                  <li class="list-group-item">
                    <a href="/player/<?php echo $readOnlineAccountsHistory["realname"]; ?>" data-toggle="tooltip" data-placement="left" data-original-title="<?php e__('Last Seen') ?>: <?php echo convertTime($readOnlineAccountsHistory["creationDate"]); ?>">
                      <div class="row">
                        <div class="col">
                          <?php echo minecraftHead($readSettings["avatarAPI"], $readOnlineAccountsHistory["realname"], 20, "mr-2"); ?>
                          <span>
                            <?php echo $readOnlineAccountsHistory["realname"]; ?>
                          </span>
                          <span>
                            <?php echo verifiedCircle($readOnlineAccountsHistory["permission"]); ?>
                          </span>
                        </div>
                        <div class="col-auto">
                          <span class="text-success">●</span>
                        </div>
                      </div>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        <?php else: ?>
          <?php echo alertWarning(t__('There are currently no online staff!')); ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
