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

class EdjParseIni {
  /**
   *  WRITE
   */
  static function write($fileName, $zkArray) {
    $sortZkArrary = null;
    foreach($zkArray as $key => $value){
      if(!is_array($value)){
        $sortZkArray[$key] = $value;
      }
    }
    foreach($zkArray as $key => $value){
      if(is_array($value)){
        $sortZkArray[$key] = $value;
      }
    }
    $string = '';
    foreach(array_keys($sortZkArray) as $key) {
      if(is_string($sortZkArray[$key])){
        $string .= $key. "='".$sortZkArray[$key]."'\n";
      }else{
        $string .= '['.$key."]\n";
        $string .= EdjParseIni::write_get_string($sortZkArray[$key], '')."\n";
      }
    }
    file_put_contents($fileName, $string);
  }
  /**
   *  write get string
   */
  static function write_get_string(& $zkArray, $prefix) {
    $string = '';
    ksort($zkArray);
    foreach($zkArray as $key => $val) {
      if (is_array($val)) {
        $string .= EdjParseIni::write_get_string($zkArray[$key], $prefix.$key.'.');
      } else {
        $string .= $prefix.$key." = '".str_replace("\n", "\\\n", EdjParseIni::set_value($val))."'\n";
      }
    }
    return $string;
  }
  /**
   *  manage keys
   */
  static function set_value($val) {
    if ($val === true) { return 'true'; }
    else if ($val === false) { return 'false'; }
      return $val;
  }
  /**
   *  READ
   */
  static function read($fileName) {
    $zkArray = array();
    $lines = file($fileName);
    $section = 'default';
    $multi = '';
    foreach($lines as $line) {
      if (substr($line, 0, 1) !== ';') {
        $line = str_replace("\r", "", str_replace("\n", "", $line));
        if (preg_match('/^\[(.*)\]/', $line, $m)) {
          $section = $m[1];
        } else if ($multi === '' && preg_match('/^([a-z0-9_.\[\]-]+)\s*=\s*(.*)$/i', $line, $m)) {
          $key = $m[1];
          $val = $m[2];
          if (substr($val, -1) !== "\\") {
            $val = trim($val);
            if($section == 'default'){
              EdjParseIni::manage_keys($zkArray, $key, $val);
            }else{
              EdjParseIni::manage_keys($zkArray[$section], $key, $val);
            }
            $multi = '';
          } else {
            $multi = substr($val, 0, -1)."\n";
          }
        } else if ($multi !== '') {
          if (substr($line, -1) === "\\") {
            $multi .= substr($line, 0, -1)."\n";
          } else {
            EdjParseIni::manage_keys($zkArray[$section], $key, $multi.$line);
            $multi = '';
          }
        }
      }
    }

    $buf = get_defined_constants(true);
    if(!empty($buf['user'])){
      $consts = array();
      foreach($buf['user'] as $key => $val) {
        $consts['{'.$key.'}'] = $val;
      }
      array_walk_recursive($zkArray, array('EdjParseIni', 'replace_consts'), $consts);
    }
    return $zkArray;
  }
  /**
   *  manage keys
   */
  static function get_value($val) {
    if (preg_match('/^-?[0-9]$/i', $val)) { return intval($val); } 
    else if (strtolower($val) === 'true') { return true; }
    else if (strtolower($val) === 'false') { return false; }
    else if (preg_match('/^"(.*)"$/i', $val, $m)) { return $m[1]; }
    else if (preg_match('/^\'(.*)\'$/i', $val, $m)) { return $m[1]; }
    else if (self::is_serialized($val)){return unserialize($val);}
      return $val;
  }

  /**
   * Check value to find if it was serialized.
   *
   * Array is set the serialized in zookeeper
   * If $data is not an string, then returned value will always be false.
   * Serialized data is always a string.
   *
   * @param mixed $data Value to check to see if was serialized.
   * @return bool False if not serialized and true if it was.
   */
  static function is_serialized( $data ) {
    // if it isn't a string, it isn't serialized
    if ( ! is_string( $data ) )
      return false;
    $data = trim( $data );
    if ( 'N;' == $data )
      return true;
    $length = strlen( $data );
    if ( $length < 4 )
      return false;
    if ( ':' !== $data[1] )
      return false;
    $lastc = $data[$length-1];
    if ( ';' !== $lastc && '}' !== $lastc )
      return false;
    $token = $data[0];
    switch ( $token ) {
    case 's' :
      if ( '"' !== $data[$length-2] )
        return false;
    case 'a' :
    case 'O' :
      return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
    case 'b' :
    case 'i' :
    case 'd' :
      return (bool) preg_match( "/^{$token}:[0-9.E-]+;\$/", $data );
    }
    return false;
  }

  /**
   *  manage keys
   */
  static function get_key($val) {
    if (preg_match('/^[0-9]$/i', $val)) { return intval($val); }
      return $val;
  }
  /**
   *  manage keys
   */
  static function manage_keys(& $zkArray, $key, $val) {
    if (preg_match('/^([a-z0-9_-]+)\.(.*)$/i', $key, $m)) {
      EdjParseIni::manage_keys($zkArray[$m[1]], $m[2], $val);
    } else if (preg_match('/^([a-z0-9_-]+)\[(.*)\]$/i', $key, $m)) {
      if ($m[2] !== '') {
        $zkArray[$m[1]][EdjParseIni::get_key($m[2])] = EdjParseIni::get_value($val);
      } else {
        $zkArray[$m[1]][] = EdjParseIni::get_value($val);
      }
    } else {
      $zkArray[EdjParseIni::get_key($key)] = EdjParseIni::get_value($val);
    }
  }
  /**
   *  replace utility
   */
  static function replace_consts(& $item, $key, $consts) {
    if (is_string($item)) {
      $item = strtr($item, $consts);
    }
  }

  /**
   * multi paser ini file include extends
   */
  static function read_multi($file, $process_sections = false, $scanner_mode = INI_SCANNER_NORMAL) {
    $explode_str = '.';
    $escape_char = "'";
    // load ini file the normal way
    $data = parse_ini_file($file, $process_sections, $scanner_mode);
    if (!$process_sections) {
      $data = array($data);
    }
    foreach ($data as $section_key => $section) {
      if(is_array($section)){
        // loop inside the section
        foreach ($section as $key => $value) {
          if (strpos($key, $explode_str)) {
            if (substr($key, 0, 1) !== $escape_char) {
              // key has a dot. Explode on it, then parse each subkeys
              // and set value at the right place thanks to references
              $sub_keys = explode($explode_str, $key);
              $subs =& $data[$section_key];
              foreach ($sub_keys as $sub_key) {
                if (!isset($subs[$sub_key])) {
                  $subs[$sub_key] = [];
                }
                $subs =& $subs[$sub_key];
              }
              // set the value at the right place
              $subs = $value;
              // unset the dotted key, we don't need it anymore
              unset($data[$section_key][$key]);
            }
            // we have escaped the key, so we keep dots as they are
            else {
              $new_key = trim($key, $escape_char);
              $data[$section_key][$new_key] = $value;
              unset($data[$section_key][$key]);
            }
          }
        }
      }
    }
    if (!$process_sections) {
      $data = $data[0];
    }
    return $data;
  }
}
?>
