<?php

namespace Demir\Database;

class Pagination
{
  /**
   * Toplam öğe sayısı
   * 
   * @var integer
   */
  protected $totalItems;

  /**
   * Toplam sayfa sayısı
   * 
   * @var integer
   */
  protected $totalPages;

  /**
   * Sayfa başına öğe sayısı
   * 
   * @var integer
   */
  protected $perPage;

  /**
   * Mevcut sayfa
   * 
   * @var integer
   */
  protected $currentPage;

  /**
   * Bağlantı kalıbı
   * 
   * @var string
   */
  protected $pattern;

  /**
   * En fazla gösterilecek sayfa sayısı
   * 
   * @var integer
   */
  protected $maxPages = 7;

  /**
   * Sınıf örneği tutar
   * 
   * @static Pagination
   */
  protected static $instance;

  /**
   * Sınıf başlatıcı
   * 
   * @param $totalItems Toplam öğe sayısı
   * @param $perPage Sayfa başına öğe sayısı
   * @param $currentPage Geçerli sayfa numarası
   * @param $pattern Bağlantı kalıbı
   */
  public function __construct(
    int $totalItems, 
    int $perPage = 10, 
    int $currentPage = 1, 
    string $pattern = '?page={number}'
  )
  {
    $this->totalItems = $totalItems;
    $this->perPage = $perPage;
    $this->currentPage = $currentPage;
    $this->pattern = $pattern;
    $this->updateTotalPages();
    static::$instance = $this;
  }

  /**
   * Sınıf örneği döndürür
   * 
   * @throws \Exception
   * @return Pagination
   */
  public static function getInstance() : Pagination
  {
    if (is_null(static::$instance)) {
      throw new \Exception('Sayfalama verileri girilmemiş ve sınıf başlatılmamış');
    }

    return static::$instance;
  }

  /**
   * Toplam sayfa sayısını hesaplar
   * 
   * @return void
   */
  protected function updateTotalPages() : void
  {
    $this->totalPages =
      ($this->perPage == 0 ? 0 : (int) ceil($this->totalItems / $this->perPage));
  }

  /**
   * Gösterilecek en fazla sayfa sayısını belirler
   * 
   * @param int $maxPages
   * @return Pagination
   */
  public function setMaxPages(int $maxPages) : Pagination
  {
    if ($maxPages > 3) {
      $this->maxPages = $maxPages;
    }
  }

  /**
   * Geçerli sayfa numarasını belirler
   * 
   * @param int $currentPage
   * @return Pagination
   */
  public function setCurrentPage(int $currentPage) : Pagination
  {
    $this->currentPage = $currentPage;
    return $this;
  }

  /**
   * Sayfa başına gönderi sayısı
   * 
   * @param int $perPage
   * @return Pagination
   */
  public function setPerPage(int $perPage) : Pagination
  {
    $this->perPage = $perPage;
    $this->updateTotalPages();
    return $this;
  }

  /**
   * Toplam öğe sayısını belirler
   * 
   * @param int $totalItems
   * @return Pagination
   */
  public function setTotalItems(int $totalItems) : Pagination
  {
    $this->totalItems = $totalItems;
    $this->updateTotalPages();
    return $this;
  }

  /**
   * Bağlantı kalıbı belirler
   * 
   * @param string $pattern
   * @return Pagination
   */
  public function setPattern(string $pattern) : Pagination
  {
    $this->pattern = $pattern;
    return $this;
  }

  /**
   * Sayfa numarasına göre URL kalıbı döndürür
   * 
   * @param int $pageNum
   * @return string
   */
  public function getPageUrl(int $pageNumber) : string
  {
    return str_replace('{number}', $pageNumber, $this->pattern);
  }

  /**
   * Sonraki sayfa numarasını döndürür
   * 
   * @return mixed
   */
  public function getNextPage()
  {
    if ($this->currentPage < $this->totalPages) {
      return $this->currentPage + 1;
    }

    return false;
  }

  /**
   * Bir önceki sayfa numarasını döndürür
   * 
   * @return mixed
   */
  public function getPrevPage()
  {
    if ($this->currentPage > 1) {
      return $this->currentPage - 1;
    }

    return false;
  }

  /**
   * Bir sonraki sayfa URL'sini döndürür
   * 
   * @return string|bool
   */
  public function getNextUrl()
  {
    if (!$this->getNextPage()) {
      return false;
    }

    return $this->getPageUrl($this->getNextPage());
  }

  /**
   * Bir önceki sayfa URL'sini döndürür
   * 
   * @return string|bool
   */
  public function getPrevUrl()
  {
    if (!$this->getPrevPage()) {
      return false;
    }

    return $this->getPageUrl($this->getPrevPage());
  }

  /**
   * Sayfaları oluşturur
   * 
   * @return array
   */
  public function getPages() : array
  {

    $pages = [];

    if ($this->totalPages <= 1) {
      return [];
    }

    if ($this->totalPages <= $this->maxPages) {
      for ($i = 1; $i <= $this->totalPages; $i++) {
        $pages[] = $this->createPage($i, $i === $this->currentPage);
      }
    } else {
      $numAdjacents = (int) floor(($this->maxPages - 3) / 2);

      if ($this->currentPage + $numAdjacents > $this->totalPages) {
        $slidingStart = $this->totalPages - $this->maxPages + 2;
      } else {
        $slidingStart = $this->currentPage - $numAdjacents;
      }

      if ($slidingStart < 2) {
        $slidingStart = 2;
      }

      $slidingEnd = $slidingStart + $this->maxPages - 3;

      if ($slidingEnd >= $this->totalPages) {
        $slidingEnd = $this->totalPages - 1;
      }

      $pages[] = $this->createPage(1, $this->currentPage === 1);

      if ($slidingStart > 2) {
        $pages[] = $this->createPageEllipsis();
      }

      for ($i = $slidingStart; $i <= $slidingEnd; $i++) {
        $pages[] = $this->createPage($i, $i === $this->currentPage);
      }

      if ($slidingEnd < $this->totalPages - 1) {
        $pages[] = $this->createPageEllipsis();
      }

      $pages[] = $this->createPage(
        $this->totalPages, 
        $this->currentPage == $this->totalPages
      );
    }

    return $pages;
  }

  /**
   * Sayfa oluşturur
   * 
   * @param int $pageNumber
   * @param bool $current
   * @return array
   */
  protected function createPage(int $pageNumber, bool $current = false) : array
  {
    return [
      'number' => $pageNumber,
      'url' => $this->getPageUrl($pageNumber),
      'isCurrent' => $current
    ];
  }

  /**
   * ... oluşturur
   * 
   * @return array
   */
  protected function createPageEllipsis() : array
  {
    return [
      'number' => '...',
      'url' => null,
      'isCurrent' => false,
    ];
  }

  /**
   * SQL Sorgusu için LIMIT döndürür
   * 1,10 gibi
   * 
   * @return string
   */
  public function getLimit() : string
  {
    $limit = ($this->currentPage * $this->perPage) - $this->perPage;
    return $limit . ',' . $this->perPage;
  }
}