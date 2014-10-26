<?php

class ElectricYahoo {
  const URL = 'http://setsuden.yahooapis.jp/v1/Setsuden/latestPowerUsage';
  const CACHE_FILE = '/var/tmp/electric.cache';
  const APPID = 'Your app ID';

  private static $_data = null;

  public static function getData($area) {
    $data = self::loadCache($area);

    if (@$data['time'] && time() - (60 * 20) < $data['time']) {
      // cache hit
      self::$_data[$area] = $data;
      return;
    }

    $url = self::URL . '?appid=' . self::APPID . '&area=' . $area;
    $list = file($url);

    if (self::$_data == null) {
      self::$_data = array();
    }
    $tmp = (array)simplexml_load_string($list[0]);
    $tmp['usage'] = intval($tmp['Usage']/10000);
    $tmp['capacity'] = intval($tmp['Capacity']/10000);
    $tmp['time'] = time();
    self::$_data[$area] = $tmp;

    self::saveCache($area);
  }

  public static function saveCache($area) {
    $serial = serialize(self::$_data[$area]);
    $cache_file = self::getCacheFileName($area);
    file_put_contents($cache_file, $serial);
  }

  public static function getCacheFileName($area) {
    return self::CACHE_FILE . '.' . $area;
  }

  public static function loadCache($area) {
    $cache_file = self::getCacheFileName($area);
    $serial = @file_get_contents($cache_file);
    if (@$serial == '') {
      return array();
    }
    return @unserialize($serial);
  }

  public static function get($area, $key) {
    if ($key == 'rate') {
      return self::getRate($area);
    }

    if (self::$_data == null || @is_array(self::$_data[$area]) == false) {
      self::getData($area);
    }

    return self::$_data[$area][$key];
  }

  public static function getRate($area) {
    $capacity = self::get($area, 'capacity');
    $usage = self::get($area, 'usage');
    
    if ($capacity == 0 || $usage == 0) {
      return '-.-%';
    }

    $rate = 100 * $usage / $capacity;
    return sprintf('%3.1f%%', $rate);
  }
}
