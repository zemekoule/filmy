<?php
namespace FrontModule;

use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;


class CsfdPresenter extends BasePresenter
{
    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    /**
     * Vyhledá na serveru ČSFD filmy podle zadaného řetězce
     * @param $movie
     *
     */
    public function renderFind($movie)
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }

        $movie_find = Strings::toAscii($movie);
        $tmp = file_get_contents('http://csfd.matousskala.cz/api/hledat.php?q='.$movie_find);
        $xml = simplexml_load_string($tmp);
        $this->template->movie = $movie;

        if (isset($xml->filmy->film)) {
            $this->template->movies = $xml->filmy->film;
        }
        else $this->template->movies = array();

        /*
        $movies = array();
        if (isset($xml->filmy->film)) {
            $c = 0;
            foreach ($xml->filmy->film as $item) {

                $movies[$c]['id'] = (string) $item->id;
                $movies[$c]['nazev'] = (string) $item->nazev;
                $movies[$c]['rok'] = (string) $item->rok;
                //dump($movies);
                $c++;
            }
            $this->template->movies = $movies;
        }
        */


        /*
        $post = $this->database->table('posts')->get($postId);
        if (!$post) {
            $this->error('Stránka nebyla nalezena');
        }

        $this->template->post = $post;
        $this->template->comments = $post->related('comment')->order('created_at');
        */
    }

    /*
    protected function createComponentCommentForm()
    {
        $form = new Form; // means Nette\Application\UI\Form

        $form->addText('movie_title', 'Název filmu:')
            ->setRequired("Zadejte název filmu, který hledáte");

        $form->addSubmit('send', 'Vyhledat film');

        $form->onSuccess[] = [$this, 'commentFormSucceeded'];

        return $form;
    }
    */

    protected function createComponentCsfdFindForm()
    {
        //$form = new Form;
        $form = $this->form();
        $form->addText('movie_title', 'Název filmu:')
            ->setRequired("Zadejte název filmu, který hledáte");

        $form->addSubmit('send', 'Vyhledat film');

        $form->onSuccess[] = [$this, 'postFormSucceeded'];

        return $form;
    }

    public function postFormSucceeded($form, $values)
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro vyhledávání filmu na ČSFD se musíte přihlásit.');
        }
        /*
        $postId = $this->getParameter('postId');

        if ($postId) {
            $post = $this->database->table('posts')->get($postId);
            $post->update($values);
        } else {
            $post = $this->database->table('posts')->insert($values);
        }

        $this->flashMessage('Příspěvek byl úspěšně publikován.', 'success');
        $this->redirect('show', $post->id);
        */

        //$tmp = file_get_contents('http://csfd.matousskala.cz/api/hledat.php?q='.$values->movie_title);
        //$csfd = file_get_contents("http://www.csfd.cz/film/257218");
        //$dom = new domDocument;
        //$html = loadHTML($csfd);
        //echo $html->getElementsByTagName('title');
        //$xml = simplexml_load_string($tmp);
        //dump($xml->filmy);
        /*
        foreach ($xml->filmy->film as $item) {
            dump($item);
        }
        */
        $this->flashMessage('Našel jsem následující filmy  k hledání: '.$values->movie_title, 'success');
        $this->redirect('this',$values->movie_title);
    }


    public function actionEdit($postId)
    {

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }

        //$post = $this->database->table('posts')->get($postId);
        if (!$postId) {
            $this->error('Příspěvek nebyl nalezen');
        }
        //$this['postForm']->setDefaults($post->toArray());
    }


    public function actionCreate()
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }
}