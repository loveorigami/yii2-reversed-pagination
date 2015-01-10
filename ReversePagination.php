<?php
/**
 * User: loveorigami
 * Date: 07.11.2014
 * Time: 10:17
 * Version 2.1
 */

namespace loveorigami\pagination;


class ReversePagination extends \yii\data\Pagination
{
    /**
     * @var boolean
     * use for redistribution items on page and paginator
     * default - true
     * использовать распределение
     */
    public $redistribution = true;

    /**
     * @var int
     * on how pages use redistribution items.
     * work with redistribution = true
     * на скольких страницах распределять записи
     */
    public $rePageCount = 3;

    /**
     * @var int
     * correction factor for hidding/visibility first page in paginator
     * корректирующий коэффициент для показа-скрытия первой неполной страницы
     */
    public $hidePage = 0;

    /**
     * @var int
     * correction factor for limit and offset
     * корректирующий коэффициент для смещения и лимита
     */
    public $dopOffset = 0;
    public $dopLimit = 0;


    public function init()
    {
        if ($this->redistribution && $this->totalCount) $this->getRedistribution();
    }

    /**
     * @return all params for redistribution
     */
    public function getRedistribution()
    {
        $totalCount = $this->totalCount;  // total items
        $pageSize = $this->getPageSize(); // items on page - limit
        $pageCount = $this->getPageCount(); // total pages
        $page = $this->getPage();

        // сколько на незаполненной странице
        $re['items'] = $totalCount - ($pageCount * $pageSize) + $pageSize;

        // на скольких страницах распределять записи
        if ($pageCount > $this->rePageCount) {
            $re['pages'] = $this->rePageCount;
            $on_page = intval($re['items'] / ($re['pages']));
        } else {
            $re['pages'] = $pageCount - 1;
            $on_page = intval($re['items'] / ($re['pages']+1));
        }

        // примерно, сколько доп. записей приходится на 1 страницу
        $re['on_page'] = $on_page ? $on_page : 1;

        // скрываем неполную страницу, последняя страница всегда полная
        if ($re['items'] < $pageSize) {
            $this->hidePage = 1; // скрываем в paginator-е незаполненную страницу
            $this->dopOffset = $re['items'];
        } else {
            $this->dopOffset = $re['items'] - $pageSize;
        }

        // пересчитываем и устанавливаем новые значения.
        // Если значение $page меньше единицы или отрицательно
        // переходим на первую страницу
        // А если слишком большое, то переходим на последнюю

        $startArr = $pageCount - $this->hidePage - 1; // уточняем для расчетов в массиве
        if ($this->getPage() == 0 && !isset($_GET[$this->pageParam])) {
            $page = $startArr;
        }

        $arr = []; // данные для итогового смещения
        // количество записей меньше, чем часть лимита
        if ($re['items'] < $pageSize) {
            $c = 0;
            $dop_limit = $re['on_page'];
            $dop_offset = 0;

            for ($i = 0; $i <= $re['pages'] - 1; $i++) {
                $c = $c + $re['on_page'];

                if ($c > $re['items']) {
                    $dop_limit = 0;
                }

                if ($i == $re['pages'] - 1) {
                    $c = $re['items'] - ($re['on_page'] * $i);
                    $dop_limit = $c > 0 ? $c : 0;
                }

                $arr[$startArr - $i]['dop_limit'] = $dop_limit;
                $arr[$startArr - $i]['dop_offset'] = $dop_offset;

                $dop_offset = $dop_offset + $arr[$startArr - $i]['dop_limit']; // счетчик смещения с распределением
            }
        }

        // подводим итоги
        if (isset($arr[$page]['dop_limit'])) {
            $this->dopLimit = $arr[$page]['dop_limit'];
        }
        if (isset($arr[$page]['dop_offset'])) {
            $this->dopOffset = $arr[$page]['dop_offset'];
        }
    }


    /**
     * @return integer number of pages
     */
    public function getPageCount()
    {
        $pageSize = $this->getPageSize();
        if ($pageSize < 1) {
            return $this->totalCount > 0 ? 1 : 0;
        } else {
            $totalCount = $this->totalCount < 0 ? 0 : (int)$this->totalCount;
            return (int)(($totalCount + $pageSize - 1) / $pageSize - $this->hidePage);
        }
    }

    public function getOffset()
    {
        if ($this->getPage() == 0 && !isset($_GET[$this->pageParam])) {
            $this->setPage($this->getPageCount() - 1);
        }

        if ($this->getPage() == 0
            && isset($_GET[$this->pageParam])
            && ($_GET[$this->pageParam] < 1 || $_GET[$this->pageParam] > $this->getPageCount())
        ) {
            $this->setPage($this->getPage() - 1);
        }

        return (($this->getPageCount() - $this->getPage() - 1) * $this->getPageSize()) + $this->dopOffset;
    }

    /**
     * @return integer the limit of the data. This may be used to set the
     * LIMIT value for a SQL statement for fetching the current page of data.
     * Note that if the page size is infinite, a value -1 will be returned.
     */
    public function getLimit()
    {
        $pageSize = $this->getPageSize() + $this->dopLimit;
        return $pageSize < 1 ? -1 : $pageSize;
    }

} 