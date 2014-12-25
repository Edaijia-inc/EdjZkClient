<?php

/**
 * PHP Zookeeper
 * Edaijia php access zookeeper library
 *
 * @category  Libraries
 * @package   EdjEdaijia
 * @author    Yu Chao<yuchao@edaijia-inc.cn>
 * @copyright Edaijia GPL 
 * @license   http://www.php.net/license The PHP License, version 3.01
 */

class EdjZkWatcher extends Zookeeper {

  private $path= '/Edaijia';

  public function watcher( $i, $type, $key ) {
    //echo "Insider Watcher $i,'|',$type,'|',$key\n";
    $this->path = $key;

    // Watcher gets consumed so we need to set a new one
    $newvalue = $this->get( $this->path, array($this, 'watcher' ) );
    //echo "The Watcher value is:".$newvalue."\n";
  }

  public function watch($path,$callback='default'){
    // path exist
    if($callback =='default'){
      $callback=array($this,'watcher');
    }else{
      $callback=array($this, 'watch');
    }
    $Edaijia = $this->get($path, $callback);
    //echo "watch is ".$Edaijia."\n";
    return $Edaijia;
  }

}

$zoo = new EdjZkWatcher('121.40.173.217:2181');
//$zoo->set("/Edaijia","yuchaodemo");
//$Edaijia = $zoo->get( '/Edaijia', array($zoo, 'watcher' ) );
$watch_Edaijia = $zoo->watch( '/Edaijia/test/page/page_index');
$other = $zoo->watch('/Edaijia/test/page/other');

//echo "The Edaijia is ".$Edaijia;
echo "The Edaijia is ".$watch_Edaijia;
while( true ) {
  echo '.';
  sleep(2);
}

?>