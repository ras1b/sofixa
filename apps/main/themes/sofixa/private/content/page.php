<?php
  $page = $db->prepare("SELECT * FROM Pages WHERE id = ?");
  $page->execute(array(get("id")));
  $readPage = $page->fetch();
?>
<section class="section page-section">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><?php e__('Home') ?></a></li>
            <?php if (isset($_GET["id"])): ?>
              <li class="breadcrumb-item"><a href="/"><?php e__('Page') ?></a></li>
              <?php if ($page->rowCount() > 0): ?>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $readPage["title"]; ?></li>
              <?php else: ?>
                <li class="breadcrumb-item active" aria-current="page"><?php e__('Not Found!') ?></li>
              <?php endif; ?>
            <?php else: ?>
              <li class="breadcrumb-item active" aria-current="page"><?php e__('Page') ?></li>
            <?php endif; ?>
          </ol>
        </nav>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <?php if ($page->rowCount() > 0): ?>
          <div class="card">
            <div class="card-header">
              <?php echo $readPage["title"]; ?>
            </div>
            <div class="card-body">
              <?php echo showEmoji($readPage["content"]); ?>
            </div>
          </div>
        <?php else: ?>
          <?php echo alertError(t__('Page not found!')); ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
