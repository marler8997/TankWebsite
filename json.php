<?php

/* Usage
$json = new JsonObject();

$json->addString('uid','2');
$json->addString('gid','23');

$json = $json->startArray('myarray');
$json->addString('dog');
$json->addBoolean(FALSE);
$json->addNull();
$json = $json->end();

$json->endAll();
*/

class JsonObject {
  public $parent;
  private $first;

  function __construct($parent = NULL) {
    $this->parent = $parent;
    echo '{';
    $this->first = TRUE;
  }

  function startArray($id) {
    if($this->first) { $this->first = FALSE; } else { echo ','; }
    echo "\"$id\":";
    return new JsonArray($this);
  }

  function startObject($id) {
    if($this->first) { $this->first = FALSE; } else { echo ','; }
    echo "\"$id\":";
    return new JsonObject($this);
  }

  function addArray($id,$array) {
    if($this->first) { $this->first = FALSE; } else {echo ',';}
    echo '"'.$id.'":[';
    $atFirst = TRUE;
    foreach($array as $element) {
      if($atFirst) { $atFirst = FALSE; } else {echo ',';}
      echo json_encode($element);                
    }
    echo ']';
  }
  function addText($id,$text) {
    if($this->first) { $this->first = FALSE; } else { echo ','; }
    echo "\"$id\":$text";
  }
  function addString($id,$str) {
    if($this->first) { $this->first = FALSE; } else { echo ','; }
    echo "\"$id\":\"$str\"";
  }
  function addNumber($id,$num) {
    if($this->first) { $this->first = FALSE; } else { echo ','; }
    echo "\"$id\":$num";
  }
  function addBoolean($id,$bool) {
    if($this->first) { $this->first = FALSE; } else { echo ','; }
    if($bool) { echo "\"$id\":true"; } else { echo "\"$id\":false"; }
  }
  function addNull($id) {
    if($this->first) { $this->first = FALSE; } else { echo ','; }
    echo "\"$id\":null";
  }

  // returns parent
  function end() {
    echo '}';
    return $this->parent;
  }
  function endAll() {
    $next = $this->end();
    while($next) {
      $next = $next->end();
    }
  }

  function errorEnd($error) {
    $json = $this;
    while($json->parent != NULL) {
      $json = $json->end();
    }
    $json->addText('error',json_encode($error));
    $json->end();
  }
  function ex($error = null) {
    if($error) {$this->errorEnd($error);}
    else {$this->endAll();}
    exit();
  }
}

class JsonArray {
  public $parent;
  private $first;

  function __construct($parent = NULL) {
    $this->parent = $parent;
    echo '[';
    $this->first = TRUE;
  }

  function startArray() {
    if($this->first) { $this->first = FALSE; } else { echo ','; }
    return new JsonArray($this);
  }

  function startObject($id) {
    if($this->first) { $this->first = FALSE; } else { echo ','; }
    return new JsonObject($this);
  }

  function addArray($array) {
    if($this->first) { $this->first = FALSE; } else {echo ',';}
    echo '[';
    $atFirst = TRUE;
    foreach($array as $element) {
      if($atFirst) { $atFirst = FALSE; } else {echo ',';}
      echo json_encode($element);                
    }
    echo ']';
  }
  function addText($text) {
    if($this->first) { $this->first = FALSE; } else { echo ','; }
    echo $text;
  }
  function addString($str) {
    if($this->first) { $this->first = FALSE; } else { echo ','; }
    echo "\"$str\"";
  }
  function addNumber($num) {
    if($this->first) { $this->first = FALSE; } else { echo ','; }
    echo $num;
  }
  function addBoolean($bool) {
    if($this->first) { $this->first = FALSE; } else { echo ','; }
    if($bool) { echo 'true'; } else { echo 'false'; }
  }
  function addNull() {
    if($this->first) { $this->first = FALSE; } else { echo ','; }
    echo 'null';
  }

  // returns parent
  function end() {
    echo ']';
    return $this->parent;
  }

  function endAll() {
    $next = $this->end();
    while($next) {
      $next = $next->end();
    }
  }
  function errorEnd($error) {
    $json = $this;
    while($json->parent != NULL) {
      $json = $json->end();
    }
    $json->addText('error',json_encode($error));
    $json->end();
  }
  function ex($error = null) {
    if($error) {$this->errorEnd($error);}
    else {$this->endAll();}
    exit();
  }
}

?>