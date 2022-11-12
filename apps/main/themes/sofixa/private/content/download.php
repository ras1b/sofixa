<?php
$downloads = $db->query("SELECT * FROM Downloads");
if (get("file") && get("id")) {
  $file = $db->prepare("SELECT * FROM Downloads WHERE id = ?");
  $file->execute(array(get("id")));
  $readFile = $file->fetch();
}
else {
  $firstFile = $db->query("SELECT * FROM Downloads ORDER BY id ASC LIMIT 1");
  $readFirstFile = $firstFile->fetch();
  if ($firstFile->rowCount() > 0) {
    go("/download/".$readFirstFile["id"]."/".$readFirstFile["slug"]);
  }
}
?>
<section class="section download-section">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><?php e__('Home') ?></a></li>
            <?php if (isset($_GET["file"])): ?>
              <li class="breadcrumb-item"><a href="/download"><?php e__('Download') ?></a></li>
              <?php if ($file->rowCount() > 0): ?>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $readFile["name"]; ?></li>
              <?php else: ?>
                <li class="breadcrumb-item active" aria-current="page"><?php e__('Not Found!') ?></li>
              <?php endif; ?>
            <?php else: ?>
              <li class="breadcrumb-item active" aria-current="page"><?php e__('Download') ?></li>
            <?php endif; ?>
          </ol>
        </nav>
      </div>
      <?php if ($downloads->rowCount() > 0): ?>
        <div class="col-md-8">
          <div class="card position-relative overflow-hidden">
            <div class="card-header">
              <?php echo $readFile["name"]; ?>
            </div>
            <div class="w-100 text-center">
              <img class="w-25 download-absolute-img" src="/apps/main/public/assets/img/extras/download-icon.png" alt="">
            </div>
            <div class="card-body text-center">
              <?php echo $readFile["content"]; ?>
              <div class="mt-3">
                <a class="btn btn-rounded btn-banner-bg" href="<?php echo $readFile["downloadURL"]; ?>" rel="external">
                  <i class="fa fa-download mr-1"></i> <?php e__('Download') ?>
                </a>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <?php if ($downloads->rowCount() > 0): ?>
            <div class="card">
              <div class="card-header">
                <?php e__('Files') ?>
              </div>
              <ul class="list-group list-group-flush">
                <?php foreach ($downloads as $readDownloads): ?>
                  <li class="list-group-item <?php echo ($readDownloads["id"] == get("id")) ? "active" : null; ?>">
                    <a href="/download/<?php echo $readDownloads["id"]; ?>/<?php echo $readDownloads["slug"]; ?>">
                      <i class="fa fa-download"></i>
                      <?php echo $readDownloads["name"]; ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="col-md-12"><?php echo alertError(t__('File not found!')); ?></div>
      <?php endif; ?>
    </div>
  </div>
</section>
