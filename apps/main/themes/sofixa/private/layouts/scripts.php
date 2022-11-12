<?php include __ROOT__.'/apps/main/themes/sofixa/private/layouts/translation_js.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/scrollup/2.4.1/jquery.scrollUp.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.6/clipboard.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/8.11.8/sweetalert2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-lazyload/16.1.0/lazyload.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/tr.min.js"></script>
<?php if ($readTheme["broadcastStatus"] == 1): ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery.Marquee/1.5.0/jquery.marquee.min.js"></script>
<?php endif; ?>
<?php
  if (isset($extraResourcesJS)) {
    $extraResourcesJS->getResources();
  }
?>
<script type="text/javascript">
  var $onlineAPI = <?php echo $readSettings["onlineAPI"]; ?>;
  var $preloaderStatus = '<?php echo (($readSettings["preloaderStatus"] == 1) ? 'true' : 'false'); ?>';
</script>
<script src="/apps/main/themes/sofixa/public/assets/js/main.min.js?v=<?php echo BUILD_NUMBER; ?>"></script>

<?php if ($readSettings["analyticsUA"] != '0'): ?>
  <!-- Global site tag (gtag.js) - Google Analytics -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $readSettings["analyticsUA"]; ?>"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag("js", new Date());

    gtag("config", "<?php echo $readSettings["analyticsUA"]; ?>");
  </script>
<?php endif; ?>

<?php if ($readSettings["tawktoID"] != '0'): ?>
  <!--Start of Tawk.to Script-->
  <script type="text/javascript">
    var Tawk_API = Tawk_API || {};
    var Tawk_LoadStart = new Date();
    (function(){
      var s1 = document.createElement("script");
      var s0 = document.getElementsByTagName("script")[0];
      s1.async = true;
      s1.src = 'https://embed.tawk.to/<?php echo $readSettings["tawktoID"]; ?>/default';
      s1.charset = 'UTF-8';
      s1.setAttribute('crossorigin','*');
      s0.parentNode.insertBefore(s1,s0);
    })();
  </script>
  <!--End of Tawk.to Script-->

  <!-- Disable ScrollUp button -->
  <style type="text/css">
    #scrollUp {
      display: none !important;
    }
  </style>
<?php endif; ?>
