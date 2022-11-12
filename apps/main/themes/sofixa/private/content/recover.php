<?php
  if (isset($_SESSION["login"])) {
    go("/profile");
  }
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\SMTP;
  use PHPMailer\PHPMailer\Exception;
  use Phelium\Component\reCAPTCHA;
  $recaptchaPagesStatusJSON = $readSettings["recaptchaPagesStatus"];
  $recaptchaPagesStatus = json_decode($recaptchaPagesStatusJSON, true);
  $recaptchaStatus = $readSettings["recaptchaPublicKey"] != '0' && $readSettings["recaptchaPrivateKey"] != '0' && $recaptchaPagesStatus["recoverPage"] == 1;
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
<section class="section section-recover">
  <div class="container">
    <div class="row">
      <div class="col-md-4 offset-md-4">
        <?php if (get("id") && get("token")): ?>
          <?php
            require_once(__ROOT__."/apps/main/private/packages/class/csrf/csrf.php");
            $csrf = new CSRF('csrf-sessions', 'csrf-token');
            $checkToken = $db->prepare("SELECT * FROM AccountRecovers WHERE accountID = ? AND recoverToken = ? AND creationIP = ? AND expiryDate > ?");
            $checkToken->execute(array(get("id"), get("token"), getIP(), date("Y-m-d H:i:s")));
          ?>
          <?php if ($checkToken->rowCount() > 0): ?>
            <?php
              if (isset($_POST["recoverAccount"])) {
                if (!$csrf->validate('recoverAccount')) {
                  echo alertError(t__('Something went wrong! Please try again later.'));
                }
                else if (post("password") == null || post("passwordRe") == null) {
                  echo alertError(t__('Please fill all the fields!'));
                }
                else if (post("password") != post("passwordRe")) {
                  echo alertError(t__('Passwords do not match!'));
                }
                else if (strlen(post("password")) < 4) {
                  echo alertError(t__('Password must be at least 4 characters long!'));
                }
                else if (checkBadPassword(post("password"))) {
                  echo alertError(t__('Your password is too weak!'));
                }
                else {
                  if ($readSettings["passwordType"] == 1)
                    $password = createSHA256(post("password"));
                  elseif ($readSettings["passwordType"] == 2)
                    $password = md5(post("password"));
                  else
                    $password = password_hash(post("password"), PASSWORD_BCRYPT);
                  
                  $updateAccounts = $db->prepare("UPDATE Accounts SET password = ? WHERE id = ?");
                  $updateAccounts->execute(array($password, get("id")));
                  $deleteAccountRecovers = $db->prepare("DELETE FROM AccountRecovers WHERE accountID = ?");
                  $deleteAccountRecovers->execute(array(get("id")));
                  $deleteAccountSessions = $db->prepare("DELETE FROM AccountSessions WHERE accountID = ?");
                  $deleteAccountSessions->execute(array(get("id")));
                  echo alertSuccess(t__('Your password has been changed successfully! You are redirected...'));
                  echo goDelay("/login", 2);
                }
              }
            ?>
            <div class="card">
              <div class="card-header">
                <?php e__('Change Password') ?>
              </div>
              <div class="card-body">
                <form action="" method="post">
                  <div class="form-group">
                    <input type="password" class="form-control" name="password" placeholder="<?php e__('New Password') ?>">
                  </div>
                  <div class="form-group">
                    <input type="password" class="form-control" name="passwordRe" placeholder="<?php e__('Confirm New Password') ?>">
                  </div>
                  <?php echo $csrf->input('recoverAccount'); ?>
                  <button type="submit" class="theme-color btn btn-primary w-100" name="recoverAccount"><?php e__('Change Password') ?></button>
                </form>
              </div>
            </div>
          <?php else: ?>
            <?php echo alertError(t__('Password reset link is invalid!')); ?>
          <?php endif; ?>
        <?php else: ?>
          <?php
            require_once(__ROOT__."/apps/main/private/packages/class/csrf/csrf.php");
            $csrf = new CSRF('csrf-sessions', 'csrf-token');
            if (isset($_POST["sendEmail"])) {
              if (!$csrf->validate('sendEmail')) {
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
              else if (post("username") == null || post("email") == null) {
                echo alertError(t__('Please fill all the fields!'));
              }
              else {
                $checkAccount = $db->prepare("SELECT * FROM Accounts WHERE realname = ? AND email = ?");
                $checkAccount->execute(array(post("username"), post("email")));
                $readAccount = $checkAccount->fetch();
                if ($checkAccount->rowCount() > 0) {
                  $recoverToken = md5(uniqid(mt_rand(), true));
                  $url = ((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === 'on' ? "https" : "http").'://'.$_SERVER["SERVER_NAME"].'/recover-account/'.$readAccount["id"].'/'.$recoverToken);
                  $search = array("%username%", "%url%");
                  $replace = array($readAccount["realname"], $url);
                  $template = $readSettings["smtpPasswordTemplate"];
                  $content = str_replace($search, $replace, $template);
                  require_once(__ROOT__."/apps/main/private/packages/class/phpmailer/exception.php");
                  require_once(__ROOT__."/apps/main/private/packages/class/phpmailer/phpmailer.php");
                  require_once(__ROOT__."/apps/main/private/packages/class/phpmailer/smtp.php");
                  $phpMailer = new PHPMailer(true);
                  try {
                    $phpMailer->IsSMTP();
                    $phpMailer->setLanguage('tr', __ROOT__.'/apps/main/private/packages/class/phpmailer/lang/');
                    $phpMailer->SMTPAuth = true;
                    $phpMailer->Host = $readSettings["smtpServer"];
                    $phpMailer->Port = $readSettings["smtpPort"];
                    $phpMailer->SMTPSecure = (($readSettings["smtpSecure"] == 1) ? PHPMailer::ENCRYPTION_SMTPS : (($readSettings["smtpSecure"] == 2) ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS));
                    $phpMailer->Username = $readSettings["smtpUsername"];
                    $phpMailer->Password = $readSettings["smtpPassword"];
                    $phpMailer->SetFrom($phpMailer->Username, $readSettings["serverName"]);
                    $phpMailer->AddAddress($readAccount["email"], $readAccount["realname"]);
                    $phpMailer->isHTML(true);
                    $phpMailer->CharSet = 'UTF-8';
                    $phpMailer->Subject = $readSettings["serverName"]." - ".t__('Reset your password!');
                    $phpMailer->Body = $content;
                    $phpMailer->send();
                    $checkAccountRecovers = $db->prepare("SELECT * FROM AccountRecovers WHERE accountID = ?");
                    $checkAccountRecovers->execute(array($readAccount["id"]));
                    if ($checkAccountRecovers->rowCount() > 0) {
                      $updateAccountRecovers = $db->prepare("UPDATE AccountRecovers SET recoverToken = ?, creationIP = ?, expiryDate = ?, creationDate = ? WHERE accountID = ?");
                      $updateAccountRecovers->execute(array($recoverToken, getIP(), createDuration(0.04166666666), date("Y-m-d H:i:s"), $readAccount["id"]));
                    }
                    else {
                      $insertAccountRecovers = $db->prepare("INSERT INTO AccountRecovers (accountID, recoverToken, creationIP, expiryDate, creationDate) VALUES (?, ?, ?, ?, ?)");
                      $insertAccountRecovers->execute(array($readAccount["id"], $recoverToken, getIP(), createDuration(0.04166666666), date("Y-m-d H:i:s")));
                    }
                    echo alertSuccess(t__('A reset link has been sent to your email address!'));
                  } catch (Exception $e) {
                    echo alertError(t__('Could not send mail due to a system error:')." ".$e->errorMessage());
                  }
                }
                else {
                  echo alertError(t__('Username and email address do not match!'));
                }
              }
            }
          ?>
          <div class="card">
            <div class="card-header">
              <?php e__('Recover Account') ?>
            </div>
            <div class="card-body">
              <form action="" method="post">
                <div class="form-group">
                  <input type="text" class="form-control" name="username" placeholder="<?php e__('Username') ?>">
                </div>
                <div class="form-group">
                  <input type="email" class="form-control" name="email" placeholder="<?php e__('Email') ?>">
                </div>
                <?php if ($recaptchaStatus): ?>
                  <div class="form-group d-flex justify-content-center">
                    <?php echo $reCAPTCHA->getHtml(); ?>
                  </div>
                <?php endif; ?>
                <?php echo $csrf->input('sendEmail'); ?>
                <button type="submit" class="theme-color btn btn-primary w-100" name="sendEmail"><?php e__('Send') ?></button>
              </form>
            </div>
            <div class="card-footer text-center">
              <?php e__('Remember your password?') ?>
              <a href="/login"><?php e__('Login') ?></a>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
