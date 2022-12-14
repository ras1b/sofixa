<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="author" content="Fırat KAYA">
<link rel="shortcut icon" type="image/x-icon" href="/apps/main/public/assets/img/extras/favicon.png">

<title><?php echo $siteTitle; ?></title>

<?php $siteURL = ((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === 'on' ? "https" : "http")."://".$_SERVER["SERVER_NAME"]); ?>
<meta name="description" content="<?php echo $readSettings["siteDescription"]; ?>" />
<meta name="keywords" content="<?php echo $readSettings["siteTags"]; ?>">
<link rel="canonical" href="<?php echo $siteURL; ?>" />
<meta property="og:locale" content="tr_TR" />
<meta property="og:type" content="website" />
<meta property="og:title" content="<?php echo $siteTitle; ?>" />
<meta property="og:description" content="<?php echo $readSettings["siteDescription"]; ?>" />
<meta property="og:url" content="<?php echo $siteURL; ?>" />
<meta property="og:site_name" content="<?php echo $readSettings["serverName"]; ?>" />

<!-- MAIN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

<!-- EXTRAS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.2/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/8.11.8/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.4.0/dist/select2-bootstrap4.min.css">

<!-- FONTS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<!-- THEMES -->
<link rel="stylesheet" type="text/css" href="/apps/main/themes/sofixa/public/assets/css/main.min.css?v=<?php echo BUILD_NUMBER; ?>">
<link rel="stylesheet" type="text/css" href="/apps/main/themes/sofixa/public/assets/css/responsive.min.css?v=<?php echo BUILD_NUMBER; ?>">

<?php if (get("route") == 'lottery'): ?>
	<link rel="stylesheet" type="text/css" href="/apps/main/themes/sofixa/public/assets/css/plugins/superwheel/superwheel.min.css">
	<style type="text/css">
		.superWheel .sWheel-inner {
			background-image: url(/apps/main/public/assets/img/extras/lottery-bg.png);
			background-repeat: no-repeat;
			background-position: center;
			background-size: 120px;
		}
	</style>
<?php endif; ?>

<!-- SFX THEME COLOR -->

<style>
	:root {
		--banner-logo-size: 150px;
		--body-color: 236,241,246;
		--second-body-bg: 235,235,235;
		--header-banner-background: 52,173,241;
		--banner-background-text: 255,255,255;
		--navbar-bg-color: 255,255,255;
		--navbar-text-color: 29,29,29;
		--breadcrumb-text-color: 255,255,255;
		--secondary-color: 0,69,118;
		--secondary-text-color: 255,255,255;
		--card-text-color-1: 0,0,0;
		--card-text-color-2: 114,114,114;
		--footer-top-background: 52,173,241 ;
		--footer-bottom-background: 52,173,241 ;
		--footer-text-color-1: 243,243,243;
		--footer-text-color-2: 243,243,243;
	}
</style>

<div class="discord-absolute">
	<a class="sfx-discord-fixed" href="https://discord.gg/BfwzmGUsmy">
		<svg width="50" height="55" viewBox="0 0 71 55" fill="rgba(var(--secondary-color));" xmlns="http://www.w3.org/2000/svg">
			<g clip-path="url(#clip0)">
				<path d="M60.1045 4.8978C55.5792 2.8214 50.7265 1.2916 45.6527 0.41542C45.5603 0.39851 45.468 0.440769 45.4204 0.525289C44.7963 1.6353 44.105 3.0834 43.6209 4.2216C38.1637 3.4046 32.7345 3.4046 27.3892 4.2216C26.905 3.0581 26.1886 1.6353 25.5617 0.525289C25.5141 0.443589 25.4218 0.40133 25.3294 0.41542C20.2584 1.2888 15.4057 2.8186 10.8776 4.8978C10.8384 4.9147 10.8048 4.9429 10.7825 4.9795C1.57795 18.7309 -0.943561 32.1443 0.293408 45.3914C0.299005 45.4562 0.335386 45.5182 0.385761 45.5576C6.45866 50.0174 12.3413 52.7249 18.1147 54.5195C18.2071 54.5477 18.305 54.5139 18.3638 54.4378C19.7295 52.5728 20.9469 50.6063 21.9907 48.5383C22.0523 48.4172 21.9935 48.2735 21.8676 48.2256C19.9366 47.4931 18.0979 46.6 16.3292 45.5858C16.1893 45.5041 16.1781 45.304 16.3068 45.2082C16.679 44.9293 17.0513 44.6391 17.4067 44.3461C17.471 44.2926 17.5606 44.2813 17.6362 44.3151C29.2558 49.6202 41.8354 49.6202 53.3179 44.3151C53.3935 44.2785 53.4831 44.2898 53.5502 44.3433C53.9057 44.6363 54.2779 44.9293 54.6529 45.2082C54.7816 45.304 54.7732 45.5041 54.6333 45.5858C52.8646 46.6197 51.0259 47.4931 49.0921 48.2228C48.9662 48.2707 48.9102 48.4172 48.9718 48.5383C50.038 50.6034 51.2554 52.5699 52.5959 54.435C52.6519 54.5139 52.7526 54.5477 52.845 54.5195C58.6464 52.7249 64.529 50.0174 70.6019 45.5576C70.6551 45.5182 70.6887 45.459 70.6943 45.3942C72.1747 30.0791 68.2147 16.7757 60.1968 4.9823C60.1772 4.9429 60.1437 4.9147 60.1045 4.8978ZM23.7259 37.3253C20.2276 37.3253 17.3451 34.1136 17.3451 30.1693C17.3451 26.225 20.1717 23.0133 23.7259 23.0133C27.308 23.0133 30.1626 26.2532 30.1066 30.1693C30.1066 34.1136 27.28 37.3253 23.7259 37.3253ZM47.3178 37.3253C43.8196 37.3253 40.9371 34.1136 40.9371 30.1693C40.9371 26.225 43.7636 23.0133 47.3178 23.0133C50.9 23.0133 53.7545 26.2532 53.6986 30.1693C53.6986 34.1136 50.9 37.3253 47.3178 37.3253Z" fill="rgba(var(--secondary-color))"/>
			</g>
			<defs>
				<clipPath id="clip0">
					<rect width="71" height="55" fill="white"/>
				</clipPath>
			</defs>
		</svg>
	</a>
</div>

<style type="text/css">
  .credit-icon::before {
    content: ' ';
    display: inline-block;
    width: 1rem;
    height: 1rem;
    background-image: url(/apps/main/public/assets/img/extras/credit.png);
    background-repeat: no-repeat;
    background-position: center;
    background-size: 1rem;
  }
</style>

<!-- COLORS -->
<?php if ($readTheme["themeID"] != 0): ?>
	<style type="text/css">
		<?php $readColors = json_decode($readTheme["colors"], true); ?>
		<?php foreach ($readColors as $selector => $styles): ?>
			<?php echo $selector; ?> {
				<?php foreach ($styles as $key => $value): ?>
					<?php echo $key.':'.$value.';'; ?>
				<?php endforeach; ?>
			}
		<?php endforeach; ?>
	</style>
<?php endif; ?>

<!-- CUSTOM CSS -->
<style type="text/css">
	<?php echo $readTheme["customCSS"]; ?>
</style>
