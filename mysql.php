<?php

require_once "common.php";

define('MYSQL_ERR_INSERT_','1062');

class MysqlException extends RuntimeException {
  public $logRefNum;
  public function __construct($logRefNum) {
    parent::__construct();
    $this->logRefNum = $logRefNum;
  }
}
class MysqlQueryOneException extends MysqlException {
  public $recordCount;
  public function __construct($logRefNum, $recordCount) {
    parent::__construct($logRefNum);
    $this->recordCount = $recordCount;
  }  
}

function MysqlArrayToWhere($conditions) {
  $count = count($conditions);
  if($count <= 0) {return '';}
  else {return ' WHERE '.implode(' AND ',$conditions);}
}

//
// call: MysqlInit();
//       throws MysqlException (error is logged in the function)
//       $mysqlException->logRegNum is the log reference number
//
function MysqlInit()
{
  $mysql = mysql_connect('localhost', 'tank');
  if($mysql === FALSE) {
    $logRefNum = error_log_with_ref('mysql_connect failed: '. mysql_error());
    throw new MysqlException($logRefNum);
  }
  if(!mysql_select_db('tank')) {
    $logRefNum = error_log_with_ref('mysql_select_db(\'tank\') failed: '. mysql_error());
    mysql_close($mysql);
    throw new MysqlException($logRefNum);
  }
}

// call: $result = MysqlQuery('query...');
//       throws MysqlException on error
function MysqlQuery($query) {
  $result = mysql_query($query);
  if($result === FALSE) {
    $logRefNum = code_error_with_ref(__FILE__,__LINE__,"MysqlQuery('$query') failed: ".mysql_error());
    throw new MysqlException($logRefNum);
  }
  return $result;
}

// call: $count = MysqlCount($result);
//       throws MysqlException on error
function MysqlCount($result) {
  $count = mysql_num_rows($result);
  if($count === FALSE) {
    $logRefNum = code_error_with_ref(__FILE__,__LINE__,"MysqlCount('$result') failed: ".mysql_error());
    throw new MysqlException($logRefNum);
  }
  return $count;
}

// if query does not return exactly one entry
// then an exception is thrown
// NOTE: you should probably only call this if you know for sure that mysql not returning
//       a single row is a bug in your code.  If the condiitons depend on the client data you should
//       just use MysqlQueryOne instead and return a custom error if 1 row isn't returned
function MysqlOne($query) {
  $result = mysql_query($query);
  if($result === FALSE) {
    $logRefNum = code_error_with_ref(__FILE__,__LINE__,"MysqlOne('$query') failed: ".mysql_error());
    throw new MysqlException($logRefNum);
  }

  $count = mysql_num_rows($result);
  if($count === FALSE) {
    $logRefNum = code_error_with_ref(__FILE__,__LINE__,"mysql_num_rows after query '$query' failed: ".mysql_error());
    throw new MysqlException($logRefNum);
  }  

  if($count == 1) {
    $row = mysql_fetch_row($result);
    return $row[0];
  }

  $logRefNum = code_error_with_ref(__FILE__,__LINE__,"mysql_num_rows after query '$query' failed: ".mysql_error());
  throw new MysqlQueryOneException($logRefNum, $count);
}

// $result = MysqlQueryOne('query');
// if($result === 0) {
//   query returned no records
// } else {
//   array of columns
// }
function MysqlQueryOne($query) {
  $result = mysql_query($query);
  if($result === FALSE) {
    $logRefNum = code_error_with_ref(__FILE__,__LINE__,"MysqlQueryOne('$query') failed: ".mysql_error());
    throw new MysqlException($logRefNum);
  }

  $count = mysql_num_rows($result);
  if($count === FALSE) {
    $logRefNum = code_error_with_ref(__FILE__,__LINE__,"mysql_num_rows after query '$query' failed: ".mysql_error());
    throw new MysqlException($logRefNum);
  }

  if($count == 1) return mysql_fetch_row($result);
  if($count == 0) return 0;  

  $logRefNum = code_error_with_ref(__FILE__,__LINE__,"mysql_num_rows after query '$query' failed: ".mysql_error());
  throw new MysqlQueryOneException($logRefNum, $count);
}

// call: $count = MysqlRecordCount($table,$conditions);
function MysqlRecordCount($table, $conditions = NULL)
{
  if($conditions != NULl) {
    $conditions = " WHERE $conditions";
  }

  // Check Email/Regcode combination
  $result = MysqlQueryOne("SELECT Count(*) FROM $table $conditions;");
  if($result === 0) {
    $logRefNum = code_error_with_ref(__FILE__,__LINE__,"MysqlRecordCount($table, $conditions) expected to have 1 row but have ".$count);
    throw new MysqlException($logRefNum);
  }
  return $result[0];
}


// call: MysqlIPLoginSession();
//       list($ip,$genTime,$lastRequest,$logins) = MysqlIPLoginSession();
//       throws RuntimeException, MysqlException (or MysqlQueryOneException) on error
function MysqlIPLoginSession()
{
  if(!isset($_SERVER["REMOTE_ADDR"]))
    throw new RuntimeException("Missing \$_SERVER['REMOTE_ADDR']");

  $ip = ip2long($_SERVER["REMOTE_ADDR"]);
  if($ip === FALSE)
    throw new RuntimeException("The \$_SERVER['REMOTE_ADDR'] variable should be an ip address but it is '".$_SERVER["REMOTE_ADDR"]."'");

  $result = MysqlQueryOne("SELECT * FROM IPSessions WHERE ip=$ip");

  if($result === 0) {
    MysqlQuery("INSERT INTO IPSessions VALUES ('$ip',NOW(),NOW(),0);");
    $result = MysqlQueryOne("SELECT * FROM IPSessions WHERE ip=$ip");
    if($result === 0) {
      $logRefNum = code_error_with_ref(__FILE__,__LINE__,"created a new ipsession entry, and then failed to retrieve it");
      throw new MysqlException($logRefNum);
    }
    return $result;
  }

  // update last request time
  MysqlQuery("UPDATE IPSessions SET lastRequest=NOW() WHERE ip=$ip;");
  return $result;
}

// throws MysqlException on error
function MysqlNewSession($ip,$uid)
{
  // check if user already has a session
  $result = MysqlQueryOne("SELECT Sid,GenTime FROM Sessions WHERE Uid=$uid;");
  if($result === 0) {
    $sid = '';
    for($i = 0; $i < 5; $i++) {
      $sid = sha1($sid.rand());
    }
    if(isset($_SERVER["REMOTE_ADDR"])) {
      $sid = sha1($sid.$_SERVER["REMOTE_ADDR"]);
    }
    if(isset($_SERVER["REMOTE_PORT"])) {
      $sid = sha1($sid.$_SERVER["REMOTE_PORT"]);
    }
    MysqlQuery("INSERT INTO Sessions VALUES('$sid',NOW(),$uid,NOW(),$ip);");
  } else {
    list($sid,$genTime) = $result;
    // TODO: if genTime is too far in the past, then create a new sid (to help prevent hackers
    //       from using sids that have been found)
    MysqlQuery("UPDATE Sessions SET LastRequest=NOW() WHERE Uid=$uid;");
  }
  setcookie("Sid",$sid);
}

// call: $result = MysqlSession();
//       if($result === 0) { session not found }
//       list($sid,$genTime,$uid,$gid,$lastRequest,$ip) = $result;
// throws: MysqlException (or MysqlQueryOneException) on error
function MysqlSession()
{
  if(!isset($_COOKIE["sid"])) return 0;
  $sid = $_COOKIE["sid"];
  return MysqlQueryOne("SELECT * FROM Sessions WHERE sid='".$sid."';");
}

//
// call: list($uid,$fname,$lname) = MysqlEmailLookup($email);
//       if(!$uid) { error }
//
function MysqlEmailLookup($email)
{
  // Check Email/Regcode combination
  $result = mysql_query('SELECT uid,fname,lname FROM Users WHERE email="'.$email.'";');
  if(!$result) {
    code_error(__FILE__,__LINE__,'MysqlEmailLookup('.$email.') mysql_query failed: '. mysql_error());
    return null;
  }
  $count = mysql_num_rows($result);
  if($count === FALSE) {
    code_error(__FILE__,__LINE__,'MysqlEmailLookup('.$email.') mysql_num_rows failed returned null');
    return null;
  }

  if($count == 1) return mysql_fetch_row($result);
  if($count != 0) {
    code_error(__FILE__,__LINE__,'MysqlUidLookup('.$uid.') expected mysql_num_rows to return 1 but returned '.$count);
  }
  return null;
}

//
// call: list($isadmin,$email,$fname,$lname) = MysqlUidLookup($uid);
//       if(!$email) { then user not found }
//
function MysqlUidLookup($uid)
{
  // Check Email/Regcode combination
  $result = mysql_query('SELECT isadmin,email,fname,lname FROM Users WHERE uid="'.$uid.'";');
  if($result === FALSE) {
    code_error(__FILE__,__LINE__,'MysqlUidLookup('.$uid.') mysql_query failed: '. mysql_error());
    return null;
  }
  $count = mysql_num_rows($result);
  if($count === FALSE) {
    code_error(__FILE__,__LINE__,'MysqlUidLookup('.$uid.') mysql_num_rows failed returned null');
    return null;
  }

  if($count == 1) return mysql_fetch_row($result);
  if($count != 0) {
    code_error(__FILE__,__LINE__,'MysqlUidLookup('.$uid.') expected mysql_num_rows to return 1 but returned '.$count);
  }
  return null;
}

// call: list($groupName) = MysqlUidLookup($uid);
//       if(!$groupName) { then group not found }
function MysqlGidLookup($gid)
{
  $result = mysql_query('SELECT name FROM groups WHERE gid="'.$gid.'";');
  if($result === FALSE) {
    code_error(__FILE__,__LINE__,'MysqlGidLookup('.$gid.') mysql_query failed: '. mysql_error());
    return null;
  }
  $count = mysql_num_rows($result);
  if($count === FALSE) {
    code_error(__FILE__,__LINE__,'MysqlGidLookup('.$gid.') mysql_num_rows failed returned null');
    return null;
  }

  if($count == 1) return mysql_fetch_row($result);
  if($count != 0) {
    code_error(__FILE__,__LINE__,'MysqlGidLookup('.$gid.') expected mysql_num_rows to return 1 (or even 0) but returned '.$count);
  }

  return null;
}

// return NULL on error and TRUE/FALSE otherwise
function MysqlTableExists($table)
{
  $result = mysql_query("SELECT count(*) FROM information_schema.tables where table_name='".$table."';");
  if($result === FALSE) {
    code_error(__FILE__,__LINE__,"MysqlTableExists(".$table.") mysql_query returned FALSE: ".mysql_error());
    return null;
  }
  $row = mysql_fetch_row($result);
  if($row === FALSE) {
    code_error(__FILE__,__LINE__,"MysqlTableExists(".$table.") mysql_fetch_row returned FALSE: ".mysql_error());
    return null;
  }
  if($row[0] == 1) return TRUE;
  if($row[0] == 0) return FALSE;

  code_error(__FILE__,__LINE__,"MysqlTableExists(".$table.") mysql_fetch_row, expected first element to be 0 or 1 but was ".$row[0]);
  return null;
}

// call: $dataColumns = MysqlGetGroupDataColumns($gid);
//       foreach($dataColumns as $column) {
//         list($columnName,$columnType,$columnNullable,$columnDefault) = $column;
//         // ...
//       }
function MysqlGetGroupDataColumns($gid)
{
  // Print Data Columns
  $result = MysqlQuery("SELECT column_name,column_type,is_nullable,column_default FROM information_schema.columns WHERE table_name='group{$gid}data';");

  $dataColumns = array();
  while(TRUE) {
    $row = mysql_fetch_row($result);
    if(!$row) return $dataColumns;
    array_push($dataColumns, $row);
  }
}

// returns error if length of $keys is invalid
function MysqlPrintResultAsJsonObjects($result, $keys)
{
  echo '[';
  $atFirst = TRUE;
  while(TRUE) {
    $row = mysql_fetch_row($result);
    if(!$row) break;
    if($atFirst) { $atFirst = FALSE; } else { echo ','; }
    echo json_encode(array_combine($keys,$row));
  }
  echo ']';
}

function MysqlPrintResultAsJsonArrays($result)
{
  echo '[';
  $row = mysql_fetch_row($result);
  if($row) {
    echo json_encode($row);
    while(TRUE) {
      $row = mysql_fetch_row($result);
      if(!$row) break;
      echo ',';
      echo json_encode($row);
    }
  }
  echo ']';
}

?>