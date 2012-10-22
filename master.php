<?php
$loggedIn = $_GLOBALS['LoggedIn'];
if($loggedIn) {
  $uid      = $_GLOBALS['LoggedInUid'];
  list($userName,$allowPlay) = MysqlOneRow("SELECT UserName,AllowPlay FROM Users WHERE Uid=$uid;");
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Tank - <?php echo $pageTitle; ?></title>
  <script type="text/javascript">
    function logout() {
      document.cookie = 'Sid=; expires=Thu, 01-Jan-70 00:00:01 GMT;';
      document.location = '/index.php?Logout';
    }
  </script>
  <link href="style.css" rel="stylesheet" type="text/css" />
  <style type="text/css">
    <?php
       echo "#${pageTitle}NavButton{background:#aaa;color:#000;}";
    ?>
  </style>
</head>
<body>
  <div id="PageDiv">
<?php
if($loggedIn) {
  echo '<div id="UserNameDiv">';
  echo   $userName;
  echo   '<span class="Button" style="background:#aaa;color:#000;" onclick="logout()">Logout</span>';
  echo '</div>';
} else {
  echo '<div id="UserNameDiv">';
  echo   '<form style="display:inline-block;" method="POST" action="/cookielogin.php">';
  echo     'UserName: <input type="text" name="UserName" /> ';
  echo     'Password: <input type="password" name="Password" /> ';
  echo     '<input class="Button" style="background:#aaa;color:#000;" type="submit" value="Login"/>';
  echo   '</form> ';
  echo   '<a href="/register.php" style="color:#fff;">Register</a>';
  echo '</div>';
}
?>
    <div id="HeaderDiv">
      <img src="/img/Tank.png" alt="Tank" />
    </div>
    <div id="NavDiv">
      <span id="HomeNavButton"     class="Button" onclick="window.location='/index.php'">Home</span>
      <span id="DownloadNavButton" class="Button" onclick="window.location=\'/download.php\'">Download</span>
<?php
if($loggedIn) {
  echo '<span id="OfflineKeysNavButton" class="Button" onclick="window.location=\'/offlinekeys.php\'">Offline Keys</span>';
} else {
}



?>
    </div>
    <div id="BodyDiv">
      <?php echo $bodyContent; ?>
    </div>
  </div>
</body>
</html>
