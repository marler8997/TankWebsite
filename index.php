<?php 

if(isset($_REQUEST['Logout'])) setcookie('Sid','',1);

include('master_login.php');
ob_start();
?>

Tank the game!!

<?php
  $bodyContent = ob_get_contents();
  ob_end_clean();
  
  $pageTitle = 'Home';

  include('master.php');
?>
