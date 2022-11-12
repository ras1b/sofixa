<footer class="footer">
  <svg fill="rgba(var(--header-banner-background))" class="wave-footer " preserveAspectRatio="none" viewBox="0 0 1440 83" xmlns="http://www.w3.org/2000/svg">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M47.4694 20.8187C122.804 42.296 222.186 70.6289 409.5 49C726.618 12.3829 789 9.50001 1086 43.5C1271.47 64.732 1385.22 31.8972 1440 5.44763V83H0V8.11478C15.1007 11.5906 30.6885 16.0345 47.4694 20.8187Z"></path>
  </svg>
  <div class="footer-top">
    <div class="container position-relative z-index-999">
      <div class="row justify-content-center">
        <div class="col-md-12">
          <div class="w-100 text-center mb-5">
            <img class="w-10" src="/apps/main/public/assets/img/extras/header-logo.png" alt="">
            <br>
            <div class="w-100 text-center d-flex justify-content-center sfx-social-icon">
              <?php if ($readSettings["footerInstagram"] != '0'): ?>
                <div class="footer-social-icon mr-4">
                  <a href="<?php echo $readSettings["footerInstagram"]; ?>">
                    <i class="bi bi-instagram"></i>
                  </a>
                </div>
              <?php endif; ?>
              <?php if ($readSettings["footerFacebook"] != '0'): ?>
                <div class="footer-social-icon mr-4">
                  <a href="<?php echo $readSettings["footerFacebook"]; ?>">
                    <i class="bi bi-facebook"></i>
                  </a>
                </div>
              <?php endif; ?>
              <?php if ($readSettings["footerTwitter"] != '0'): ?>
                <div class="footer-social-icon mr-4">
                  <a href="<?php echo $readSettings["footerTwitter"]; ?>">
                    <i class="bi bi-twitter"></i>
                  </a>
                </div>
              <?php endif; ?>
              <?php if ($readSettings["footerYoutube"] != '0'): ?>
                <div class="footer-social-icon mr-4">
                  <a href="<?php echo $readSettings["footerYoutube"]; ?>">
                    <i class="bi bi-youtube"></i>
                  </a>
                </div>
              <?php endif; ?>
              <?php if ($readSettings["footerDiscord"] != '0'): ?>
                <div class="footer-social-icon mr-4">
                  <a href="<?php echo $readSettings["footerDiscord"]; ?>">
                    <i class="bi bi-discord"></i>
                  </a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-5 mb-md-0">
          <h5 class="footer-title"><?php e__('About') ?></h5>
          <p class="mb-0 about-text-content">
            <?php if ($readSettings["footerAboutText"] == '0'): ?>
              <?php e__('You can edit this text from the Dashboard.') ?>
            <?php else: ?>
              <?php echo $readSettings["footerAboutText"]; ?>
            <?php endif; ?>
          </p>
          <?php
          $availableLanguages = $db->query("SELECT * FROM Languages");
          $readAvailableLanguages = $availableLanguages->fetchAll();
          ?>
          <?php if ($availableLanguages->rowCount() > 0): ?>
           <div class="mt-4">
             <div class="dropdown dropup">
              <button class="btn secondary-bg-color dropdown-toggle" type="button" id="languageMenu" data-toggle="dropdown" aria-expanded="false">
                 <?php echo $readAvailableLanguages[array_search($lang, array_column($readAvailableLanguages, 'code'))]["name"]; ?>
               </button>
               <ul class="dropdown-menu" aria-labelledby="languageMenu">
                 <?php foreach ($readAvailableLanguages as $readAvailableLanguage): ?>
                   <a class="dropdown-item <?php echo ($readAvailableLanguage["code"] == $lang) ? "active" : null; ?>" href="?lang=<?php echo $readAvailableLanguage["code"]; ?>">
                     <?php echo $readAvailableLanguage["name"]; ?>
                   </a>
                 <?php endforeach; ?>
               </ul>
             </div>
           </div>
         <?php endif; ?>
       </div>
       <div class="col-6 col-md-2">
        <h5 class="footer-title"><?php e__('Quick Menu') ?></h5>
        <ul class="list-unstyled mb-0">
          <li class="mb-2">
            <a href="/"><?php e__('Home') ?></a>
          </li>
          <li class="mb-2">
            <a href="/store"><?php e__('Store') ?></a>
          </li>
          <li class="mb-2">
            <a href="/credit/buy"><?php e__('Buy Credits') ?></a>
          </li>
          <?php if (isset($_SESSION["login"])): ?>
            <li class="mb-2">
              <a href="/profile"><?php e__('Profile') ?></a>
            </li>
          <?php else: ?>
            <li class="mb-2">
              <a href="/login"><?php e__('Login') ?></a>
            </li>
            <li class="mb-2">
              <a href="/register"><?php e__('Register') ?></a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="col-6 col-md-2">
        <h5 class="footer-title"><?php e__('Social Media') ?></h5>
        <ul class="list-unstyled mb-0">
          <?php if (($readSettings["footerFacebook"] != '0') || ($readSettings["footerTwitter"] != '0') || ($readSettings["footerInstagram"] != '0') || ($readSettings["footerYoutube"] != '0') || ($readSettings["footerDiscord"] != '0')): ?>
          <?php if ($readSettings["footerFacebook"] != '0'): ?>
            <li class="mb-2">
              <a href="<?php echo $readSettings["footerFacebook"]; ?>" rel="external">
                <i class="fa fa-facebook-square text-white mr-1"></i> Facebook
              </a>
            </li>
          <?php endif; ?>
          <?php if ($readSettings["footerTwitter"] != '0'): ?>
            <li class="mb-2">
              <a href="<?php echo $readSettings["footerTwitter"]; ?>" rel="external">
                <i class="fa fa-twitter text-white mr-1"></i> Twitter
              </a>
            </li>
          <?php endif; ?>
          <?php if ($readSettings["footerInstagram"] != '0'): ?>
            <li class="mb-2">
              <a href="<?php echo $readSettings["footerInstagram"]; ?>" rel="external">
                <i class="fa fa-instagram text-white mr-1"></i> Instagram
              </a>
            </li>
          <?php endif; ?>
          <?php if ($readSettings["footerYoutube"] != '0'): ?>
            <li class="mb-2">
              <a href="<?php echo $readSettings["footerYoutube"]; ?>" rel="external">
                <i class="fa fa-youtube-play text-white mr-1"></i> Youtube
              </a>
            </li>
          <?php endif; ?>
          <?php if ($readSettings["footerDiscord"] != '0'): ?>
            <li class="mb-2">
              <a href="<?php echo $readSettings["footerDiscord"]; ?>" rel="external">
                <i class="fa fa-gamepad text-white mr-1"></i> Discord
              </a>
            </li>
          <?php endif; ?>
        <?php else: ?>
          <li><?php e__('You can edit social media details from the Dashboard.') ?></li>
        <?php endif; ?>
      </ul>
    </div>
    <div class="col-md-3 mt-5 mt-md-0">
      <h5 class="footer-title"><?php e__('Contact') ?></h5>
      <ul class="list-unstyled mb-0">
        <?php if (($readSettings["footerEmail"] != '0') || ($readSettings["footerPhone"] != '0') || ($readSettings["footerWhatsapp"] != '0')): ?>
        <?php if ($readSettings["footerEmail"] != '0'): ?>
          <li class="mb-2">
            <a href="mailto:<?php echo $readSettings["footerEmail"]; ?>" rel="external">
              <i class="fa fa-envelope text-white mr-1"></i> <?php echo $readSettings["footerEmail"]; ?>
            </a>
          </li>
        <?php endif; ?>
        <?php if ($readSettings["footerPhone"] != '0'): ?>
          <li class="mb-2">
            <a href="tel:<?php echo $readSettings["footerPhone"]; ?>" rel="external">
              <i class="fa fa-phone text-white mr-1"></i> <?php echo $readSettings["footerPhone"]; ?>
            </a>
          </li>
        <?php endif; ?>
        <?php if ($readSettings["footerWhatsapp"] != '0'): ?>
          <li class="mb-2">
            <a href="https://wa.me/<?php echo str_replace(array("+", " "), array('', ''), $readSettings["footerWhatsapp"]); ?>" rel="external">
              <i class="fa fa-whatsapp text-white mr-1"></i> <?php echo $readSettings["footerWhatsapp"]; ?>
            </a>
          </li>
        <?php endif; ?>
      <?php else: ?>
        <li><?php e__('You can edit contact details from the Dashboard.') ?></li>
      <?php endif; ?>
    </ul>
  </div>
</div>
</div>
<div class="w-100 text-center position-relative z-index-999">
  <a href="https://sofixa.com" target="blank"><img data-toggle="tooltip" data-placement="top" title="Theme: Sofixa" src="/apps/main/themes/sofixa/public/assets/img/extras/sofixa-icon.png" class="sofixa-footer-icon info-icon-footer"></a>
  <a href="https://leaderos.com.tr" target="blank">
    <img data-toggle="tooltip" data-placement="top" title="Powered by VEXUS" src="/apps/main/themes/sofixa/public/assets/img/extras/leaderos-icon.png" class="leaderos-footer-icon info-icon-footer"></a>
  </div>
  <div class="w-100 text-center position-relative z-index-999">
    <p class="footer-title"><?php e__('All rights reserved.') ?> &copy; <?php echo date("Y"); ?></p>
  </div>
</div>
</footer>
