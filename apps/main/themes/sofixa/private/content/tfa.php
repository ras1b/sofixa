<?php
  if (isset($_SESSION["login"])) {
    go("/profile");
  }
  if (isset($_SESSION["tfa"]) && $_SESSION["tfa"]["expiryDate"] <= date("Y-m-d H:i:s")) {
    unset($_SESSION["tfa"]);
  }
  if (!isset($_SESSION["tfa"]) || $readSettings["authStatus"] == 0) {
    go("/login");
  }
  use Phelium\Component\reCAPTCHA;
  $recaptchaPagesStatusJSON = $readSettings["recaptchaPagesStatus"];
  $recaptchaPagesStatus = json_decode($recaptchaPagesStatusJSON, true);
  $recaptchaStatus = $readSettings["recaptchaPublicKey"] != '0' && $readSettings["recaptchaPrivateKey"] != '0' && $recaptchaPagesStatus["tfaPage"] == 1;
  if ($recaptchaStatus) {
    require_once(__ROOT__.'/apps/main/private/packages/class/extraresources/extraresources.php');
    require_once(__ROOT__.'/apps/main/private/packages/class/recaptcha/recaptcha.php');
    $reCAPTCHA = new reCAPTCHA($readSettings["recaptchaPublicKey"], $readSettings["recaptchaPrivateKey"]);
    $reCAPTCHA->setRemoteIp(getIP());
    $reCAPTCHA->setLanguage("tr");
    $reCAPTCHA->setTheme(($readTheme["recaptchaThemeID"] == 1) ? "light" : (($readTheme["recaptchaThemeID"] == 2) ? "dark" : "light"));
    $extraResourcesJS = new ExtraResources('js');
    $extraResourcesJS->addResource($reCAPTCHA->getScriptURL(), true, true);
  }
?>
    <section class="section page-section">
      <div class="container">
        <div class="row">
          <div class="col-md-4 offset-md-4">
            <?php if ($_SESSION["tfa"]["ipAddress"] == getIP()): ?>
              <?php
                $account = $db->prepare("SELECT * FROM Accounts WHERE id = ?");
                $account->execute(array($_SESSION["tfa"]["accountID"]));
                $readAccount = $account->fetch();
              ?>
              <?php if ($account->rowCount() > 0): ?>
                <?php
                  require_once(__ROOT__."/apps/main/private/packages/class/tfa/tfa.php");
                  $tfa = new GoogleAuthenticator();

                  $accountAuth = $db->prepare("SELECT * FROM AccountAuths WHERE accountID = ?");
                  $accountAuth->execute(array($readAccount["id"]));
                  $readAccountAuth = $accountAuth->fetch();
                  if ($accountAuth->rowCount() > 0 && $readAccount["authStatus"] == 1) {
                    if (!isset($_SESSION["tfa"]["secretKey"])) {
                      $_SESSION["tfa"]["secretKey"] = $readAccountAuth["secretKey"];
                    }
                  }
                  else {
                    if (!isset($_SESSION["tfa"]["secretKey"])) {
                      $_SESSION["tfa"]["secretKey"] = $tfa->createSecret();
                    }
                    $qrCode = $tfa->getQRCodeGoogleUrl($readAccount["realname"], $_SESSION["tfa"]["secretKey"], $serverName);
                  }
                ?>
                <?php
                  require_once(__ROOT__."/apps/main/private/packages/class/csrf/csrf.php");
                  $csrf = new CSRF('csrf-sessions', 'csrf-token');
                  if (isset($_POST["verifyTFA"])) {
                    if (!$csrf->validate('verifyTFA')) {
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
                    else if (post("oneCode") == null) {
                      echo alertError(t__('Please fill all the fields!'));
                    }
                    else {
                      $verifyTFA = $tfa->verifyCode($_SESSION["tfa"]["secretKey"], post("oneCode"));
                      if ($verifyTFA) {
                        $loginToken = md5(uniqid(mt_rand(), true));
                        $insertAccountSessions = $db->prepare("INSERT INTO AccountSessions (accountID, loginToken, creationIP, expiryDate, creationDate) VALUES (?, ?, ?, ?, ?)");
                        $insertAccountSessions->execute(array($readAccount["id"], $loginToken, getIP(), createDuration(((isset($_SESSION["tfa"]["rememberMe"]) && $_SESSION["tfa"]["rememberMe"] == 'true') ? 365 : 0.01666666666)), date("Y-m-d H:i:s")));

                        if ($accountAuth->rowCount() > 0) {
                          $updateAccountAuths = $db->prepare("UPDATE AccountAuths SET secretKey = ? WHERE accountID = ?");
                          $updateAccountAuths->execute(array($_SESSION["tfa"]["secretKey"], $readAccount["id"]));
                        }
                        else {
                          $insertAccountAuths = $db->prepare("INSERT INTO AccountAuths (accountID, secretKey) VALUES (?, ?)");
                          $insertAccountAuths->execute(array($readAccount["id"], $_SESSION["tfa"]["secretKey"]));
                        }

                        if (isset($_SESSION["tfa"]["profileUpdate"]) && $_SESSION["tfa"]["profileUpdate"] == 'true') {
                          $updateAccounts = $db->prepare("UPDATE Accounts SET authStatus = ? WHERE id = ?");
                          $updateAccounts->execute(array(1, $readAccount["id"]));
                        }

                        if (isset($_SESSION["tfa"]["rememberMe"]) && $_SESSION["tfa"]["rememberMe"] == 'true') {
                          createCookie("rememberMe", $loginToken, 365, $sslStatus);
                        }
                        $_SESSION["login"] = $loginToken;

                        unset($_SESSION["tfa"]);
                        unset($_SESSION["tfa-recover"]);
                        go("/profile");
                      }
                      else {
                        echo alertError(t__('Wrong code!'));
                      }
                    }
                  }
                ?>
                <div class="card">
                  <div class="card-header">
                    <?php e__('Two Factor Authentication') ?>
                  </div>
                  <div class="card-body">
                    <form action="" method="post">
                      <?php if ($accountAuth->rowCount() == 0 || $readAccount["authStatus"] == 0): ?>
                        <div class="form-group text-center">
                          <img src="<?php echo $qrCode; ?>" alt="Google Authenticator QR Kod">
                        </div>
                        <div class="form-group text-center">
                          <span><?php e__('If you cannot read the QR code, add an account with the <strong>%key%</strong> key.', ['%key%' => $_SESSION["tfa"]["secretKey"]]) ?></span>
                        </div>
                      <?php endif; ?>
                      <div class="form-group">
                        <?php if ($accountAuth->rowCount() > 0 && $readAccount["authStatus"] == 1): ?>
                          <div class="row">
                            <div class="col">
                              <label for="input-password" class="form-control-label"><?php e__('Code') ?>:</label>
                            </div>
                            <div class="col-auto">
                              <a class="small" href="/recover-verify"><?php e__("I don't have access!") ?></a>
                            </div>
                          </div>
                        <?php endif; ?>
                        <input type="text" class="form-control" name="oneCode" placeholder="<?php e__('Enter the code') ?>" autocomplete="off">
                      </div>
                      <?php if ($recaptchaStatus): ?>
                        <div class="form-group d-flex justify-content-center">
                          <?php echo $reCAPTCHA->getHtml(); ?>
                        </div>
                      <?php endif; ?>
                      <?php echo $csrf->input('verifyTFA'); ?>
                      <button type="submit" class="btn btn-banner-bg w-100" name="verifyTFA"><?php e__('Verify') ?></button>
                    </form>
                  </div>
                  <div class="card-footer text-center">
                    <a href="https://help.leaderos.net/google-authenticator" rel="external"><?php e__('How to use Google Authenticator?') ?></a>
                  </div>
                </div>
              <?php else: ?>
                <?php unset($_SESSION["tfa"]); ?>
                <?php echo alertError(t__('User not found!')); ?>
              <?php endif; ?>
            <?php else: ?>
              <?php unset($_SESSION["tfa"]); ?>
              <?php echo alertError(t__('IP address is invalid!')); ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>
