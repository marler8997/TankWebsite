<?php 
include('master_login.php');
ob_start();
?>

<?php

$result = MysqlQuery("SELECT * FROM Macs WHERE Uid=$uid;");
$count = MysqlCount($result);
if($count <= 0) {
  echo '<h2>You have no offline keys</h2>';
} else {
  echo "<h2>You have $count offline key(s)</h2>";
}


?>

<?php
  $bodyContent = ob_get_contents();
  ob_end_clean();
  
  $pageTitle = 'OfflineKeys';

  include('master.php');
?>
