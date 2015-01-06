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
        $data['items']=$totalCount-($pageCount*$pageSize)+$pageSize;



        // На сколько страниц раскидывать остаток, если он есть
        if($pageCount > $this->redistributionPageCount){
            $re['pages'] = $this->redistributionPageCount;
            // насколько заполнена страница
            $part=intval($data['items']/$re['pages']);
            $re['limit'] = intval($pageSize/$re['pages']*($re['pages']-1));
        }
        else {
            $re['pages']=$pageCount-1;
            $part=intval($data['items']/($re['pages']+1));
            $re['limit'] = intval(($pageSize/($re['pages']+1))*$re['pages']);
        }

        // сколько записей приходится на каждую часть
        $re['on_page'] = $part ? $part : 1;

        // скрываем неполную страницу
        if($data['items'] < $re['limit']){
            $this->hidePage=1; // скрываем в paginator-е незаполненную страницу
            echo 'c распределением <br />';
            $this->globalOffset=$data['items'];
        }
        else{
            $this->globalOffset=$data['items']-$pageSize;
        }


        // пересчитываем и устанавливаем новые значения.
        // Если значение $cur_page меньше единицы или отрицательно
        // переходим на последнюю страницу
        // А если слишком большое, то переходим на последнюю
        $startArr=$pageCount-$this->hidePage-1; // уточняем для расчетов в массиве
        if ($this->getPage() == 0 && !isset($_GET[$this->pageParam])){
            $page=$startArr;
        }


        // количество записей меньше, чем часть лимита
        if($data['items'] < $re['limit']){
            $c=0;
            $dop_items=$re['on_page'];
            for($i=0; $i<=$re['pages']-1; $i++){
                $c=$c+$re['on_page'];
                if($c > $data['items']){
                    $dop_items=0;
                }
                if($i==$re['pages']){
                    $c=$data['items']-($re['on_page']*($i-1));
                    $dop_items = $c > 0 ? $c : 0;
                }
                $arr[$startArr-$i]['total']=$totalCount;
                $arr[$startArr-$i]['page']=$pageCount-$i;
                $arr[$startArr-$i]['dop_items']=$dop_items;
                $arr[$startArr-$i]['cur_page']=$page;
            }
            var_dump($arr);
            // итоговое смещение
            $offset=0;
            foreach ($arr as $p) {
                if($p['page']<=$page){
                    $offset=$offset+$p['dop_items'];
                }
            }

            $cur_page['title']='C растановкой';
            $cur_page['pages']=$pageCount-1;
            $cur_page['page']=$page;
            $cur_page['limit']=$pageSize+$arr[$page]['dop_items'];
            $cur_page['offset']=$offset; // начало выборки;
            $cur_page['start']=$totalCount-($pageSize*($page))-$offset; // начало выборки
        }
        else{
            $cur_page['title']='Без растановки';
            $cur_page['pages']=$pageCount;
            $cur_page['page']=$page;

            if($cur_page['page']){
                $cur_page['start']=$totalCount-($pageSize*($page)); // начало выборки
                $cur_page['limit']=$pageSize;
            }
            else{
                $cur_page['start']=0;
                $cur_page['limit']=$data['items'];
            }
        }

                //echo $data['pages'];
                echo 'всего записей - '. $totalCount.'<br />';
                echo 'всего страниц - '. $pageCount.'<br />';
                echo 'offset - '. $data['items'].'<br />';
                echo 'остаток - '. $re['limit'].'<br />';
                echo 'текущая страница - '. $page.'<br />';
                var_dump($cur_page);
               // echo $pageSize;
               // echo $pageCount;
               // echo $totalCount;
            }


            function revpage($page, $total, $limit){
                // Определяем начало сообщений для текущей страницы
             //   $page = intval($page);

                // Находим общее число страниц
              //  $data['pages'] = ceil(($total) / $limit);
                // сколько на странице
            //    $data['items']=$total-($data['pages']*$limit)+$limit;

                // На сколько страниц раскидывать остаток, если он есть
        /*        if($data['pages'] > config('redistribution')){
                    $re['pages'] = config('redistribution');
                    // насколько заполнена страница
                    $part=intval($data['items']/$re['pages']);
                    $re['limit'] = intval($limit/$re['pages']*($re['pages']-1));
                }
                else {
                    $re['pages']=$data['pages']-1;
                    $part=intval($data['items']/($re['pages']+1));
                    $re['limit'] = intval(($limit/($re['pages']+1))*$re['pages']);
                }*/


        // сколько записей приходится на каждую часть
       // $re['on_page'] = $part ? $part : 1;
//debug($re);

        // Если значение $cur_page меньше единицы или отрицательно
        // переходим на первую страницу
        // А если слишком большое, то переходим на последнюю
/*       if($page < 0) $page = 1;
        if(empty($page) or $page > $data['pages']) {
            $page = $data['pages'];
            if($data['items'] < $re['limit']) $page=$page-1;
        }

// количество записей меньше, чем часть лимита
        if($data['items'] < $re['limit'] && ($data['pages']-$page)<=$re['pages'] && $data['pages']-$page>0){
            $c=0;
            $dop_items=$re['on_page'];

            for($i=1; $i<=$re['pages']; $i++){
                $c=$c+$re['on_page'];
                if($c > $data['items']){
                    $dop_items=0;
                }
                if($i==$re['pages']){
                    $c=$data['items']-($re['on_page']*($i-1));
                    $dop_items = $c>0 ? $c : 0;
                }
                $arr[$data['pages']-$i]['total']=$total;
                $arr[$data['pages']-$i]['page']=$data['pages']-$i;
                //$arr[$data['pages']-$i]['pages']=$data['pages'];
                //$arr[$data['pages']-$i]['items']=$data['items'];
                //$arr[$data['pages']-$i]['re_pages']=$re['pages'];
                //$arr[$data['pages']-$i]['re_on_page']=$re['on_page'];
                //$arr[$data['pages']-$i]['re_limit']=$re['limit'];
                $arr[$data['pages']-$i]['dop_items']=$dop_items;
                $arr[$data['pages']-$i]['cur_page']=$page;
            }

            // итоговое смещение
            $offset=0;
            foreach ($arr as $p) {
                if($p['page']<=$page){
                    $offset=$offset+$p['dop_items'];
                }
            }

            $cur_page['title']='C растановкой';
            $cur_page['pages']=$data['pages']-1;
            $cur_page['page']=$data['pages']-$page;
            $cur_page['limit']=$limit+$arr[$page]['dop_items'];
            $cur_page['offset']=$offset; // начало выборки;

            //$cur_page['start']=$total-($limit*($page-1))+$offset; // начало выборки
            $cur_page['start']=$total-($limit*($page))-$offset; // начало выборки
            //debug($arr);
        }
        else{
            $cur_page['title']='Без растановки';
            $cur_page['pages']=$data['pages'];
            $cur_page['page']=$data['pages']-$page;

            if($cur_page['page']){
                $cur_page['start']=$total-($limit*($page)); // начало выборки
                $cur_page['limit']=$limit;
            }
            else{
                $cur_page['start']=0;
                $cur_page['limit']=$data['items'];
            }
        }
//debug($cur_page);
//echo $cur_page['page'];
        return $cur_page;*/
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
            && ($_GET[$this->pageParam] < 1                || $_GET[$this->pageParam] > $this->getPageCount())) {
            $this->setPage($this->getPage() - 1);
        }

        echo (($this->getPageCount() - $this->getPage() - 1) * $this->getPageSize()) + $this->globalOffset;
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
        $pageSize = $this->getPageSize();
        //echo $pageSize;

        return $pageSize < 1 ? -1 : $pageSize;
       // return 9;
    }


} 