<?php

namespace Demir\Database;

class Query
{
  /**
   * SQL ifadelerini tutarlar
   *
   * @var string
   */
  protected 
    $select, 
    $from, 
    $where, 
    $having, 
    $join, 
    $orderBy, 
    $groupBy, 
    $limit, 
    $insert, 
    $update, 
    $delete;

  /**
   * Sorgu oluşturma modunu tutar
   *
   * @var string
   */
  protected $mode;

  public function __construct()
  {
    $this->select = 'SELECT *';
    $this->mode = 'select';
  }

  /**
   * SELECT ifadesi hazırlar
   *
   * @param string $select
   * @return Query
   */
  public function select(string $select = '*') : Query
  {
    $this->select = "SELECT {$select}";
    return $this;
  }

  /**
   * FROM ifadesi hazırlar
   *
   * @param string $from
   * @return Query
   */
  public function from(string $from) : Query
  {
    $this->from = "FROM {$from}";
    return $this;
  }

  /**
   * SELECT * FROM ifadesi hazırlar
   *
   * @param string $from
   * @param string $select
   * @return Query
   */
  public function table(string $from, string $select = '*') : Query
  {
    return $this->select($select)->from($from);
  }

  /**
   * WHERE ifadesi hazırlar
   *
   * @param string|\Closure $where
   * @return Query
   */
  public function where($where) : Query
  {
    if (is_null($this->where)) {
      $this->where = "WHERE ";
    }

    if ($where instanceof \Closure) {
      $where->call($this);
    } elseif (is_string($where)) {
      $this->where .= " {$where}";
    }

    return $this;
  }

  /**
   * HAVING ifadesi hazırlar
   *
   * @param string|\Closure $having
   * @return Query
   */
  public function having($having) : Query
  {
    if (is_null($this->having)) {
      $this->having = "HAVING ";
    }

    if ($having instanceof \Closure) {
      $having->call($this);
    } elseif (is_string($having)) {
      $this->having .= " {$having}";
    }

    return $this;
  }

  /**
   * JOIN ifadesi hazırlar
   *
   * @return Query
   */
  public function join() : Query
  {
    $args = func_get_args();

    if (count($args) === 1) {
      $type = 'INNER';
      [$join] = $args;
    } elseif (count($args) === 2) {
      [$type, $join] = $args;
      $type = strtoupper($type);
    } else {
      throw new \Exception('Geçersiz argümanlar');
    }

    $this->join .= "{$type} JOIN {$join} ";

    return $this;
  }

  /**
   * ORDER BY ifadesi hazırlar
   *
   * @param string $orderBy
   * @return Query
   */
  public function orderBy(string $orderBy) : Query
  {
    $this->orderBy = "ORDER BY {$orderBy}";
    return $this;
  }

  /**
   * GROUP BY ifadesi hazırlar
   *
   * @param string $groupBy
   * @return Query
   */
  public function groupBy(string $groupBy) : Query
  {
    $this->groupBy = "GROUP BY {$groupBy}";
    return $this;
  }

  /**
   * INSERT INTO ifadesi hazırlar
   *
   * @param array $data
   * @return Query
   */
  public function insert($data) : Query
  {
    $this->mode = 'insert';
    $table = str_replace('FROM ', '', $this->from);
    $this->insert = "INSERT INTO {$table} ";
    if (is_string($data)) {
      $this->insert .= $data;
    } elseif (is_array($data)) {
      $this->insert .= $this->arrayToString($data, 'insert');
    }
    
    return $this;
  }

  /**
   * ON DUPLICATE KEY UPDATE ifadesi hazırlar
   *
   * @param array $data
   * @return Query
   */
  public function onDuplicateKeyUpdate($data) : Query
  {
    $this->insert .= ' ON DUPLICATE KEY UPDATE ';

    if (is_string($data)) {
      $this->insert .= $data;
    } elseif (is_array($data)) {
      $this->insert .= $this->arrayToString($data, 'update');
    }

    return $this;
  }

  /**
   * UPDATE ifadesi hazırlar
   *
   * @param array $data
   * @return Query
   */
  public function update($data) : Query
  {
    $this->mode = 'update';
    $table = str_replace('FROM ', '', $this->from);
    $this->update = "UPDATE {$table} SET ";
    if (is_string($data)) {
      $this->update .= $data;
    } elseif (is_array($data)) {
      $this->update .= $this->arrayToString($data, 'update');
    }

    return $this;
  }

  /**
   * DELETE ifadesi hazırlar
   *
   * @return Query
   */
  public function delete() : Query
  {
    $this->mode = 'delete';
    $table = str_replace('FROM ', '', $this->from);
    $this->delete = "DELETE FROM {$table} ";
    return $this;
  }

  /**
   * Dizeden dizgeye çevirir
   * 
   * @param array $data
   * @param string $type
   * @return string
   */
  private function arrayToString(array $data, string $type) : string
  {
    $string = '';

    switch ($type) {
      case 'insert':
        $arrayParameters = array_values($data);
        $columnsString = implode(',', array_keys($data));
        $valuesString = implode(',', array_fill(0, count($arrayParameters), '?'));
        $string = "({$columnsString}) VALUES ({$valuesString})";
        break;

      case 'update':
        foreach ($data as $key => $value) {
          $string .= "{$key}=?,";
        }
        $string = rtrim($string, ',');
        break;
    }

    return $string;
  }

  /**
   * LIMIT ifadesi hazırlar
   *
   * @param string|int $limit
   * @return Query
   */
  public function limit($limit) : Query
  {
    $this->limit = "LIMIT {$limit}";
    return $this;
  }


  /**
   * Sorgu dizgesini üretir
   *
   * @return string
   */
  public function build() : string
  {
    $query = '';

    switch ($this->mode) {
      case 'select':
        $query = sprintf(
          '%s %s %s %s %s %s %s %s',
          $this->select,
          $this->from,
          $this->join,
          $this->where,
          $this->having,
          $this->groupBy,
          $this->orderBy,
          $this->limit
        );
        break;

      case 'insert':
        $query = $this->insert;
        break;

      case 'update':
        $query = sprintf(
          '%s %s %s %s',
          $this->update,
          $this->where,
          $this->orderBy,
          $this->limit
        );
        break;

      case 'delete':
        $query = sprintf(
          '%s %s %s %s',
          $this->delete,
          $this->where,
          $this->orderBy,
          $this->limit
        );
        break;
    }

    return trim(str_replace(['   ', '  '], ' ', $query));
  }

  /**
   * Sorgu dizgesini döndürür
   *
   * @return string
   */
  public function __toString()
  {
    return $this->build();
  }
}
