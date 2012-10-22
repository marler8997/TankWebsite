<?php 
include('master_login.php');
ob_start();
?>

<table style="margin:auto;">
  <tr><td>Windows</td><td><a href="/dist/TankWindows.zip">Download</a></td></tr>
  <tr><td>Mac</td><td><a href="/dist/TankMac.zip">Download</a></td></tr>
  <tr><td>Linux</td><td><a href="/dist/TankLinux.zip">Download</a></td></tr>
</table>

<?php
  $bodyContent = ob_get_contents();
  ob_end_clean();
  $pageTitle = 'Download';
  include('master.php');
?>
