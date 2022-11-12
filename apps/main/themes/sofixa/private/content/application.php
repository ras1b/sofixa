<?php
  if (!isset($_SESSION["login"])) {
    go("/login");
  }
?>
<section class="section support-section">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><?php e__('Home') ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php e__('Application') ?></li>
          </ol>
        </nav>
      </div>
      <div class="col-md-12">
        <?php if (get("action") == 'apply' && isset($_GET["form"])): ?>
          <?php
          $form = $db->prepare("SELECT * FROM ApplicationForms WHERE slug = ? AND isEnabled = ?");
          $form->execute(array(get("form"), 1));
          $readForm = $form->fetch();
          ?>
          <?php if ($form->rowCount() > 0): ?>
            <?php
            $activeApplication = $db->prepare("SELECT id, status FROM Applications WHERE accountId = ? AND formId = ? ORDER BY id DESC LIMIT 1");
            $activeApplication->execute(array($readAccount["id"], $readForm["id"]));
            $readActiveApplication = $activeApplication->fetch();
            ?>
            <?php if (($activeApplication->rowCount() == 0) || ($activeApplication->rowCount() > 0 && $readActiveApplication["status"] == "0" && $readForm["reappliable"] == "1")): ?>
              <?php
              require_once(__ROOT__."/apps/main/private/packages/class/csrf/csrf.php");
              $csrf = new CSRF('csrf-sessions', 'csrf-token');
              if (isset($_POST["apply"])) {
                if (!$csrf->validate('apply')) {
                  echo alertError(t__('Something went wrong! Please try again later.'));
                }
                else {
                  $checkQuestions = true;
                  $questionsForChecking = $db->prepare("SELECT * FROM ApplicationFormQuestions WHERE formId = ? AND isEnabled = ? ORDER BY id ASC");
                  $questionsForChecking->execute(array($readForm["id"], 1));
                  $questionsForChecking = $questionsForChecking->fetchAll();
                  foreach ($questionsForChecking as $readQuestionForChecking) {
                    if ($readQuestionForChecking["type"] == 4) {
                      $field = $_POST["field-".$readQuestionForChecking["id"]];
                      if (empty(array_filter($field))) {
                        $checkQuestions = false;
                        break;
                      }
                      else {
                        $answers = array_map('trim', explode(",", $readQuestionForChecking["variables"]));
                        if (!array_intersect($field, $answers)) {
                          $checkQuestions = false;
                          break;
                        }
                      }
                    }
                    else {
                      $field = post("field-".$readQuestionForChecking["id"]);
                      if ($field == "" || $field == null) {
                        $checkQuestions = false;
                        break;
                      }
                      else {
                        if ($readQuestionForChecking["type"] == 3) {
                          $answers = explode(",", $readQuestionForChecking["variables"]);
                          if (!in_array($field, $answers)) {
                            $checkQuestions = false;
                            break;
                          }
                        }
                      }
                    }
                  }
                  if (!$checkQuestions) {
                    echo alertError(t__('Please fill all the fields!'));
                  } else {
                    $insertApplication = $db->prepare("INSERT INTO Applications (accountID, formID, reason, creationDate) VALUES (?, ?, ?, ?)");
                    $insertApplication->execute(array($readAccount["id"], $readForm["id"], "", date("Y-m-d H:i:s")));
                    $applicationID = $db->lastInsertId();
  
                    foreach ($questionsForChecking as $readQuestionForChecking) {
                      if ($readQuestionForChecking["type"] == 4) {
                        foreach ($_POST["field-".$readQuestionForChecking["id"]] as $key => $value) {
                          $field = htmlspecialchars(trim(strip_tags($_POST["field-".$readQuestionForChecking["id"]][$key])));
                          $insertAnswer = $db->prepare("INSERT INTO ApplicationAnswers (applicationId, questionId, answer) VALUES (?, ?, ?)");
                          $insertAnswer->execute(array($applicationID, $readQuestionForChecking["id"], $field));
                        }
                      }
                      else {
                        $field = post("field-".$readQuestionForChecking["id"]);
                        $insertAnswer = $db->prepare("INSERT INTO ApplicationAnswers (applicationId, questionId, answer) VALUES (?, ?, ?)");
                        $insertAnswer->execute(array($applicationID, $readQuestionForChecking["id"], $field));
                      }
                    }
  
                    $websiteURL = ((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === 'on' ? "https" : "http")."://".$_SERVER["SERVER_NAME"]);
                    if ($readSettings["webhookApplicationURL"] != '0') {
                      require_once(__ROOT__."/apps/main/private/packages/class/webhook/webhook.php");
                      $search = array("%username%", "%form%", "%panelurl%");
                      $replace = array($readAccount["realname"], $readForm["title"], "$websiteURL/dashboard/applications/view/$applicationID");
                      $webhookMessage = $readSettings["webhookApplicationMessage"];
                      $webhookEmbed = $readSettings["webhookApplicationEmbed"];
                      $postFields = (array(
                        'content'     => ($webhookMessage != '0') ? str_replace($search, $replace, $webhookMessage) : null,
                        'avatar_url'  => 'https://minotar.net/avatar/'.$readAccount["realname"].'/256.png',
                        'tts'         => false,
                        'embeds'      => array(
                          array(
                            'type'        => 'rich',
                            'title'       => $readSettings["webhookApplicationTitle"],
                            'color'       => hexdec($readSettings["webhookApplicationColor"]),
                            'description' => str_replace($search, $replace, $webhookEmbed),
                            'image'       => array(
                              'url' => ($readSettings["webhookApplicationImage"] != '0') ? $readSettings["webhookApplicationImage"] : null
                            ),
                            'footer'      =>
                              ($readSettings["webhookApplicationAdStatus"] == 1) ? array(
                                'text'      => 'Powered by LeaderOS',
                                'icon_url'  => 'https://i.ibb.co/b1XB16h/ledaeros-png-64.png'
                              ) : array()
                          )
                        )
                      ));
                      $curl = new \LeaderOS\Http\Webhook($readSettings["webhookApplicationURL"]);
                      $curl(json_encode($postFields, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                    }
  
                    echo alertSuccess(t__('Your application has been sent successfully.'));
                    echo goDelay("/applications/$applicationID", 2);
                  }
                }
              }
              ?>
              <div class="card">
                <div class="card-header">
                  <h4><?php echo $readForm["title"]; ?></h4>
                  <div>
                    <?php echo $readForm["description"]; ?>
                  </div>
                </div>
                <div class="card-body">
                  <form action="" method="post">
                    <?php
                      $questions = $db->prepare("SELECT * FROM ApplicationFormQuestions WHERE formId = ? AND isEnabled = ? ORDER BY id ASC");
                      $questions->execute(array($readForm["id"], 1));
                    ?>
                    <?php foreach ($questions as $readQuestion): ?>
                      <div class="form-group">
                        <label for="input-<?php echo $readQuestion["id"]; ?>">
                          <?php echo $readQuestion["question"]; ?>
                        </label>
                        <?php if ($readQuestion["type"] == 1): ?>
                          <input type="text" id="input-<?php echo $readQuestion["id"]; ?>" class="form-control" name="field-<?php echo $readQuestion["id"]; ?>" required>
                        <?php endif; ?>
                        <?php if ($readQuestion["type"] == 2): ?>
                          <textarea id="input-<?php echo $readQuestion["id"]; ?>" class="form-control" name="field-<?php echo $readQuestion["id"]; ?>" rows="3" required></textarea>
                        <?php endif; ?>
                        <?php if ($readQuestion["type"] == 3 || $readQuestion["type"] == 4): ?>
                          <select id="input-<?php echo $readQuestion["id"]; ?>" class="form-control" name="field-<?php echo $readQuestion["id"].($readQuestion["type"] == 4 ? "[]" : null); ?>" data-toggle="select2" required <?php echo ($readQuestion["type"] == 4) ? 'multiple="multiple"' : null ?>>
                            <?php $variables = explode(",", $readQuestion["variables"]); ?>
                            <?php foreach ($variables as $variable): ?>
                              <?php $variable = trim($variable); ?>
                              <?php if ($variable != ''): ?>
                                <option value="<?php echo $variable; ?>"><?php echo $variable; ?></option>
                              <?php endif; ?>
                            <?php endforeach; ?>
                          </select>
                        <?php endif; ?>
                      </div>
                    <?php endforeach; ?>
                    <?php echo $csrf->input('apply'); ?>
                    <div class="clearfix">
                      <div class="float-right">
                        <button type="submit" class="btn btn-success btn-rounded" name="apply"><?php e__('Send') ?></button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            <?php else: ?>
              <?php echo alertError(t__("You cannot apply while you have an active application.")) ?>
            <?php endif; ?>
          <?php else: ?>
            <?php echo alertError(t__("Application form not found!")) ?>
          <?php endif; ?>
        <?php elseif (get("action") == 'get' && isset($_GET["id"])): ?>
          <?php
          $application = $db->prepare("SELECT AP.*, A.realname, AF.title FROM Applications AP INNER JOIN Accounts A ON A.id = AP.accountID INNER JOIN ApplicationForms AF ON AP.formID = AF.id WHERE AP.id = ? AND AP.accountID = ?");
          $application->execute(array(get("id"), $readAccount["id"]));
          $readApplication = $application->fetch();
          ?>
          <?php if ($application->rowCount() > 0): ?>
            <div class="card">
              <div class="card-header">
                <div class="row">
                  <div class="col">
                    <?php echo limitedContent($readApplication["title"], 50); ?>
                  </div>
                  <div class="col-auto">
                    <?php if ($readApplication["status"] == 0): ?>
                      <span class="badge badge-pill badge-danger" data-toggle="tooltip" data-placement="top" data-original-title="<?php e__('Status') ?>"><?php e__('Rejected') ?></span>
                    <?php elseif ($readApplication["status"] == 1): ?>
                      <span class="badge badge-pill badge-success" data-toggle="tooltip" data-placement="top" data-original-title="<?php e__('Status') ?>""><?php e__('Approved') ?></span>
                    <?php elseif ($readApplication["status"] == 2): ?>
                      <span class="badge badge-pill badge-warning" data-toggle="tooltip" data-placement="top" data-original-title="<?php e__('Status') ?>""><?php e__('Pending Approval') ?></span>
                    <?php else: ?>
                      <span class="badge badge-pill badge-danger" data-toggle="tooltip" data-placement="top" data-original-title="<?php e__('Status') ?>""><?php e__('Error!') ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <div class="card-body pb-0">
                <?php if ($readApplication["reason"] != ''): ?>
                  <div class="message">
                    <div class="message-content">
                      <div class="message-header">
                        <div class="message-username" style="font-weight: 500">
                          <?php e__('Reason'); ?>:
                        </div>
                      </div>
                      <div class="message-body">
                        <p><?php echo $readApplication["reason"]; ?></p>
                      </div>
                    </div>
                  </div>
                <?php endif; ?>
                <?php
                  $answers = $db->prepare("SELECT GROUP_CONCAT(AA.answer) as answer, AFQ.question FROM ApplicationAnswers AA INNER JOIN ApplicationFormQuestions AFQ ON AFQ.id = AA.questionID WHERE AA.applicationID = ? GROUP BY AFQ.id");
                  $answers->execute(array($readApplication["id"]));
                ?>
                <?php if ($answers->rowCount() > 0): ?>
                  <?php foreach ($answers as $readAnswer): ?>
                    <div class="message">
                      <div class="message-content">
                        <div class="message-header">
                          <div class="message-username" style="font-weight: 500">
                            <?php echo $readAnswer["question"]; ?>
                          </div>
                        </div>
                        <div class="message-body">
                          <p>
                            <?php echo $readAnswer["answer"]; ?>
                          </p>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
          <?php else: ?>
            <?php echo alertError(t__('Application not found!')); ?>
          <?php endif; ?>
        <?php else: ?>
          <?php go('/404'); ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>