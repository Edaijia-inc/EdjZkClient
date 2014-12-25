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

class EdjReadZk{
  private static $instance = null;

  private $zookeeper = null;

  public function __construct($hostname){
    $this->zookeeper = EdjZkInstance::single($hostname);
  }
  /**
   * single object instance
   * @param $host hostname
   */
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

  public function read($path, $acl = 'default'){
    //return $this->zookeeper->get($path);
    if($this->zookeeper->exists($path)){
      if($acl == 'default'){
        return $this->zookeeper->get($path);
      }else{
        return $this->zookeeper->aclGet($path,$acl);
      }
    }else{
      return null;
    }

  }

  public function aclGet($path,$acl =''){
    if(empty($acl)){
      $acl= array(
        array(
          'perms' => Zookeeper::PERM_ALL,
          'scheme'=> 'world',
          'id'    => 'anyone',
        )
      );
    }
    return $this->zookeeper->get($path,$acl);
  
  }
  public function getChildren($path, $acl=''){
    if(strlen($path) > 1 && preg_match('@/$@',$path)){
      $path = substr($path , 0 , -1);
    }
    return $this->zookeeper->getChildren($path);
  
  }

}
/*
$zoo = new EdjReadZk('127.0.0.1:2181');
$path = "/Edj/devel";
$acl = array(array(
  'perms' => Zookeeper::PERM_ALL,
  'scheme' => 'digest',
  'id'    => 'yuchao:YuChao',
));
$app1 = $zoo -> read($path);
//$app1 = $zoo -> getChildren($path); 

var_dump($app1);

echo "The zk is ".$app1;
while( true ) {
  echo '.';
  sleep(2);
}
 */

?>
