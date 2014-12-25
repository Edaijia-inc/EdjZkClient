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

require_once dirname(dirname(__FILE__))."/EdjAutoLoad.php";
if (!file_exists(dirname(__FILE__)."/config.php")) {
	echo
		"In order to execute the PHPUnit tests you need a config.php file\n".
		"in directory phpunit. Please use config.sample.php as a template.\n";
	exit;
}
require_once "config.php";

class EdjZkInstanceTest extends PHPUnit_Framework_TestCase
{

	protected $zl;

	protected function setUp()
	{
		$this->zl=EdjZkInstance::single(EDJZK_HOSTLIST);
	}

	public function testSingleObject()
	{
    $single = EdjZkInstance::single(EDJZK_HOSTLIST);

		$this->assertTrue((bool) $single, "single object is succeed.");
	}


}
