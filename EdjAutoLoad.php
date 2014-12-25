<?php

/**
 * PHP Zookeeper
 * 
 * The main library. Just an autoloader, actually.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see {@link http://www.gnu.org/licenses/}.
 *
 * Edaijia php access zookeeper library
 *
 * @category  Libraries
 * @package   EdjZk
 * @author    Yu Chao<yuchao@edaijia-inc.cn>
 * @version   1.0
 * @copyright Edaijia GPL 
 * @license   http://www.php.net/license The PHP License, version 3.01
 */


function EdjZkautoloader($className)
{
  $filename=dirname(__FILE__)."/EdjZookeeper/".$className.".php";
  if (!is_readable($filename)){
    return false;
  }else{
    include $filename;
  }
  return true;
}

spl_autoload_register('EdjZkautoloader',false);

