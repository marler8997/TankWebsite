<?php 
include('master_login.php');
ob_start();
?>

<?php
require_once('common.php');
require_once('mysql.php');

$loginErrorMessage = null;

  if(!isset($_REQUEST['UserName'])) goto SEND_PAGE;
  if(!isset($_REQUEST['Email'])) {
    $loginErrorMessage = 'Missing Email';
    goto SEND_PAGE;
  }
  if(!isset($_REQUEST['Password'])) {
    $loginErrorMessage = 'Missing password';
    goto SEND_PAGE;
  }

  $userName = trim($_REQUEST['UserName']);
  if(empty($userName)) {
    $loginErrorMessage = 'Empty UserName';
    goto SEND_PAGE;
  }
  if(!IsValidUserName($userName)) {
    $loginErrorMessage = "The User Name you supplied '$userName' is invalid";
    goto SEND_PAGE;
  }
  
  $email = trim($_REQUEST['Email']);
  if(empty($email)) {
    $loginErrorMessage = 'Empty Email';
    goto SEND_PAGE;
  }
  if(!IsValidEmail($email)) {
    $loginErrorMessage = "The Email you supplied '$email' is invalid";
    goto SEND_PAGE;
  }

  $password = trim($_REQUEST['Password']);
  if(empty($password)) {
    $loginErrorMessage = 'Empty password';
    goto SEND_PAGE;
  }

  $salt = sha1(rand());
  $passwordHash = passwd($password, $salt);

try {

  // Register the user
  MysqlQuery("INSERT INTO Users VALUES(0,'$userName','$email',FALSE,'$salt','$passwordHash','FirstName','LastName','8881114444',NULL,NULL,NULL);");

} catch(MysqlException $me) {
  $loginErrorMessage = 'Server Error: your reference number is '.$me->logRefNum;
}
  
SEND_PAGE:

if($loginErrorMessage) {
  echo '<span class="Error">'.$loginErrorMessage.'</span>';
}

?>

<form method="POST" action="/register.php">
  <table>
    <tr><td>User Name: </td><td><input type="text" name="UserName"/>     </td></tr>
    <tr><td>Email:     </td><td><input type="text" name="Email" />       </td></tr>
    <tr><td>Password:  </td><td><input type="password" name="Password" /></td></tr>
    <tr><td>           </td><td><input type="submit" />                  </td></tr>
  </table>
</form>


<?php
  $bodyContent = ob_get_contents();
  ob_end_clean();
  
  $pageTitle = 'Register';

  include('master.php');
?>
