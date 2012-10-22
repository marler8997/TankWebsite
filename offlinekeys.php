<?php 
include('master_login.php');
ob_start();
?>

Your offline keys

<?php
  $bodyContent = ob_get_contents();
  ob_end_clean();
  
  $pageTitle = 'OfflineKeys';

  include('master.php');
?>
