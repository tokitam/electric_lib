<?php

class ElectricPower {
  const URL = 'http://tepco-usage-api.appspot.com/latest.json';
  const CACHE_FILE = '/var/tmp/tepco.cache';

  private static $_data = null;

  public static function getData() {
    $data = self::loadCache();
    if (@$data['year'] && time() - (60 * 20) < $data['time']) {
      // cache hit
      self::$_data = $data;
      return;
    }

    $list = file(self::URL);
    $tmp = '';
    foreach ($list as $val) {
      $tmp .= ($val . ' ');
    }

    self::$_data = json_decode($tmp, true);
    self::$_data['time'] = time();

    self::saveCache();
  }

  public static function saveCache() {
    $serial = serialize(self::$_data);
    file_put_contents(self::CACHE_FILE, $serial);
  }

  public static function loadCache() {
    $serial = @file_get_contents(self::CACHE_FILE);
    if (@$serial == '') {
      return array();
    }
    return @unserialize($serial);
  }

  public static function get($key) {
    if ($key == 'rate') {
      return self::getRate();
    }

    if (self::$_data == null) {
      self::getData();
    }

    return self::$_data[$key];
  }

  public static function getRate() {
    $rate = 100 * self::get('usage') / self::get('capacity');
    return sprintf('%3.1f%%', $rate);
  }
}
