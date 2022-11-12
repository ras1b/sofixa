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
  .sfx-login-row {
    flex-shrink: 1;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    align-self: center;
    margin: auto;
    height: 80vh;
  }
</style>
<?php
if (isset($_SESSION["login"])) {
  go("/profile");
}
use Phelium\Component\reCAPTCHA;
$recaptchaPagesStatusJSON = $readSettings["recaptchaPagesStatus"];
$recaptchaPagesStatus = json_decode($recaptchaPagesStatusJSON, true);
$recaptchaStatus = $readSettings["recaptchaPublicKey"] != '0' && $readSettings["recaptchaPrivateKey"] != '0' && $recaptchaPagesStatus["loginPage"] == 1;
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
<div class="w-100 position-relative">
  <div class="rounded:full sfx-turn-home-button">
    <a href="/">
      <i class="bi bi-arrow-return-left"></i>
    </a>
  </div>  
</div>
<section class="section page-section">
  <div class="container">
    <div class="row sfx-login-row">
      <div class="col-md-6">
        <?php
        require_once(__ROOT__."/apps/main/private/packages/class/csrf/csrf.php");
        $csrf = new CSRF('csrf-sessions', 'csrf-token');
        if (isset($_POST["login"])) {
          if (!$csrf->validate('login')) {
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
          else if (post("username") == null || post("password") == null) {
            echo alertError(t__('Please fill all the fields!'));
          }
          else {
            $login = $db->prepare("SELECT * FROM Accounts WHERE realname = ?");
            $login->execute(array(post("username")));
            $readAccount = $login->fetch();
            if ($login->rowCount() > 0) {
              if ($readSettings["passwordType"] == 1)
                $password = checkSHA256(post("password"), $readAccount["password"]);
              elseif ($readSettings["passwordType"] == 2)
                $password = md5(post("password")) == $readAccount["password"];
              else
                $password = password_verify(post("password"), $readAccount["password"]);
              if ($password == true) {
                $siteBannedStatus = $db->prepare("SELECT * FROM BannedAccounts WHERE accountID = ? AND categoryID = ? AND (expiryDate > ? OR expiryDate = ?)");
                $siteBannedStatus->execute(array($readAccount["id"], 1, date("Y-m-d H:i:s"), '1000-01-01 00:00:00'));
                if ($siteBannedStatus->rowCount() > 0) {
                  echo alertError(t__('Your account is banned!'));
                }
                else {
                  if ($readSettings["maintenanceStatus"] == 1 && ($readAccount["permission"] == 0 || $readAccount["permission"] == 6)) {
                    echo alertError(t__('The site is under maintenance!'));
                  }
                  else {
                    if ($readSettings["authStatus"] == 1 && $readAccount["authStatus"] == 1) {
                      $_SESSION["tfa"] = array(
                        'accountID'   => $readAccount["id"],
                        'rememberMe'  => (post("rememberMe")) ? 'true' : 'false',
                        'ipAddress'   => getIP(),
                        'expiryDate'  => createDuration(0.00347222222)
                      );
                      go("/verify");
                    }
                    else {
                      $loginType = 'NEW';
                      if ($loginType == 'NEW') {
                        $db->beginTransaction();
                        $deleteOldSessions = $db->prepare("DELETE FROM AccountSessions WHERE accountID = ?");
                        $deleteOldSessions->execute(array($readAccount["id"]));

                        $loginToken = md5(uniqid(mt_rand(), true));
                        $insertAccountSessions = $db->prepare("INSERT INTO AccountSessions (accountID, loginToken, creationIP, expiryDate, creationDate) VALUES (?, ?, ?, ?, ?)");
                        $insertAccountSessions->execute(array($readAccount["id"], $loginToken, getIP(), createDuration(((isset($_POST["rememberMe"])) ? 365 : 0.01666666666)), date("Y-m-d H:i:s")));

                        if ($deleteOldSessions && $insertAccountSessions){
                            $db->commit(); // işlemi tamamla
                            if (post("rememberMe")) {
                              createCookie("rememberMe", $loginToken, 365, $sslStatus);
                            }
                            $_SESSION["login"] = $loginToken;
                            go("/profile");
                          }
                          else {
                            $db->rollBack(); // işlemi geri al
                            alertError(t__('Error!'));
                          }
                        }
                        else {
                          $loginToken = md5(uniqid(mt_rand(), true));
                          $insertAccountSessions = $db->prepare("INSERT INTO AccountSessions (accountID, loginToken, creationIP, expiryDate, creationDate) VALUES (?, ?, ?, ?, ?)");
                          $insertAccountSessions->execute(array($readAccount["id"], $loginToken, getIP(), createDuration(((isset($_POST["rememberMe"])) ? 365 : 0.01666666666)), date("Y-m-d H:i:s")));

                          if (post("rememberMe")) {
                            createCookie("rememberMe", $loginToken, 365, $sslStatus);
                          }
                          $_SESSION["login"] = $loginToken;
                          go("/profile");
                        }
                      }
                    }
                  }
                }
                else {
                  echo alertError(t__('Wrong password!'));
                }
              }
              else {
                echo alertError(t__('<strong>%username%</strong> not found!', ['%username%' => post("username")]));
              }
            }
          }
          ?>
          <div class="card">
            <div class="card-header">
              <?php e__('Login') ?>
            </div>
            <div class="card-body">
              <form action="" method="post">
                <div class="w-100 text-center mb-3">
                  <img class="w-25" src="/apps/main/public/assets/img/extras/header-logo.png" alt=""><br>
                  <span class="h5"><?php e__('Login') ?></span>                
                </div>
                <div class="w-100">
                  <div class="form-group mt-2 position-relative">
                    <i class="bi bi-person-fill sfx-icon-absolute-login"></i> 
                    <input type="text" class="form-control text-center p-4 border-radius:full" name="username" placeholder="<?php e__('Username') ?>" value="<?php echo ((post("username")) ? post("username") : null); ?>">
                  </div>
                  <div class="form-group mt-2 position-relative">
                    <i class="bi bi-lock-fill sfx-icon-absolute-login"></i> 
                    <input type="password" class="form-control text-center p-4 border-radius:full" name="password" placeholder="<?php e__('Password') ?>">
                  </div>
                  <div class="form-group custom-control custom-checkbox">
                    <div class="row">
                      <div class="col">
                        <input type="checkbox" id="rememberMe" class="custom-control-input" name="rememberMe">
                        <label for="rememberMe" class="custom-control-label" name="rememberMe"><?php e__('Remember me') ?></label>
                      </div>
                      <div class="col-auto">
                        <a href="/recover-account"><?php e__('Forgot Password?') ?></a>
                      </div>
                    </div>
                  </div>
                </div>
                <?php if ($recaptchaStatus): ?>
                  <div class="form-group d-flex justify-content-center">
                    <?php echo $reCAPTCHA->getHtml(); ?>
                  </div>
                <?php endif; ?>
                <?php echo $csrf->input('login'); ?>
                <button type="submit" class="btn btn-banner-bg w-100" name="login"><?php e__('Login') ?></button>
              </form>
            </div>
            <div class="card-footer text-center">
              <?php e__("Don't have an account?") ?>
              <a href="/register"><?php e__('Register') ?></a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
