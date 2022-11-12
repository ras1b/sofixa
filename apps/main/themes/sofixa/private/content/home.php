<?php
if (get("page")) {
	if (!is_numeric(get("page"))) {
		$_GET["page"] = 1;
	}
	$page = intval(get("page"));
}
else {
	$page = 1;
}
if (get("category")) {
	$itemsCount = $db->prepare("SELECT N.id from News N INNER JOIN NewsCategories NC ON N.categoryID = NC.id WHERE NC.slug = ?");
	$itemsCount->execute(array(get("category")));
	$itemsCount = $itemsCount->rowCount();
	$requestURL = '/categories/'.get("category");
}
else if (get("tag")) {
	$itemsCount = $db->prepare("SELECT N.id from News N INNER JOIN NewsTags NT ON N.id = NT.newsID WHERE NT.slug = ?");
	$itemsCount->execute(array(get("tag")));
	$itemsCount = $itemsCount->rowCount();
	$requestURL = '/tags/'.get("tag");
}
else {
	$itemsCount = $db->query("SELECT id from News");
	$itemsCount = $itemsCount->rowCount();
	$requestURL = '/blog';
}
$pageCount = ceil($itemsCount/$newsLimit);
if ($page > $pageCount) {
	$page = 1;
}
$visibleItemsCount = $page * $newsLimit - $newsLimit;
$visiblePageCount = 5;
?>
<style type="text/css">
	<?php if ($readTheme["sliderStyle"] == '2'): ?>
		.news-section {
			margin-top: 2rem !important;
		}
		.carousel-inner, .carousel-item img {
			<?php if ($readTheme["serverOnlineInfoStatus"] == '0'): ?>
				border-radius: 1rem;
			<?php else: ?>
				border-radius: 1rem 1rem 0 0;
			<?php endif; ?>
		}
		<?php if ($readTheme["serverOnlineInfoStatus"] == '1'): ?>
			.server-online-info {
				border-radius: 0 0 1rem 1rem;
			}
		<?php endif; ?>
	<?php endif; ?>
</style>
<?php if (!get("category") && !get("tag") && $page == 1): ?>
<?php if ($readTheme["sliderStatus"] == '1'): ?>
	<?php $slider = $db->query("SELECT * FROM Slider"); ?>
	<?php if ($slider->rowCount() > 0): ?>
		<div class="<?php echo ($readTheme["sliderStyle"] == '2') ? 'container mt-4 mt-md-5' : null; ?>">
			<div id="carouselSlider" class="carousel slide" data-ride="carousel">
				<ol class="carousel-indicators">
					<?php for ($i=0; $i < $slider->rowCount(); $i++): ?>
						<li <?php echo ($i == 0) ? 'class="active"' : null; ?> data-target="#carouselSlider" data-slide-to="<?php echo $i; ?>"></li>
					<?php endfor; ?>
				</ol>
				<div class="carousel-inner">
					<?php foreach ($slider as $i => $readSlider): ?>
						<div class="carousel-item <?php echo ($i == 0) ? "active" : null; ?>">
							<a href="<?php echo $readSlider["url"]; ?>">
								<img class="d-block w-100 lazyload" data-src="/apps/main/public/assets/img/slider/<?php echo $readSlider["imageID"].'.'.$readSlider["imageType"]; ?>" src="/apps/main/public/assets/img/loaders/slider.png" alt="<?php echo $serverName." Slider - Afiş"; ?>">
								<div class="carousel-caption d-md-block">
									<h1><?php echo $readSlider["title"]; ?></h1>
									<p><?php echo $readSlider["content"]; ?></p>
								</div>
							</a>
						</div>
					<?php endforeach; ?>
				</div>
				<a class="carousel-control-prev" href="#carouselSlider" role="button" data-slide="prev">
					<span class="fa fa-angle-left" aria-hidden="true"></span>
					<span class="sr-only"><?php e__('Prev') ?></span>
				</a>
				<a class="carousel-control-next" href="#carouselSlider" role="button" data-slide="next">
					<span class="fa fa-angle-right" aria-hidden="true"></span>
					<span class="sr-only"><?php e__('Next') ?></span>
				</a>
			</div>
			<?php if ($readTheme["serverOnlineInfoStatus"] == '1'): ?>
				<div class="server-online-info" data-toggle="onlinebox"><strong data-toggle="onlinetext" server-ip="<?php echo $serverIP; ?>">-/-</strong> <?php e__('players online') ?></div>
			<?php endif; ?>
		</div>
	<?php else: ?>
		<section class="section">
			<div class="container">
				<div class="row">
					<div class="col-12">
						<?php echo alertError(t__('Slider not found!')); ?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
<section class="section news-section">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb">
						<li class="breadcrumb-item text-color"><a href="/"><?php e__('Home') ?></a></li>
						<?php if (get("category")): ?>
							<?php
							$newsCategory = $db->prepare("SELECT name FROM NewsCategories WHERE slug = ?");
							$newsCategory->execute(array(get("category")));
							$readNewsCategory = $newsCategory->fetch();
							?>
							<li class="breadcrumb-item"><a href="/"><?php e__('Blog') ?></a></li>
							<li class="breadcrumb-item"><a href="/"><?php e__('Category') ?></a></li>
							<li class="breadcrumb-item active" aria-current="page"><?php echo (($newsCategory->rowCount() > 0) ? $readNewsCategory["name"] : t__('Not Found!')); ?></li>
						<?php elseif (get("tag")): ?>
							<?php
							$newsTag = $db->prepare("SELECT name FROM NewsTags WHERE slug = ?");
							$newsTag->execute(array(get("tag")));
							$readNewsTag = $newsTag->fetch();
							?>
							<li class="breadcrumb-item"><a href="/"><?php e__('Blog') ?></a></li>
							<li class="breadcrumb-item"><a href="/"><?php e__('Tag') ?></a></li>
							<li class="breadcrumb-item active" aria-current="page"><?php echo (($newsTag->rowCount() > 0) ? $readNewsTag["name"] : t__('Not Found!')); ?></li>
						<?php else: ?>
							<li class="breadcrumb-item active" aria-current="page"><?php e__('Blog') ?></li>
						<?php endif; ?>
					</ol>
				</nav>
			</div>
		</div>
		<div class="row">
			<div class="<?php echo ($readTheme["sidebarStatus"] == 0) ? 'col-md-12' : 'col-md-8'; ?>">
				<div class="row">
					<?php
					if (get("category")) {
						$news = $db->prepare("SELECT N.*, NC.name as categoryName, NC.slug as categorySlug from News N INNER JOIN NewsCategories NC ON N.categoryID = NC.id INNER JOIN Accounts A ON N.accountID = A.id WHERE NC.slug = ? ORDER BY N.id DESC LIMIT $visibleItemsCount, $newsLimit");
						$news->execute(array(get("category")));
					}
					else if (get("tag")) {
						$news = $db->prepare("SELECT N.*, NC.name as categoryName, NC.slug as categorySlug from News N INNER JOIN NewsCategories NC ON N.categoryID = NC.id INNER JOIN NewsTags NT ON N.id = NT.newsID INNER JOIN Accounts A ON N.accountID = A.id WHERE NT.slug = ? ORDER BY N.id DESC LIMIT $visibleItemsCount, $newsLimit");
						$news->execute(array(get("tag")));
					}
					else {
						$news = $db->query("SELECT N.*, NC.name as categoryName, NC.slug as categorySlug from News N INNER JOIN NewsCategories NC ON N.categoryID = NC.id INNER JOIN Accounts A ON N.accountID = A.id ORDER BY N.id DESC LIMIT $visibleItemsCount, $newsLimit");
						$news->execute();
					}
					?>
					<?php if ($news->rowCount() > 0): ?>
						<?php foreach ($news as $readNews): ?>
							<?php
							$newsComments = $db->prepare("SELECT * FROM NewsComments WHERE newsID = ? AND status = ? ORDER BY id DESC");
							$newsComments->execute(array($readNews["id"], 1));
							?>
							<?php
							$newsCardCol = 'col-md-4';
							$newsLetterLimit = 240;
							if ($readTheme["sidebarStatus"] == 0 && $readTheme["newsCardStyle"] == 1) {
								$newsCardCol = 'col-md-4';
								$newsLetterLimit = 240;
							}
							if ($readTheme["sidebarStatus"] == 0 && $readTheme["newsCardStyle"] == 2
								|| $readTheme["sidebarStatus"] == 1 && $readTheme["newsCardStyle"] == 1) {
								$newsCardCol = 'col-md-6';
							$newsLetterLimit = 420;
						}
						if ($readTheme["sidebarStatus"] == 1 && $readTheme["newsCardStyle"] == 2) {
							$newsCardCol = 'col-md-12';
							$newsLetterLimit = 600;
						}
						?>
						<div class="<?php echo $newsCardCol; ?> d-flex">
							<div class="card news-card">
								<div class="card-body">
									<div class="w-100 text-center">
										<a class="" href="/posts/<?php echo $readNews["id"]; ?>/<?php echo $readNews["slug"]; ?>">
											<img class="news-img lazyload" data-src="/apps/main/public/assets/img/news/<?php echo $readNews["imageID"].'.'.$readNews["imageType"]; ?>" src="/apps/main/public/assets/img/loaders/news.png" alt="<?php echo $serverName." Haber - ".$readNews["title"]; ?>">
										</a>
									</div>
									<div class="w-100 text-center my-3">
										<a class="news-head-title" href="">
											<?php echo $readNews["title"]; ?>
										</a>
									</div>
									<div class="w-100 text-center">
										<a class="news-head-content" href="">
											<?php echo showEmoji(limitedContent(strip_tags($readNews["content"]), $newsLetterLimit)); ?>
										</a>
									</div>
									<div class="w-100 text-right news-read-remaining">
										<a class="btn btn-banner-bg mt-2" href="/posts/<?php echo $readNews["id"]; ?>/<?php echo $readNews["slug"]; ?>"><?php e__('Read More') ?></a>
									</div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
					<div class="col-md-12 d-flex justify-content-center <?php echo ($readTheme["sidebarStatus"] == 1) ? 'mb-4' : null; ?>">
						<nav class="pages" aria-label="Sayfalar">
							<ul class="pagination">
								<li class="page-item <?php echo ($page == 1) ? "disabled" : null; ?>">
									<a class="page-link" href="<?php echo $requestURL.'/'.($page-1); ?>" tabindex="-1">
										<i class="fa fa-angle-double-left"></i>
									</a>
								</li>
								<?php for ($i = $page - $visiblePageCount; $i < $page + $visiblePageCount + 1; $i++): ?>
									<?php if ($i > 0 and $i <= $pageCount): ?>
										<li class="page-item <?php echo (($page == $i) ? "active" : null); ?>">
											<a class="page-link header-banner-background" href="<?php echo $requestURL.'/'.$i; ?>"><?php echo $i; ?></a>
										</li>
									<?php endif; ?>
								<?php endfor; ?>
								<li class="page-item <?php echo ($page == $pageCount) ? "disabled" : null; ?>">
									<a class="page-link" href="<?php echo $requestURL.'/'.($page+1); ?>">
										<i class="fa fa-angle-double-right"></i>
									</a>
								</li>
							</ul>
						</nav>
					</div>
				<?php else : ?>
					<div class="col-md-12">
						<?php echo alertError(t__('No posts were found!')); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php if ($readTheme["sidebarStatus"] == 1): ?>
			<div class="col-md-4">
				<?php $storeHistory = $db->query("SELECT P.name as productName, S.name as serverName, A.realname, A.permission FROM StoreHistory SH INNER JOIN Products P ON SH.productID = P.id INNER JOIN Servers S ON SH.serverID = S.id INNER JOIN Accounts A ON SH.accountID = A.id ORDER BY SH.id DESC LIMIT 5"); ?>
				<?php if ($storeHistory->rowCount() > 0): ?>
					<div class="card mb-3">
						<div class="card-header">
							<span><?php e__('Recent Purchases') ?><?php echo $lang; ?></span>
						</div>
						<div class="card-body p-0">
							<div class="table-responsive">
								<table class="table table-hover">
									<thead>
										<tr>
											<th class="text-center">#</th>
											<th><?php e__('Username') ?></th>
											<th class="text-center"><?php e__('Server') ?></th>
											<th class="text-center"><?php e__('Product') ?></th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($storeHistory as $readStoreHistory): ?>
											<tr>
												<td class="text-center">
													<?php echo minecraftHead($readSettings["avatarAPI"], $readStoreHistory["realname"], 20); ?>
												</td>
												<td>
													<?php echo $readStoreHistory["realname"]; ?>
													<?php echo verifiedCircle($readStoreHistory["permission"]); ?>
												</td>
												<td class="text-center"><?php echo $readStoreHistory["serverName"]; ?></td>
												<td class="text-center"><?php echo $readStoreHistory["productName"]; ?></td>
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

				<?php
				$creditHistory = $db->prepare("SELECT CH.type, CH.price, A.realname, A.permission FROM CreditHistory CH INNER JOIN Accounts A ON CH.accountID = A.id WHERE CH.type IN (?, ?) AND CH.paymentStatus = ? ORDER BY CH.id DESC LIMIT 5");
				$creditHistory->execute(array(1, 2, 1));
				?>
				<?php if ($creditHistory->rowCount() > 0): ?>
					<div class="card mb-3">
						<div class="card-header">
							<span><?php e__('Recent Donations') ?></span>
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
													<?php echo minecraftHead($readSettings["avatarAPI"], $readCreditHistory["realname"], 20); ?>
												</td>
												<td>
													<?php echo $readCreditHistory["realname"]; ?>
													<?php echo verifiedCircle($readCreditHistory["permission"]); ?>
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
													<?php echo minecraftHead($readSettings["avatarAPI"], $topCreditHistoryRead["realname"], 20); ?>
												</td>
												<td>
													<?php echo $topCreditHistoryRead["realname"]; ?>
													<?php echo verifiedCircle($topCreditHistoryRead["permission"]); ?>
												</td>
												<td class="text-center"><?php echo $topCreditHistoryRead["totalPrice"]; ?></td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				<?php endif; ?>
				<?php if ($readTheme["discordServerID"] != '0'): ?>
					<iframe class="lazyload" data-src="https://discordapp.com/widget?id=<?php echo $readTheme["discordServerID"]; ?>&theme=<?php echo ($readTheme["discordThemeID"] == 1) ? "light" : (($readTheme["discordThemeID"] == 2) ? "dark" : "light"); ?>" width="100%" height="500" allowtransparency="true" frameborder="0"></iframe>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
</section>
