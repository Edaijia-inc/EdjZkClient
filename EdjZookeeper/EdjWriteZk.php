<?php

/**
 * PHP Zookeeper
 * Edaijia php access zookeeper library
 *
 * @category  Libraries
 * @package   EdjZk
 * @author    Yu Chao<yuchao@edaijia-inc.cn>
 * @copyright Edaijia GPL 
 * @license   http://www.php.net/license The PHP License, version 3.01
 */

class EdjWriteZk {

  private static $instance = null;

  private $zookeeper = null;

  private function __construct($hostname){
    $this->zookeeper = new zookeeper($hostname);
  }

  public static function getInstance($hostname){
    if(!isset(self::$instance)){
      $class = __CLASS__;
      self::$instance = new $class($hostname);
    }
    return self::$instance;
  }

  public function watcher( $i, $type, $key ) {
    echo "Insider Watcher\n";

    // Watcher gets consumed so we need to set a new one
    $newvalue = $this->zookeeper->get( '/zk', array($this, 'watcher' ) );
    echo "The Watcher value is:".$newvalue."\n";
  }

  public function watch($path,$callback='default'){
    // path exist
    if($callback=='default'){
      $callback=array($this,'watcher');
    }

    $zk = $this->zookeeper->get('/zk',$callback);

  
  }
  public function write($path, $value, $acl = 'default'){
    if(!$this->zookeeper->exists($path)){
      $this->makePath($path);
      if($acl == 'default'){
        $this->makeNode($path,$value);
      }else{
        $this->makeNode($path,$value,$acl);
      }
    
    }else{
      $this->zookeeper->set($path,$value);
    }
  
  }
  public function makePath($path,$value=''){
    $parts = explode('/',$path);
    $parts = array_filter($parts);
    $subpath = '';

    while(count($parts) > 1){
      $subpath .= '/'.array_shift($parts);
      if(!$this->zookeeper->exists($subpath)){
        $this->makeNode($subpath,$value);
      }
    
    }
  
  }

  public function makeNode($path,$value,$params=array()){
    if(empty($params)){
      $params = array(
        array(
          'perms' => Zookeeper::PERM_ALL,
          'scheme'=> 'world',
          'id'    => 'anyone',
        )
      );
    }
    return $this->zookeeper->create($path,$value,$params);
  
  }

}
/*
$zoo = EdjWriteZk::getInstance('127.0.0.1:2181');
$path = "/Edj/devel/app1";
$value = "yuchao";
$acl = array(array(
  'perms' => Zookeeper::PERM_ALL,
  'scheme' => 'digest',
  'id'    => 'yuchao:YuChao',
));
$zoo -> write($path, $value);
/*
$zk = $zoo->watch( '/zk');

echo "The zk is ".$zk;
while( true ) {
  echo '.';
  sleep(2);
}
 */
?>
