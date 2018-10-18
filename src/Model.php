<?php

namespace Demir\Database;

class Model
{
  /**
   * Tablo adını tutar
   *
   * @static string
   */
  public static $table;

  /**
   * Birinci anahtar adını tutar
   *
   * @static string
   */
  public static $primaryKey = 'id';

  /**
   * Kullanılacak bağlantı adını tutar
   *
   * @static string
   */
  public static $connection = 'default';

  /**
   * SELECT ifadesini ayarlar
   *
   * @static string
   */
  protected static $select = '*';

  /**
   * Sayfalama ayarlarını tutar
   *
   * @static Pagination
   */
  protected static $pagination;

  /**
   * Tablo ismiyle sorgu çalıştırır
   *
   * @param string $query
   * @param array $params
   * @return \PDOStatement
   * @throws \Exception
   */
  public static function tableQuery(
    string $query,
    array $params = []
  ) : \PDOStatement
  {
    static::boot();
    static::$select = '*';
    return CRUD::connection(static::$connection)->query($query, $params);
  }

  /**
   * Sorgu çalıştırır
   *
   * @param string $query
   * @param array $params
   * @return \PDOStatement
   * @throws \Exception
   */
  public static function query(
    string $query = '',
    array $params = []
  ) : \PDOStatement
  {
    return CRUD::connection(static::$connection)
      ->query($query, $params);
  }

  /**
   * Tablo adı tanımlanmamışsa istisna fırlatır
   *
   * @throws \Exception
   * @return void
   */
  private static function boot() : void
  {
    if (is_null(static::$table)) {
      $class = get_class(new static());
      $class = explode('\\', $class);
      static::$table = strtolower(end($class));
    }
  }

  /**
   * Son eklenen satırın ID'sini döndürür
   *
   * @return int
   * @throws \Exception
   */
  public static function lastInsertId() : int
  {
    return (int) Connection::get(static::$connection)
      ->lastInsertId();
  }

  /**
   * SELECT ifadesini belirler
   *
   * @param string $select
   * @return Model
   */
  public static function select(string $select) : Model
  {
    static::$select = $select;
    return new static();
  }

  /**
   * Sorgu çalıştırıp, çoğul satır döndürür
   *
   * @param string $query
   * @param array $params
   * @return mixed
   * @throws \Exception
   */
  public static function get(string $query = '', array $params = [])
  {
    [$select, $table] = [static::$select, static::$table];
    return static::tableQuery(
      "SELECT {$select} FROM {$table} {$query}", $params
    )->fetchAll();
  }

  /**
   * Sorgu çalıştırıp, çoğul satır döndürür
   *
   * @param string $query
   * @param array $params
   * @return mixed
   * @throws \Exception
   */
  public static function all(string $query = '', array $params = [])
  {
    return static::get($query, $params);
  }

  /**
   * Sorgu çalıştırıp, tekil satır döndürür
   *
   * @param string $query
   * @param array $params
   * @return mixed
   * @throws \Exception
   */
  public static function first(string $query = '', array $params = [])
  {
    [$select, $table] = [static::$select, static::$table];
    return static::tableQuery(
      "SELECT {$select} FROM {$table} {$query}", $params
    )->fetch();
  }

  /**
   * Sorgu çalıştırıp, sütun döndürür
   *
   * @param string $query
   * @param array $params
   * @return mixed
   * @throws \Exception
   */
  public static function column(string $query = '', array $params = [])
  {
    [$select, $table] = [static::$select, static::$table];
    return static::tableQuery(
      "SELECT {$select} FROM {$table} {$query}", $params
    )->fetchColumn();
  }

  /**
   * INSERT ifadesi hazırlayıp çalıştırır
   * Yeni veri ekler
   *
   * @param array $data
   * @param array $duplicateKey
   * @return \PDOStatement
   * @throws \Exception
   */
  public static function insert(
    array $data,
    array $duplicateKey = []
  ) : \PDOStatement
  {
    static::boot();
    return CRUD::insert(static::$table, $data, $duplicateKey);
  }

  /**
   * UPDATE ifadesi hazırlayıp çalıştırır
   * Varolan veriyi günceller
   *
   * @param array $data
   * @param int|string $query
   * @param array $params
   * @return \PDOStatement
   * @throws \Exception
   */
  public static function update(
    array $data,
    $query,
    array $params = []
  ) : \PDOStatement
  {
    static::boot();
    $primaryKey = static::$primaryKey;

    if (is_numeric($query)) {
      $query = "WHERE {$primaryKey}=?";
      $params[] = $query;
    }

    return CRUD::update(static::$table, $data, $query, $params);
  }

  /**
   * DELETE ifadesi hazırlayıp çalıştırır
   * Belirlenen şarttaki verileri siler
   *
   * @param int|string $query
   * @param array $params
   * @return \PDOStatement
   * @throws \Exception
   */
  public static function delete(
    $query,
    array $params = []
  ) : \PDOStatement
  {
    static::boot();
    $primaryKey = static::$primaryKey;

    if (is_numeric($query)) {
      $query = "WHERE {$primaryKey}=?";
      $params[] = $query;
    }

    return CRUD::delete(static::$table, $query, $params);
  }

  /**
   * Verileri sayfalayıp döndürür
   *
   * @param string $query
   * @param array $params
   * @return mixed
   * @throws \Exception
   */
  public static function paginate(array $pagination = [], string $query = '', array $params = [])
  {
    $perPage = $pagination['perPage'] ?? 10;
    $page = $_GET['page'] ?? 1;
    $currentPage = $pagination['currentPage'] ?? $page;
    $url = $pagination['url'] ?? '?url={number}';

    $select = static::$select;
    $count = static::select('count(*)')->column($query, $params);
    static::$pagination = new Pagination(
      intval($count), 
      intval($perPage), 
      intval($currentPage), 
      $url
    );
    $limit = static::$pagination->getLimit();
    return static::select($select)->get($query . " LIMIT {$limit}", $params);
  }

  /**
   * Sayfalama sınıfını döndürür
   *
   * @return Pagination
   * @throws \Exception
   */
  public static function getPagination()
  {
    if (is_null(static::$pagination)) {
      throw new \Exception('Sayfalama sınıfı başlatılmamış');
    }

    return static::$pagination;
  }
}
