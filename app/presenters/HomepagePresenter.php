<?php
/*
 * Hlavní stránka projektu , zobrazuje tabulku se seznamem filmů
 * */

namespace App\Presenters;

use Nette;
use App\Model\ArticleManager;
use App\Model\CategoryManager;


class HomepagePresenter extends BasePresenter
{
    /** @var Nette\Database\Context */
    //private $database;
    private $articleManager;
    private $categoryManager;


    public function __construct(ArticleManager $articleManager, CategoryManager $categoryManager)
    {
        $this->articleManager = $articleManager;
        $this->categoryManager = $categoryManager;
    }

    public function renderDefault($page = 1, $itens = 0, $find = null)
    {
        $paginator = new Nette\Utils\Paginator;

        $paginator->setItemsPerPage(30); // počet položek na stránce
        $paginator->setPage($page); // číslo aktuální stránky, číslováno od 1

        $posts = $this->template->posts = $this->articleManager->getPublicArticles();
        $posts->limit($paginator->getLength(), $paginator->getOffset());

        if (isset($find)) {
            $posts->where('title LIKE ?', '%' . $find . '%');
        }

        $this->template->countPost = $this->articleManager->getPublicArticles()->where('title LIKE ?', '%'.$find.'%')->count();
        //$this->template->countPost = $this->database->table('posts')->where('title LIKE ?', '%'.$find.'%')->count();
        $paginator->setItemCount($this->template->countPost); // celkový počet položek (např. článků)

        $this->template->totalPosts = $paginator->getPageCount();
        $this->template->posts = $posts;
        $this->template->page = $paginator->page;
        $this->template->find = $find;

        if ($page >= 3) {
            $this->template->pageTo = ($page + 2 > $paginator->getPageCount() ? $paginator->getPageCount():$page + 2 );
            $this->template->pageFrom = ($this->template->pageTo -4 < 1 ? 1 : $this->template->pageTo -4) ;

        }
        else {
            $this->template->pageFrom = 1;
            $this->template->pageTo = ($page + (5-$page) > $paginator->getPageCount() ? $paginator->getPageCount():$page + (5-$page) );
        }

        $this->template->category = $this->categoryManager->getCategories();

    }

    protected function form() {
        $form = new \Nette\Application\UI\Form;

        $form->setTranslator($this->getTranslator());

        $form->setRenderer(new \Nette\Forms\Rendering\BootstrapFormRenderer);

        $form->getElementPrototype()
            ->class('form-horizontal page-form')
            ->role('form');

        return $form;
    }
}
