<?php

namespace Demir\Database;

class Connection
{
  /**
   * PDO nesnelerini tutar
   *
   * @static array
   */
  protected static $collection = [];

  /**
   * Koleksiyona PDO bağlantısı ekler
   *
   * @param \PDO $pdo
   * @param string $name
   */
  public function __construct(\PDO $pdo, string $name = 'default')
  {
    static::$collection[$name] = $pdo;
  }

  /**
   * Koleksiyonda varsa isme göre bağlantı döndürür
   *
   * @param string $name
   * @throws \Exception
   * @return \PDO
   */
  public static function get(string $name = 'default') : \PDO
  {
    if (static::has($name)) {
      return static::$collection[$name];
    }

    throw new \Exception("'{$name}' isminde bir bağlantı mevcut değil");
  }

  /**
   * Bağlantı mevcut mu diye bakar
   *
   * @param string $name
   * @return bool
   */
  public static function has(string $name) : bool
  {
    return key_exists($name, static::$collection);
  }
}
