<?php
require_once('common.php');
require_once('mysql.php');
require_once('json.php');

header('Content-Type: application/json');
$json = new JsonObject();

try {

  MysqlInit();

  list($ip,$genTime,$lastRequest,$logins) = MysqlIPLoginSession();
  if($logins > MAX_LOGINS) $json->ex("You've tried to login over ".MAX_LOGINS." times, you'll have to either wait or contact us to fix your login");

  $result = TryToLogin();
  if(is_string($result)) $json->ex($result);

  list($uid,$userName) = $result;

  MysqlNewSession($ip,$uid);
  
  header('Location: /index.php');
  $json->endAll();


} catch(MysqlException $me) {
  $json->ex('Server Error: your reference number is '.$me->logRefNum);
}
