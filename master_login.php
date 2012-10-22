<?php

require_once('mysql.php');

$_GLOBALS['LoggedIn'] = FALSE;

try {

  MysqlInit();

  // Check for cookies
  $result = MysqlSession();
  if($result !== 0) {
    list($sid,$genTime,$uid,$lastRequest,$ip) = $result;
    $_GLOBALS['LoggedIn'] = TRUE;
    $_GLOBALS['LoggedInUid'] = $uid;
  }

} catch(MysqlException $me) {
  $loginError = 'Server Error: your reference number is '.$me->logRefNum;
}


?>