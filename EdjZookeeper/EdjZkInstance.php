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

class EdjZkInstance
{
  /**
   * @var Zookeeper static 
   */
  private static $zookeeper;

  /**
   * private Constructor
   * ommit the new object
   *
   * @param string $address CSV list of host:port values (e.g. "host1:2181,host2:2181")
   */
  private function __construct() {
    //self::zookeeper = new Zookeeper($address);
  }

  /**
   * single instance for zookeeper
   *
   * @param the host list
   */
  public static function single($address){
    if(!isset(self::$zookeeper)){
      $c= __CLASS__;
      self::$zookeeper = new Zookeeper($address);
    }
    return self::$zookeeper;
  
  }

  /**
   * deny clone function for zookeeper
   *
   * @param empty 
   */
  public function __clone(){
   trigger_error('clone the zookeeper object is not allow', E_USER_ERROR);
  }

  /**
   * test function for zookeeper
   *
   * @param empty 
   */
  public function instance(){
    echo 'edaijia instance';
  }

}
/*
$zk = EdjZkInstance::single('localhost:2181');

var_dump($zk->get("/"));

//$zk = new EdjZkInstance();
$clonezk = clone $zk;
*/
