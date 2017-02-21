<?php
/**
 * Created by PhpStorm.
 * User: Jarda
 * Date: 18.02.2017
 * Time: 22:24
 */

namespace FrontModule;

use Nette;
use Nette\Application\UI\Form;
use IPub\VisualPaginator\Components as VisualPaginator;

class TagsPresenter extends BasePresenter
{

    /** @var  \App\Model\TagsManager @inject */
    public $TagsManager;

    /**
     * @var array
     */
    private $tags;

    public function renderDefault()
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }

        $this->template->tags = $this->tags;
    }

    public function actionDefault()
    {
        $this->tags = $this->TagsManager->getTags();
        bdump($this->tags->fetchAll());

    }

    public function actionEdit($postId)
    {

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }

        $tag = $this->TagsManager->getTags()->get($postId);
        if (!$tag) {
            $this->error('Štítek nebyl nalezen');
        }
        $this['tagsForm']->setDefaults($tag->toArray());
    }

    /**
     * @throws \Nette\Application\BadRequestException
     * @throws  \Nette\Application\AbortException
     * @secured
     */
    public function handleDelete($postId)
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro smazání štítku se musíte přihlásit.');
        }

        $tag = $this->TagsManager->getTags()->get($postId);

        if (!$tag) {
            $this->error('Štítek nebyl nalezen');
        }

        $this->TagsManager->deleteTag($postId);
        $this->flashMessage('Štítek byl odstraněn ze seznamu', 'success');
        $this->redirect('this');

    }

    protected function createComponentTagsForm()
    {
        $form = $this->form();
        $form->addText('name', 'Název:')
            ->setRequired("Vložte název štítku");

        $form->addSubmit('send', 'Uložit');
        $form->onSuccess[] = [$this, 'tagsFormSucceeded'];

        return $form;
    }

    public function tagsFormSucceeded($form, $values)
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro přidání nebo editaci štítku se musíte přihlásit.');
        }

        $tagId = $this->getParameter('postId');

        if ($tagId) {
            $this->TagsManager->updateTag($tagId,$values);
            $action = "přidán";

        } else {

            $this->TagsManager->insertTag($tagId, $values);
            $action = "uložen";
        }

        $this->flashMessage('Štítek byl úspěšně '.$action , 'success');
        $this->redirect('Tags:default');

    }

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