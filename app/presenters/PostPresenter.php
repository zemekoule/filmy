<?php
namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


class PostPresenter extends BasePresenter
{
    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    protected function form() {
        $form = new Form;

        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = 'div class=form-group';
        $renderer->wrappers['pair']['.error'] = 'has-error';
        $renderer->wrappers['control']['container'] = 'div class=col-sm-8';
        $renderer->wrappers['label']['container'] = 'div class="col-sm-1 control-label"';
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

    protected function createComponentCommentForm()
    {
        $form = new Form; // means Nette\Application\UI\Form

        $form->addText('name', 'Jméno:')
            ->setRequired();

        $form->addEmail('email', 'Email:');

        $form->addTextArea('content', 'Komentář:')
            ->setRequired();

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

        $category = array();
        $select = $this->database->table('category')->select('id')->select('name')->fetchAll();
        foreach ($select as $id => $row) {
            $category[$id] = $row['name'];
        }

        $form->addSelect('category', 'Kategorie:', $category)
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

    public function actionCreate($title, $rok, $csfd)
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }

        $this['postForm']->setDefaults([
            'title' => $title,
            "year" => $rok,
            'csfd_id' => $csfd
        ]);
    }
}