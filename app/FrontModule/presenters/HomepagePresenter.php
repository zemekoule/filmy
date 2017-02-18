<?php
/*
 * Hlavní stránka projektu , zobrazuje tabulku se seznamem filmů
 * */

namespace FrontModule;

use Nette;
use App\Model\ArticleManager;
use App\Model\CategoryManager;
use IPub\VisualPaginator\Components as VisualPaginator;

class HomepagePresenter extends BasePresenter
{
    /** @var Nette\Database\Context */

    /**
     * @var ArticleManager
     */
    private $articleManager;

    /**
     * @var CategoryManager
     */
    private $categoryManager;



    public function __construct(ArticleManager $articleManager, CategoryManager $categoryManager)
    {
        parent::__construct();
        $this->articleManager = $articleManager;
        $this->categoryManager = $categoryManager;
    }

    public function renderDefault($page = 1,$find = null, $sort= null)
    {

        $paginator = new Nette\Utils\Paginator;

        $paginator->setItemsPerPage(30); // počet položek na stránce
        $paginator->setPage($page); // číslo aktuální stránky, číslováno od 1

        $posts = $this->template->posts = $this->articleManager->getPublicArticles();
        //$posts->limit($paginator->getLength(), $paginator->getOffset());

        if (isset($find)) {
            $posts->where('title LIKE ?', '%' . $find . '%');
        }

        if ($sort === 'date') {
            $posts->order('created_at DESC');
        }
        else {
            $posts->order('title ASC');
        }


        // přístup ke komponentě VisualPaginatoru:
        $visualPaginator = $this['visualPaginator'];
        // přístup k samotnému paginatoru:
        $paginator = $visualPaginator->getPaginator();
        // počet položek na stránku
        $paginator->itemsPerPage = 30;
        // spočítat celkový počet položek
        $paginator->itemCount = $posts->count('*');

        // přidat stránkovač k dotazu
        $posts->limit($paginator->itemsPerPage, $paginator->offset);

        // poslat do šablony:
        //$this->template->polozky = $polozky;


        $this->template->countPost = $this->articleManager->getPublicArticles()->where('title LIKE ?', '%'.$find.'%')->count();
        //$this->template->countPost = $this->database->table('posts')->where('title LIKE ?', '%'.$find.'%')->count();
        $paginator->setItemCount($this->template->countPost); // celkový počet položek (např. článků)

        $this->template->pageTotal = $paginator->getPageCount();
        $this->template->posts = $posts;
        $this->template->page = $paginator->page;
        $this->template->find = $find;

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

    /**
     * @throws \Nette\Application\BadRequestException
     * @throws  \Nette\Application\AbortException
     * @secured
     */
    public function handleDelete($id)
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro smazání filmu se musíte přihlásit.');
        }

        $film = $this->articleManager->getFilmById($id);

        if (!$film) {
            $this->error('Film nebyl nalezen');
        }

        $this->articleManager->deleteFilm($id);
        $this->flashMessage('Film byl odstraněn ze seznamu', 'success');
        $this->redirect('this');

    }

    // továrna Visual paginátoru, která vrátí stránkovač
    protected function createComponentVisualPaginator() {
        // Init visual paginator
        $control = new VisualPaginator\Control;
        // použít šablonu pro bootstrap
        $control->setTemplateFile('paginator.latte');
        // vypnout Ajax, s tím si budeme hrát až bude čas
        $control->disableAjax();

        return $control; // <-- v šabloně dáme jen {control visualPaginator}
    }
}
