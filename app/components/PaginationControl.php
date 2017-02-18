<?php

/**
 * Created by PhpStorm.
 * User: Jarda
 * Date: 14.01.2017
 * Time: 12:51
 */

use Nette\Application\UI\Control;

class PaginationControl extends Control
{
    public function render($page, /* $pageFrom, $pageTo, */ $pageTotal, $find)
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/Pagination.latte');
        // vložíme do šablony nějaké parametry
        $template->page = $page;
        //$template->pageFrom = $pageFrom;
        //$template->pageTo = $pageTo;
        $template->pageTotal = $pageTotal;
        $template->find = $find;

        if ($page >= 3) {
            $this->template->pageTo = ($page + 2 > $pageTotal ? $pageTotal:$page + 2 );
            $this->template->pageFrom = ($this->template->pageTo -4 < 1 ? 1 : $this->template->pageTo -4) ;

        }
        else {
            $this->template->pageFrom = 1;
            $this->template->pageTo = ($page + (5-$page) > $pageTotal ? $pageTotal:$page + (5-$page) );
        }

        // a vykreslíme ji
        $template->render();
    }

    /*
    public function handleSignal($page, $pageFrom, $pageTo, $pageTotal, $find)
    {
        $this->link('edit', 10);

        //$this->template->page = $this->getParameter('page');

    }
    */
}