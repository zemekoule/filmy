<?php
namespace FrontModule;


use Nette;
use Nette\Application\UI\Form;


class PostPresenter extends BasePresenter
{
    /** @var Nette\Database\Context */
    private $database;

    /**
     * @var
     */
    private $image;

    /**
     * @var \App\Model\CommentManager @inject
     */
    public $CommentManger;

    public function __construct(Nette\Database\Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    protected function form() {
        $form = new Form;

        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = 'div class=form-group';
        $renderer->wrappers['pair']['.error'] = 'has-error';
        $renderer->wrappers['control']['container'] = 'div class=col-sm-10';
        $renderer->wrappers['label']['container'] = 'div class="col-sm-2 control-label"';
        $renderer->wrappers['control']['description'] = 'span class=help-block';
        $renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';
// make form and controls compatible with Twitter Bootstrap
        $form->getElementPrototype()
            ->class('form-horizontal page-form')
            ->role('form');

        $form->onRender[] = function ($form) {
            foreach ($form->getControls() as $control) {
                $type = $control->getOption('type');
                if ($type === 'button') {
                    $control->getControlPrototype()->addClass('btn btn-primary');
                    $usedPrimary = TRUE;
                } elseif (in_array($type, ['text', 'textarea', 'select'], TRUE)) {
                    $control->getControlPrototype()->addClass('form-control');
                } elseif (in_array($type, ['checkbox', 'radio'], TRUE)) {
                    $control->getSeparatorPrototype()->setName('div')->addClass($type);
                }
            }
        };
        return $form;
    }

    public function renderShow($postId)
    {

        $post = $this->database->table('posts')->get($postId);
        if (!$post) {
            $this->error('Stránka nebyla nalezena');
        }

        $this->template->post = $post;
        $this->template->comments = $post->related('comment')->order('created_at');

    }

    public function renderCreate()
    {
        $this->template->image = $this->image;
    }

    protected function createComponentCommentForm()
    {
        //$form = new Form; // means Nette\Application\UI\Form
        $form = $this->form();
        $form->addText('name', 'Jméno:')
            ->setRequired('Zadejte jméno nebo přezdívku');

        $form->addEmail('email', 'Email:')
            ->setRequired('Zadejte email');

        $form->addTextArea('content', 'Komentář:')
            ->setRequired('Zadejte text komentáře');

        $form->addSubmit('send', 'Publikovat komentář');

        $form->onSuccess[] = [$this, 'commentFormSucceeded'];

        return $form;
    }

    public function commentFormSucceeded($form, $values)
    {
        $postId = $this->getParameter('postId');

        $this->database->table('comments')->insert([
            'post_id' => $postId,
            'name' => $values->name,
            'email' => $values->email,
            'content' => $values->content,
        ]);

        $this->flashMessage('Děkuji za komentář', 'success');
        $this->redirect('this');
    }

    protected function createComponentPostForm()
    {
        //$form = new Form;
        $form = $this->form();
        $form->addText('title', 'Titulek:')
            ->setRequired("Vložte titulek stránky");

        $select = $this->database->table('category')->select('id')->select('name')->fetchPairs('id', 'name');

        $form->addSelect('category', 'Kategorie:', $select)
            ->setPrompt('Zvolte kategorii')
            ->setRequired("Zadejte kategorii");
        $form->addText('year','Rok')
            ->setRequired('Rok výroby je potřeba vyplnit');
        $form->addText('csfd_id','Id ČSFD');
        $form->addTextArea('content', 'Obsah:',NULL, 10);

        $form->addSubmit('send', 'Uložit a publikovat');
        $form->onSuccess[] = [$this, 'postFormSucceeded'];

        return $form;
    }

    public function postFormSucceeded($form, $values)
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }

        $postId = $this->getParameter('postId');

        if ($postId) {
            $post = $this->database->table('posts')->get($postId);
            $post->update($values);
        } else {
            $post = $this->database->table('posts')->insert($values);
        }

        $this->flashMessage('Film: '.$post->title.' ('.$post->year.') byl úspěšně uložen.', 'success');
        $this->redirect('Homepage:default');

    }


    public function actionEdit($postId)
    {

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }

        $post = $this->database->table('posts')->get($postId);
        if (!$post) {
            $this->error('Příspěvek nebyl nalezen');
        }
        $this['postForm']->setDefaults($post->toArray());
    }

    public function actionCreate($title, $rok, $csfd= null)
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }

        if ($csfd) {

            $tmp = file_get_contents('http://api.spuntanela.cz/csfd/index.php?id='.$csfd);
            $xml = simplexml_load_string($tmp);

            if (isset($xml)) {
                $this->image = $xml->obrazek;
                $this['postForm']->setDefaults([
                    'title' => $xml->nazev,
                    "year" => $xml->rok,
                    'csfd_id' => $xml->id,
                    'content' => $xml->obsah
                ]);
            }

        }

    }

    public function handleCommentDelete($id)
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }

        $this->CommentManger->CommentDelete($id);
        $this->flashMessage('Komentář byl odstraněn', 'success');
    }

    public function handlePostFormInsertContent()
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }

        $this['postForm']->setDefaults([
            'content' => 'Nový obsah filmu'
        ]);
    }
}