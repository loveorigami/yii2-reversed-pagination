<?php
/**
 * Created by PhpStorm.
 * User: loveorigami
 * Date: 07.11.2014
 * Time: 10:17
 */

namespace loveorigami\pagination;


class ReversePagination extends \yii\data\Pagination {

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
    public $redistributionPageCount = 3;

    /**
     * @var int
     * correction factor for hidding/visibility first page in paginator
     * корректирующий коэффициент для показа-скрытия первой неполной страницы
     */
    public $hidePage = 0;
    public $globalOffset = 0;
    public $globalLimit = 0;



    public function init()
    {
       if($this->redistribution && $this->totalCount) $this->getRedistribution();
    }

    /**
     * @return all params for redistribution
     */
    public function getRedistribution(){
        $totalCount = $this->totalCount;  // total items
        $pageSize = $this->getPageSize(); // items on page - limit
        $pageCount = $this->getPageCount(); // total pages
        $page=$this->getPage();

        // сколько на незаполненной странице
        $re['items']=$totalCount-($pageCount*$pageSize)+$pageSize;

        // На сколько страниц раскидывать остаток, если он есть
        if($pageCount > $this->redistributionPageCount){
            $re['pages'] = $this->redistributionPageCount;
            // насколько заполнена страница
            $part=intval($re['items']/$re['pages']);
            $re['limit'] = intval($pageSize/$re['pages']*($re['pages']-1));
        }
        else {
            $re['pages']=$pageCount-1;
            $part=intval($re['items']/($re['pages']+1));
            $re['limit'] = intval(($pageSize/($re['pages']+1))*$re['pages']);
        }
        // сколько записей приходится на каждую часть
        $re['on_page'] = $part ? $part : 1;

var_dump($re);
// поправить скрипт для случая, когда страница 1, а остаточных записей больше 1
// например всего 21, на странице 18. остатоок 3





        // скрываем неполную страницу,
        if($re['items'] < $re['limit']){
            $this->hidePage=1; // скрываем в paginator-е незаполненную страницу
            $this->globalOffset=$re['items'];
        }
        else{
            $this->globalOffset=$re['items']-$pageSize;
        }


        // пересчитываем и устанавливаем новые значения.
        // Если значение $cur_page меньше единицы или отрицательно
        // переходим на последнюю страницу
        // А если слишком большое, то переходим на последнюю

        $startArr=$pageCount-$this->hidePage-1; // уточняем для расчетов в массиве
        if ($this->getPage() == 0 && !isset($_GET[$this->pageParam])){
            $page=$startArr;
        }

        $arr=[]; // данные для итогового смещения
        // количество записей меньше, чем часть лимита
        if($re['items'] < $re['limit']){
            $c=0;
            $dop_items=$re['on_page'];
            $offset=0;

            for($i=0; $i<=$re['pages']-1; $i++){
                $c=$c+$re['on_page'];

                if($c > $re['items']){
                    $dop_items=0;
                }

                if($i==$re['pages']){
                    $c=$re['items']-($re['on_page']*($i-1));
                    $dop_items = $c > 0 ? $c : 0;
                }

                $arr[$startArr-$i]['total']=$totalCount;
                $arr[$startArr-$i]['dop_items']=$dop_items;
                $arr[$startArr-$i]['cur_page']=$page;
                $arr[$startArr-$i]['offset']=$offset;
                $offset=$offset+$arr[$startArr-$i]['dop_items'];
            }
            var_dump($arr);

            $cur_page['title']='C растановкой';
            $cur_page['pages']=$pageCount-1;
            $cur_page['page']=$page;
            $cur_page['limit']=$pageSize+$arr[$page]['dop_items'];
            $cur_page['offset']=$offset; // начало выборки;
            $cur_page['start']=$totalCount-($pageSize*($page))-$offset; // начало выборки
        }


        if(isset($arr[$page]['dop_items'])) {
            $this->globalLimit=$arr[$page]['dop_items'];
           // echo $this->globalLimit;
            // $this->globalLimit=$re['items']; // для случая, когда мало страниц что то придумать надо
        }
        if(isset($arr[$page]['offset'])) {
            $this->globalOffset=$arr[$page]['offset'];
            // $this->globalLimit=$re['items']; // для случая, когда мало страниц что то придумать надо
        }
       // $this->globalOffset=1;

                //echo $data['pages'];
        echo 'всего записей - '. $totalCount.'<br />';
               // echo 'всего страниц - '. $pageCount.'<br />';
            // echo 'offset - '. $offset.'<br />';
              //  echo 'остаток - '. $re['limit'].'<br />';
        echo 'текущая страница - '. $page.'<br />';
        echo 'limit - '. $this->GetLimit().'<br />';
              //  var_dump($cur_page);
               // echo $pageSize;
               // echo $pageCount;
               // echo $totalCount;
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
            $totalCount = $this->totalCount < 0 ? 0 : (int) $this->totalCount;

            return (int) (($totalCount + $pageSize - 1) / $pageSize - $this->hidePage);
        }
    }

    public function getOffset() {

       //echo $this->getPage(); // текущая страница

       if ($this->getPage() == 0 && !isset($_GET[$this->pageParam])) {
           $this->setPage($this->getPageCount() - 1);
        }

        if ($this->getPage() == 0
            && isset($_GET[$this->pageParam])
            && ($_GET[$this->pageParam] < 1 || $_GET[$this->pageParam] > $this->getPageCount())) {
            $this->setPage($this->getPage() - 1);
        }

       // echo (($this->getPageCount() - $this->getPage() - 1) * $this->getPageSize()) + $this->globalOffset;
        return (($this->getPageCount() - $this->getPage() - 1) * $this->getPageSize()) + $this->globalOffset;

       // $pageSize = $this->getPageSize();
       // return $pageSize < 1 ? 0 : $this->getPage() * $pageSize;
       // найти остаток и распределение - этот остаток и будет смещением для всех
       // 8 статей
       // 3 страницы
       // по три на каждой
       // offset = 5 = 8 - 3 * 1(page) количество на странице)
       // offset = 2 = 8 - 3 * 2(page) количество на странице)
       //
    }

    /**
     * @return integer the limit of the data. This may be used to set the
     * LIMIT value for a SQL statement for fetching the current page of data.
     * Note that if the page size is infinite, a value -1 will be returned.
     */
    public function getLimit()
    {
        $pageSize = $this->getPageSize() + $this->globalLimit;
        //echo $pageSize;

        return $pageSize < 1 ? -1 : $pageSize;
       // return 9;
    }


} 