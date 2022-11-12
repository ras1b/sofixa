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
$recaptchaStatus = $readSettings["recaptchaPublicKey"] != '0' && $readSettings["recaptchaPrivateKey"] != '0' && $recaptchaPagesStatus["registerPage"] == 1;
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
        if (isset($_POST["insertAccounts"])) {
          if (!$csrf->validate('insertAccounts')) {
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
          else if (post("username") == null || post("email") == null || post("password") == null || post("passwordRe") == null) {
            echo alertError(t__('Please fill all the fields!'));
          }
          else {
            $usernameValid = $db->prepare("SELECT * FROM Accounts WHERE realname = ?");
            $usernameValid->execute(array(post("username")));

            $emailValid = $db->prepare("SELECT * FROM Accounts WHERE email = ?");
            $emailValid->execute(array(post("email")));

            $ipCount = $db->prepare("SELECT * FROM Accounts WHERE creationIP = ?");
            $ipCount->execute(array(getIP()));

            $badUsernameList = array(
              'yarrak',
              'sikis',
              'serefsiz',
              'amcik',
              'orospu'
            );

            if (!post("acceptRules")) {
              echo alertError(t__('Please accept the rules!'));
            }
            else if ($registerLimit != 0 && $ipCount->rowCount() >= $registerLimit) {
              echo alertError(t__('You have reached the limit of registrations!'));
            }
            else if (checkUsername(post("username"))) {
              echo alertError(t__('Username is not valid!'));
            }
            else if (strlen(post("username")) < 3) {
              echo alertError(t__('Username must be at least 3 characters long!'));
            }
            else if (strlen(post("username")) > 16) {
              echo alertError(t__('Username must be less than 16 characters!'));
            }
            else if (checkEmail(post("email"))) {
              echo alertError(t__('Please enter a valid email address!'));
            }
            else if ($usernameValid->rowCount() > 0) {
              echo alertError(t__('<strong>%username%</strong> already registered!', ['%username%' => post("username")]));
            }
            else if ($emailValid->rowCount() > 0) {
              echo alertError(t__('<strong>%email%</strong> already registered!', ['%email%' => post("email")]));
            }
            else if (strlen(post("password")) < 4) {
              echo alertError(t__('Password must be at least 4 characters long!'));
            }
            else if (post("password") != post("passwordRe")) {
              echo alertError(t__('Passwords do not match!'));
            }
            else if (checkBadPassword(post("password"))) {
              echo alertError(t__('Your password is too weak!'));
            }
            else if (checkBadUsername(post("username"), $badUsernameList)) {
              echo alertError(t__('Bad username detected!'));
            }
            else {
              $loginToken = md5(uniqid(mt_rand(), true));
              if ($readSettings["passwordType"] == 1)
                $password = createSHA256(post("password"));
              elseif ($readSettings["passwordType"] == 2)
                $password = md5(post("password"));
              else
                $password = password_hash(post("password"), PASSWORD_BCRYPT);
              $insertAccounts = $db->prepare("INSERT INTO Accounts (username, realname, email, password, creationIP, creationDate) VALUES (?, ?, ?, ?, ?, ?)");
              $insertAccounts->execute(array(strtolower(post("username")), post("username"), post("email"), $password, getIP(), date("Y-m-d H:i:s")));
              $accountID = $db->lastInsertId();
              $insertAccountSessions = $db->prepare("INSERT INTO AccountSessions (accountID, loginToken, creationIP, expiryDate, creationDate) VALUES (?, ?, ?, ?, ?)");
              $insertAccountSessions->execute(array($accountID, $loginToken, getIP(), createDuration(0.01666666666), date("Y-m-d H:i:s")));
              $_SESSION["login"] = $loginToken;
              echo alertSuccess(t__('You have successfully registered! You are redirected...'));
              echo goDelay("/profile", 2);
            }
          }
        }
        ?>
        <div class="card">
          <div class="card-header">
            <?php e__('Register') ?>
          </div>
          <div class="card-body">
            <form action="" method="post">
              <div class="w-100 text-center mb-3">
                <img class="w-25" src="/apps/main/public/assets/img/extras/header-logo.png" alt=""><br>
                <span class="h5"><?php e__('Register') ?></span>
              </div>
              <div class="form-group mt-2 position-relative">
                <i class="bi bi-person-fill sfx-icon-absolute-login"></i> 
                <input type="text" class="form-control p-4 text-center border-radius:full" name="username" placeholder="<?php e__('Username') ?>" value="<?php echo ((post("username")) ? post("username") : null); ?>">
              </div>
              <div class="form-group mt-2 position-relative">
                <i class="bi bi-envelope-fill sfx-icon-absolute-login"></i>
                <div class="form-group">
                  <input type="email" class="form-control p-4 text-center border-radius:full" name="email" placeholder="<?php e__('Email') ?>" value="<?php echo ((post("email")) ? post("email") : null); ?>">
                </div>
              </div>
              <div class="form-group mt-2 position-relative">
                <i class="bi bi-lock-fill sfx-icon-absolute-login"></i>
                <input type="password" class="form-control p-4 text-center border-radius:full" name="password" placeholder="<?php e__('Password') ?>">
              </div>
              <div class="form-group mt-2 position-relative">
                <i class="bi bi-lock-fill sfx-icon-absolute-login"></i> 
                <input type="password" class="form-control p-4 text-center border-radius:full" name="passwordRe" placeholder="<?php e__('Confirm Password') ?>">
              </div>
              <div class="form-group mt-2 position-relative custom-control custom-checkbox text-center">
                <input type="checkbox" class="custom-control-input" id="acceptRules" name="acceptRules" checked="checked">
                <label class="custom-control-label" for="acceptRules">
                  <?php e__('I read the <a href="/rules" rel="external">Rules</a> and I accept.') ?>
                </label>
              </div>
              <?php if ($recaptchaStatus): ?>
                <div class="form-group d-flex justify-content-center">
                  <?php echo $reCAPTCHA->getHtml(); ?>
                </div>
              <?php endif; ?>
              <?php echo $csrf->input('insertAccounts'); ?>
              <button type="submit" class="btn btn-banner-bg w-100" name="insertAccounts"><?php e__('Register') ?></button>
            </form>
          </div>
          <div class="card-footer text-center">
            <?php e__('Do you have an account?') ?>
            <a href="/login"><?php e__('Login') ?></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
