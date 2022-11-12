<?php
if (!isset($_SESSION["login"])) {
  go("/login");
}
if (get("action") == 'send' && get("id")) {
  $receiverAccount = $db->prepare("SELECT * FROM Accounts WHERE id = ?");
  $receiverAccount->execute(array(get("id")));
  $readReceiverAccount = $receiverAccount->fetch();
}
?>
<section class="section credit-section">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><?php e__('Home') ?></a></li>
            <?php if (get("target") == "credit"): ?>
              <?php if (get("action") == "buy"): ?>
                <li class="breadcrumb-item"><a href="/credit/buy"><?php e__('Credit') ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php e__('Buy Credits') ?></li>
              <?php elseif (get("action") == "send"): ?>
                <?php if (get("id")): ?>
                  <li class="breadcrumb-item"><a href="/credit/buy"><?php e__('Credit') ?></a></li>
                  <li class="breadcrumb-item"><a href="/credit/send"><?php e__('Send Credits') ?></a></li>
                  <?php if ($receiverAccount->rowCount() > 0): ?>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo $readReceiverAccount["realname"]; ?></li>
                  <?php else: ?>
                    <li class="breadcrumb-item active" aria-current="page"><?php e__('Not Found!') ?></li>
                  <?php endif; ?>
                <?php else: ?>
                  <li class="breadcrumb-item"><a href="/credit/buy"><?php e__('Credit') ?></a></li>
                  <li class="breadcrumb-item active" aria-current="page"><?php e__('Send Credits') ?></li>
                <?php endif; ?>
              <?php elseif (get("action") == "pay"): ?>
                <li class="breadcrumb-item"><a href="/credit/buy"><?php e__('Credit') ?></a></li>
                <li class="breadcrumb-item"><a href="/credit/buy"><?php e__('Send Credits') ?></a></li>
                <?php if (get("api") == "paytr"): ?>
                  <li class="breadcrumb-item active" aria-current="page">PayTR</li>
                <?php elseif (get("api") == "ininal"): ?>
                  <li class="breadcrumb-item active" aria-current="page">Ininal</li>
                <?php elseif (get("api") == "papara"): ?>
                  <li class="breadcrumb-item active" aria-current="page">Papara</li>
                <?php elseif (get("api") == "eft"): ?>
                  <li class="breadcrumb-item active" aria-current="page">EFT</li>
                <?php else: ?>
                  <li class="breadcrumb-item active" aria-current="page"><?php e__('Not Found!') ?></li>
                <?php endif; ?>
              <?php else: ?>
                <li class="breadcrumb-item"><a href="/credit/buy"><?php e__('Credit') ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php e__('Buy Credits') ?></li>
              <?php endif; ?>
            <?php else: ?>
              <?php go("/404"); ?>
            <?php endif; ?>
          </ol>
        </nav>
      </div>
      <div class="col-md-8">
        <?php if (get("target") == 'credit'): ?>
          <?php if (get("action") == 'buy'): ?>
            <?php if ($readAccount["email"] != "your@email.com" && $readAccount["email"] != "guncelle@gmail.com"): ?>
              <?php
              require_once(__ROOT__."/apps/main/private/packages/class/csrf/csrf.php");
              $csrf = new CSRF('csrf-sessions', 'csrf-token');
              ?>
              <?php if ($readSettings["creditMultiplier"] != 1): ?>
                <?php echo alertWarning(t__('<strong>1 %currency% equals %multiplier% credits!</strong>', ['%currency%' => $readSettings["currency"], '%multiplier%' => $readSettings["creditMultiplier"]])); ?>
              <?php endif; ?>
              <?php if ($readSettings["bonusCredit"] != 0): ?>
                <?php echo alertWarning(t__('%credit%% bonus credits on purchases!', ['%credit%' => $readSettings["bonusCredit"]])); ?>
              <?php endif; ?>
              <div class="card">
                <div class="card-header p-3">
                  <?php e__('Buy Credits') ?>
                </div>
                <div class="card-body">
                  <form id="buyCreditsForm" action="/apps/main/public/ajax/pay.php" method="post">
                    <div class="col-md-12">
                      <div class="row justify-content-center">
                        <div class="col-md-6">
                          <div class="card text-center">
                            <div class="card-body">
                              <div class="w-100">
                                <img class="w-50 mb-2" src="/apps/main/themes/sofixa/public/assets/img/extras/coins.webp" alt="">
                                <div class="sfx-input-icon">
                                  <input type="number" id="inputPrice" class="form-control" name="price" placeholder="<?php e__('Enter the amount.') ?>" aria-label="Miktar" aria-describedby="ariaPrice" min="<?php echo $readSettings["minPay"]; ?>" max="<?php echo $readSettings["maxPay"]; ?>" required="required">
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="card text-center">
                            <div class="card-body">
                              <div class="w-100">
                                <img class="w-50 mb-2" src="/apps/main/themes/sofixa/public/assets/img/extras/wallet.webp" alt="">
                                <div class="sfx-input-icon">
                                 <select id="selectPayment" class="form-control" name="paymentID" data-toggle="select2" required="required">
                                  <?php
                                  $payment = $db->prepare("SELECT P.* FROM Payment P INNER JOIN PaymentSettings PS ON P.apiID = PS.slug WHERE PS.status = ? ORDER BY P.id DESC");
                                  $payment->execute(array(1));
                                  ?>
                                  <?php if ($payment->rowCount() > 0): ?>
                                    <?php foreach ($payment as $readPayment): ?>
                                      <option value="<?php echo $readPayment["id"]; ?>"><?php echo $readPayment["title"]; ?></option>
                                    <?php endforeach; ?>
                                  <?php else: ?>
                                    <option><?php e__('Payment method not found!') ?></option>
                                  <?php endif; ?>
                                </select>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <?php if ($payment->rowCount() > 0): ?>
                    <hr>
                    <?php
                    $accountContactInfo = $db->prepare("SELECT * FROM AccountContactInfo WHERE accountID = ?");
                    $accountContactInfo->execute(array($readAccount["id"]));
                    $readAccountContactInfo = $accountContactInfo->fetch();
                    ?>
                    <div class="form-group row">
                      <label for="inputFirstName" class="col-sm-2 col-form-label"><?php e__('First Name') ?>:</label>
                      <div class="col-sm-10">
                        <input type="text" class="form-control" id="inputFirstName" placeholder="<?php e__('Enter your first name.') ?>" name="firstName" required="required" value="<?php echo (isset($readAccountContactInfo["firstName"])) ? $readAccountContactInfo["firstName"] : null; ?>">
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="inputLastName" class="col-sm-2 col-form-label"><?php e__('Last Name') ?>:</label>
                      <div class="col-sm-10">
                        <input type="text" class="form-control" id="inputLastName" placeholder="<?php e__('Enter your last name.') ?>" name="lastName" required="required" value="<?php echo (isset($readAccountContactInfo["lastName"])) ? $readAccountContactInfo["lastName"] : null; ?>">
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="inputEmail" class="col-sm-2 col-form-label"><?php e__('Email') ?>:</label>
                      <div class="col-sm-10">
                        <input type="email" class="form-control" id="inputEmail" required="required" value="<?php echo $readAccount["email"]; ?>" readonly="readonly">
                      </div>
                    </div>
                  <?php endif; ?>
                  <?php echo $csrf->input('buyCredits'); ?>
                  <div class="clearfix">
                    <div class="float-right">
                      <button type="submit" class="btn btn-banner-bg" name="buyCredits"><?php e__('Buy Credits') ?></button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          <?php else: ?>
            <?php echo alertError(t__('To buy credits, you need to update your email.')); ?>
            <a href="/profile/edit" class="btn btn-success w-100"><?php e__('Click to update your email.') ?></a>
          <?php endif; ?>
        <?php elseif (get("action") == 'send' && $readSettings["creditStatus"] == 1): ?>
          <?php
          require_once(__ROOT__."/apps/main/private/packages/class/csrf/csrf.php");
          $csrf = new CSRF('csrf-sessions', 'csrf-token');
          if (isset($_POST["sendCredit"])) {
            $receiverAccount = $db->prepare("SELECT * FROM Accounts WHERE realname = ?");
            $receiverAccount->execute(array(post("username")));
            $readReceiverAccount = $receiverAccount->fetch();

            if (!$csrf->validate('sendCredit')) {
              echo alertError(t__('Something went wrong! Please try again later.'));
            }
            else if (post("username") == null || post("price") == null) {
              echo alertError(t__('Please fill all the fields!'));
            }
            else if (post("price") <= 0) {
              echo alertError(t__('Please enter a valid price!'));
            }
            else if ($receiverAccount->rowCount() == 0) {
              echo alertError(t__('<strong>%username%</strong> not found!', ['%username%' => post("username")]));
            }
            else if ($readAccount["id"] == $readReceiverAccount["id"]) {
              echo alertError(t__("You can't send credits to yourself!"));
            }
            else if (post("price") > $readAccount["credit"]) {
              echo alertError(t__("You don't have enough credits!"));
            }
            else if (!is_numeric(post("price"))) {
              echo alertError(t__('Please enter a valid price!'));
            }
            else {
              $db->beginTransaction();

              $updateSenderAccount = $db->prepare("UPDATE Accounts SET credit = credit - :amount  WHERE id = :sender");
              $updateSenderAccount->execute(array(
                ":amount" => post("price"),
                ":sender" => $readAccount["id"]
              ));

              $updateReceiverAccount = $db->prepare("UPDATE Accounts SET credit = credit + :amount WHERE id = :receiver");
              $updateReceiverAccount->execute(array(
                ":amount"   => post("price"),
                ":receiver" => $readReceiverAccount["id"]
              ));

              $insertCreditHistory = $db->prepare("INSERT INTO CreditHistory (accountID, paymentID, paymentStatus, type, price, earnings, creationDate) VALUES (?, ?, ?, ?, ?, ?, ?)");
              $insertCreditHistory->execute(array($readAccount["id"], 0, 1, 3, post("price"), 0, date("Y-m-d H:i:s")));
              $insertCreditHistory->execute(array($readReceiverAccount["id"], 0, 1, 4, post("price"), 0, date("Y-m-d H:i:s")));

              if ($updateSenderAccount && $updateReceiverAccount && $insertCreditHistory) {
                    $db->commit(); // işlemi tamamla
                    echo alertSuccess(t__('%credit% credit(s) have been successfully sent to %username%!', ['%credit%' => post("price"), '%username%' => post("username")]));
                  }
                  else {
                    $db->rollBack(); // işlemi geri al
                    echo alertError(t__('Something went wrong! Please try again later.'));
                  }
                }
              }
              ?>
              <div class="card">
                <div class="card-header">
                  <?php e__('Send Credits') ?>
                </div>
                <div class="card-body">
                  <form action="" method="post">
                    <div class="form-group row">
                      <label for="inputUsername" class="col-sm-2 col-form-label"><?php e__('Username') ?>:</label>
                      <div class="col-sm-10">
                        <input type="text" id="inputUsername" class="form-control" name="username" placeholder="<?php e__('Enter the username.') ?>" value="<?php echo (get("id") && $receiverAccount->rowCount() > 0) ? $readReceiverAccount["realname"] : null; ?>">
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="inputPrice" class="col-sm-2 col-form-label"><?php e__('Amount') ?>:</label>
                      <div class="col-sm-10">
                        <div class="input-group">
                          <input type="number" id="inputPrice" class="form-control" name="price" placeholder="<?php e__('Enter the amount.') ?>">
                          <div class="input-group-append">
                            <span id="ariaPrice" class="input-group-text"><?php echo $readSettings["creditIcon"] ?></span>
                          </div>
                        </div>
                      </div>
                    </div>
                    <?php echo $csrf->input('sendCredit'); ?>
                    <div class="clearfix">
                      <div class="float-right">
                        <button type="submit" class="btn btn-rounded btn-success" name="sendCredit" onclick="return confirm('<?php e__('Are you sure you want to send the credit to this player?') ?>')"><?php e__('Send Credit') ?></button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            <?php elseif (get("action") == "pay"): ?>
              <?php if (get("api") == 'paytr'): ?>
                <div class="card">
                  <div class="card-header">
                    Kredi Yükle
                  </div>
                  <div class="card-body">
                    <div class="iframe-payment-content">
                      <?php if (isset($_SESSION["PAYTR_IFRAME_TOKEN"])): ?>
                        <script src="https://www.paytr.com/js/iframeResizer.min.js"></script>
                        <iframe src="https://www.paytr.com/odeme/guvenli/<?php echo $_SESSION["PAYTR_IFRAME_TOKEN"]; ?>" id="paytriframe" frameborder="0" scrolling="no" style="width: 100%;"></iframe>
                        <script>iFrameResize({}, "#paytriframe");</script>
                        <?php unset($_SESSION["PAYTR_IFRAME_TOKEN"]); ?>
                      <?php else: ?>
                        <?php go("/credit/buy"); ?>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php elseif (get("api") == 'ininal'): ?>
                <?php
                $ininal = $db->prepare("SELECT variables FROM PaymentSettings WHERE slug = ?");
                $ininal->execute(array('ininal'));
                $readIninal = $ininal->fetch();
                $readVariables = json_decode($readIninal["variables"], true);
                ?>
                <?php if (count(array_filter($readVariables["ininalBarcodes"]))): ?>
                  <?php foreach ($readVariables["ininalBarcodes"] as $ininalBarcode): ?>
                    <div class="alert alert-success d-flex flex-column align-items-center">
                      <p class="mb-1"><strong>BARKOD NO:</strong></p>
                      <p class="mb-1"><?php echo $ininalBarcode; ?></p>
                    </div>
                  <?php endforeach; ?>
                  <div class="alert alert-primary">
                    <p class="mb-2"><strong>Nasıl Para Gönderebilirim?</strong></p>
                    <p class="mb-1"><strong>1)</strong> İninal Mobil Uygulamasına giriş yapınız.</p>
                    <p class="mb-1"><strong>2)</strong> Alt kısımdan <strong>"İşlemler"</strong> sekmesine geçinız.</p>
                    <p class="mb-1"><strong>3)</strong> <strong>"Para Gönder"</strong> butonuna tıklayınız.</p>
                    <p class="mb-1"><strong>4)</strong> <strong>"Alıcı Kart Barkodu"</strong>'na tıklayınız.</p>
                    <p class="mb-1"><strong>5)</strong> Açılan kamerada <strong>"Barkod numarasını kendim girmek istiyorum"</strong>'a tıklayınız.</p>
                    <p class="mb-1"><strong>6)</strong> Açılan sayfaya sitemizdeki Barkod NO'sunu yazınız ve <strong>"Devam Et"</strong> butonuna tıklayınız.</p>
                    <p class="mb-1"><strong>7)</strong> Miktar kısmına yükleyeceğiniz kredi miktarını yazınız.</p>
                    <p class="mb-1"><strong>8)</strong> Açıklama kısmına, yükleme yapılacak hesabın kullanıcı adını yazınız.</p>
                    <p class="mb-1"><strong>9)</strong> Ödeme yaptıktan sonra <strong>Destek Bildirimi</strong> açınız.</p>
                  </div>
                <?php else: ?>
                  <?php echo alertError("Barkod numarası bulunamadı!"); ?>
                <?php endif; ?>
              <?php elseif (get("api") == 'papara'): ?>
                <?php
                $papara = $db->prepare("SELECT variables FROM PaymentSettings WHERE slug = ?");
                $papara->execute(array('papara'));
                $readPapara = $papara->fetch();
                $readVariables = json_decode($readPapara["variables"], true);
                ?>
                <?php if (count(array_filter($readVariables["paparaNumbers"]))): ?>
                  <?php foreach ($readVariables["paparaNumbers"] as $paparaNumber): ?>
                    <div class="alert alert-success d-flex flex-column align-items-center">
                      <p class="mb-1"><strong>PAPARA NO:</strong></p>
                      <p class="mb-1"><?php echo $paparaNumber; ?></p>
                    </div>
                  <?php endforeach; ?>
                  <div class="alert alert-primary">
                    <p class="mb-2"><strong>Nasıl Para Gönderebilirim?</strong></p>
                    <p class="mb-1"><strong>1)</strong> Papara Mobil Uygulamasına giriş yapınız.</p>
                    <p class="mb-1"><strong>2)</strong> Alt kısımdan <strong>"Gönder"</strong> sekmesine geçinız.</p>
                    <p class="mb-1"><strong>3)</strong> <strong>"Papara Numarsına"</strong> butonuna tıklayınız.</p>
                    <p class="mb-1"><strong>4)</strong> Açılan sayfaya sitemizdeki Papara NO'sunu, Gönderilecek tutarı yazınız ve <strong>"Para Gönder"</strong> butonuna tıklayınız.</p>
                    <p class="mb-1"><strong>5)</strong> Ödeme yaptıktan sonra <strong>Destek Bildirimi</strong> açınız ve mesaja <strong>"İşlem Numarasını"</strong> yazınız.</p>
                  </div>
                <?php else: ?>
                  <?php echo alertError("Papara numarası bulunamadı!"); ?>
                <?php endif; ?>
              <?php elseif (get("api") == 'eft'): ?>
                <?php
                $eft = $db->prepare("SELECT variables FROM PaymentSettings WHERE slug = ?");
                $eft->execute(array('eft'));
                $readEFT = $eft->fetch();
                $readVariables = json_decode($readEFT["variables"], true);
                ?>
                <?php if (count(array_filter($readVariables["bankAccounts"]))): ?>
                  <?php echo alertWarning('Ödeme işlemini yaptıktan sonra <strong>Destek Bildirimi</strong> açınız.'); ?>
                  <?php foreach ($readVariables["bankAccounts"] as $bankAccount): ?>
                    <div class="alert alert-success d-flex flex-column align-items-center">
                      <p class="mb-1"><strong>AD SOYAD:</strong> <?php echo $bankAccount["fullName"]; ?></p>
                      <p class="mb-1"><strong>BANKA:</strong> <?php echo $bankAccount["bankName"]; ?></p>
                      <p class="mb-1"><strong>IBAN:</strong> <?php echo $bankAccount["iban"]; ?></p>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <?php echo alertError("Banka hesabı bulunamadı!"); ?>
                <?php endif; ?>
              <?php else: ?>
                <?php echo alertError(t__('Payment method not found!')); ?>
              <?php endif; ?>
            <?php elseif (get("action") == 'alert'): ?>
              <?php if (get("result") == 'success'): ?>
                <div class="card mb-3">
                  <div class="card-header">
                    <?php e__('Payment Successful!') ?>
                  </div>
                  <div class="card-body text-success text-center">
                    <div class="mt-3">
                      <img src="/apps/main/public/assets/img/extras/success.png" width="120px">
                    </div>
                    <p class="mt-4"><?php e__('Your credit has been successfully added to your account!') ?></p>
                    <a href="/store" class="btn btn-success rounded-pill mb-3"><?php e__('Start Shopping!') ?></a>
                  </div>
                </div>
              <?php elseif (get("result") == 'unsuccess'): ?>
                <div class="card mb-3">
                  <div class="card-header">
                    <?php e__('Payment Failed!') ?>
                  </div>
                  <div class="card-body text-danger text-center">
                    <div class="mt-3">
                      <img src="/apps/main/public/assets/img/extras/unsuccess.png" width="120px">
                    </div>
                    <p class="mt-4"><?php e__('Your payment method has been declined. Please use an another method/card.') ?></p>
                    <a href="/credit/buy" class="btn btn-primary rounded-pill mb-3"><?php e__('Try Again!') ?></a>
                  </div>
                </div>
              <?php else: ?>
                <?php go("/404"); ?>
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
            <?php if (get("target") == "credit"): ?>
              <?php if (get("action") == 'send'): ?>
                <div class="col-md-12">
                  <?php
                  $creditHistory = $db->prepare("SELECT * FROM CreditHistory WHERE accountID = ? AND type IN (?, ?) AND paymentStatus = ? ORDER by id DESC LIMIT 5");
                  $creditHistory->execute(array($readAccount["id"], 3, 4, 1));
                  ?>
                  <?php if ($creditHistory->rowCount() > 0): ?>
                    <div class="card mb-3">
                      <div class="card-header">
                        <div class="row">
                          <div class="col">
                            <span><?php e__('Credit Transfer History') ?></span>
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
                                <th class="text-center"><?php e__('Amount') ?></th>
                                <th class="text-center"><?php e__('Type') ?></th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php foreach ($creditHistory as $readCreditHistory): ?>
                                <tr>
                                  <td class="text-center">
                                    <img class="rounded-circle" src="https://minotar.net/avatar/<?php echo $readAccount["realname"]; ?>/20.png" alt="<?php echo $serverName." Oyuncu - ".$readAccount["realname"]; ?>">
                                  </td>
                                  <td>
                                    <?php echo $readAccount["realname"]; ?>
                                    <?php echo verifiedCircle($readAccount["permission"]); ?>
                                  </td>
                                  <td class="text-center"><?php echo ($readCreditHistory["type"] == 3 || $readCreditHistory["type"] == 5) ? '<span class="text-danger">-'.$readCreditHistory["price"].'</span>' : '<span class="text-success">+'.$readCreditHistory["price"].'</span>'; ?></td>
                                  <td class="text-center">
                                    <?php if ($readCreditHistory["type"] == 1): ?>
                                      <i class="fa fa-mobile" data-toggle="tooltip" data-placement="top" title="<?php e__('Mobile Payment') ?>"></i>
                                    <?php elseif ($readCreditHistory["type"] == 2): ?>
                                      <i class="fa fa-credit-card" data-toggle="tooltip" data-placement="top" title="<?php e__('Credit Card') ?>"></i>
                                    <?php elseif ($readCreditHistory["type"] == 3): ?>
                                      <i class="fa fa-paper-plane" data-toggle="tooltip" data-placement="top" title="<?php e__('Transfer (Sender)') ?>"></i>
                                    <?php elseif ($readCreditHistory["type"] == 4): ?>
                                      <i class="fa fa-paper-plane" data-toggle="tooltip" data-placement="top" title="<?php e__('Transfer (Receiver)') ?> "></i>
                                    <?php elseif ($readCreditHistory["type"] == 5): ?>
                                      <i class="fa fa-ticket" data-toggle="tooltip" data-placement="top" title="<?php e__('Wheel of Fortune (Ticket)') ?>"></i>
                                    <?php elseif ($readCreditHistory["type"] == 6): ?>
                                      <i class="fa fa-ticket" data-toggle="tooltip" data-placement="top" title="<?php e__('Wheel of Fortune (Prize)') ?>"></i>
                                    <?php else: ?>
                                      <i class="fa fa-paper-plane"></i>
                                    <?php endif; ?>
                                  </td>
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
              <?php else: ?>
                <div class="col-md-12">
                  <?php
                  $creditHistory = $db->prepare("SELECT CH.*, PY.name as paymentGatewayName FROM CreditHistory CH INNER JOIN PaymentSettings PY ON CH.paymentAPI = PY.slug WHERE CH.accountID = ? AND CH.type IN (?, ?) AND CH.paymentStatus = ? ORDER by CH.id DESC LIMIT 5");
                  $creditHistory->execute(array($readAccount["id"], 1, 2, 1));
                  ?>
                  <?php if ($creditHistory->rowCount() > 0): ?>
                    <div class="card mb-3">
                      <div class="card-header">
                        <div class="row">
                          <div class="col">
                            <span><?php e__('Credit History') ?></span>
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
                                <th class="text-center"><?php e__('Amount') ?></th>
                                <th class="text-center"><?php e__('Type') ?></th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php foreach ($creditHistory as $readCreditHistory): ?>
                                <tr>
                                  <td class="text-center">
                                    <img class="rounded-circle" src="https://minotar.net/avatar/<?php echo $readAccount["realname"]; ?>/20.png" alt="<?php echo $serverName." Oyuncu - ".$readAccount["realname"]; ?>">
                                  </td>
                                  <td>
                                    <?php echo $readAccount["realname"]; ?>
                                    <?php echo verifiedCircle($readAccount["permission"]); ?>
                                  </td>
                                  <td class="text-center"><?php echo ($readCreditHistory["type"] == 3 || $readCreditHistory["type"] == 5) ? '<span class="text-danger">-'.$readCreditHistory["price"].'</span>' : '<span class="text-success">+'.$readCreditHistory["price"].'</span>'; ?></td>
                                  <td class="text-center">
                                    <?php if ($readCreditHistory["type"] == 1): ?>
                                      <i class="fa fa-mobile" data-toggle="tooltip" data-placement="top" title="<?php echo $readCreditHistory["paymentGatewayName"] ?>"></i>
                                    <?php elseif ($readCreditHistory["type"] == 2): ?>
                                      <i class="fa fa-credit-card" data-toggle="tooltip" data-placement="top" title="<?php echo $readCreditHistory["paymentGatewayName"] ?>"></i>
                                    <?php elseif ($readCreditHistory["type"] == 3): ?>
                                      <i class="fa fa-paper-plane" data-toggle="tooltip" data-placement="top" title="<?php e__('Transfer (Sender)') ?>"></i>
                                    <?php elseif ($readCreditHistory["type"] == 4): ?>
                                      <i class="fa fa-paper-plane" data-toggle="tooltip" data-placement="top" title="<?php e__('Transfer (Receiver)') ?> "></i>
                                    <?php elseif ($readCreditHistory["type"] == 5): ?>
                                      <i class="fa fa-ticket" data-toggle="tooltip" data-placement="top" title="<?php e__('Wheel of Fortune (Ticket)') ?>"></i>
                                    <?php elseif ($readCreditHistory["type"] == 6): ?>
                                      <i class="fa fa-ticket" data-toggle="tooltip" data-placement="top" title="<?php e__('Wheel of Fortune (Prize)') ?>"></i>
                                    <?php else: ?>
                                      <i class="fa fa-paper-plane"></i>
                                    <?php endif; ?>
                                  </td>
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
                <div class="col-md-12">
                  <?php
                  $topCreditHistory = $db->prepare("SELECT SUM(CH.price) as totalPrice, COUNT(CH.id) as totalProcess, A.realname, A.permission FROM CreditHistory CH INNER JOIN Accounts A ON CH.accountID = A.id WHERE CH.type IN (?, ?) AND CH.paymentStatus = ? AND CH.creationDate LIKE ? GROUP BY CH.accountID HAVING totalProcess > 0 ORDER BY totalPrice DESC LIMIT 5");
                  $topCreditHistory->execute(array(1, 2, 1, '%'.date("Y-m").'%'));
                  ?>
                  <?php if ($topCreditHistory->rowCount() > 0): ?>
                    <div class="card mb-3">
                      <div class="card-header">
                        <div class="row">
                          <div class="col">
                            <span><?php e__('Top Donators') ?></span>
                          </div>
                          <div class="col-auto">
                            <span>(<?php e__('This Month') ?>)</span>
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
                                <th class="text-center"><?php e__('Total') ?></th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php foreach ($topCreditHistory as $topCreditHistoryRead): ?>
                                <tr>
                                  <td class="text-center">
                                    <img class="rounded-circle" src="https://minotar.net/avatar/<?php echo $topCreditHistoryRead["realname"]; ?>/20.png" alt="<?php echo $serverName." Oyuncu - ".$topCreditHistoryRead["realname"]; ?>">
                                  </td>
                                  <td>
                                    <a href="/player/<?php echo $topCreditHistoryRead["realname"]; ?>">
                                      <?php echo $topCreditHistoryRead["realname"]; ?>
                                      <?php echo verifiedCircle($topCreditHistoryRead["permission"]); ?>
                                    </a>
                                  </td>
                                  <td class="text-center"><?php echo $topCreditHistoryRead["totalPrice"] ?></td>
                                </tr>
                              <?php endforeach; ?>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>
