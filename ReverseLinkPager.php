<?php
namespace loveorigami\pagination;

use yii\widgets\LinkPager;
use yii\helpers\Html;

/**
 * Created by PhpStorm.
 * User: loveorigami
 * Date: 07.11.2014
 * Time: 9:53
 */
class ReverseLinkPager extends LinkPager
{
    public $nextPageLabel = '&laquo;';
    public $prevPageLabel = '&raquo;';


    protected function renderPageButtons()
    {
        if (($pageCount = $this->pagination->getPageCount()) <= 1)
            return '';

        list($beginPage, $endPage) = $this->getPageRange();


        // currentPage is calculated in getPageRange()
        $currentPage = $this->pagination->getPage(false);

        $buttons = array();

        // first page
        if ($this->firstPageLabel !== false) {
            $buttons[] = $this->renderPageButton(
                $this->firstPageLabel,
                $pageCount - 1,
                $this->firstPageCssClass,
                $currentPage >= $pageCount - 1,
                false
            );
        }

        // next page
        if (($page = $currentPage + 1) >= $pageCount - 1)
            $page = $pageCount - 1;
        $buttons[] = $this->renderPageButton(
            $this->nextPageLabel,
            $page,
            $this->nextPageCssClass,
            $currentPage >= $pageCount - 1, false
        );

        // internal pages
        for ($i = $endPage; $i >= $beginPage; --$i) {
            $buttons[] = $this->renderPageButton(
                $i + 1,
                $i,
                null,
                false,
                $i == $currentPage
            );
        }

        // prev page
        if (($page = $currentPage - 1) < 0)
            $page = 0;

        $buttons[] = $this->renderPageButton(
            $this->prevPageLabel,
            $page,
            $this->prevPageCssClass,
            $currentPage <= 0,
            false
        );

        // last page
        if ($this->lastPageLabel !== false) {
            $buttons[] = $this->renderPageButton(
                $this->lastPageLabel,
                0,
                $this->lastPageCssClass,
                $currentPage <= 0,
                false
            );
        }

        return Html::tag('ul', implode("\n", $buttons), $this->options);
    }

    /**
     * @return array the begin and end pages that need to be displayed.
     */


}