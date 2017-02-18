<?php

namespace FrontModule;

use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;

/**
 * Class BasePresenter
 * @package FrontModule
 *
 */
abstract class BasePresenter extends Presenter
{
    use \Nextras\Application\UI\SecuredLinksPresenterTrait;

    protected function createComponentSearchMoviesForm()
    {
        $form = new Form;
        $form->addText('movie', 'Název filmu:')
            ->setRequired('Musíte vyplnit název filmu.');

        $form->addSubmit('findMovie', 'Odeslat');

        $form->onSuccess[] = [$this, 'searchMoviesFormSucceeded'];
        return $form;
    }

    public function searchMoviesFormSucceeded($form, $values)
    {
        $this->flashMessage('Vysledek vyhledávání: '. $values->movie);
        $this->redirect('Homepage:default',array("find" => $values->movie, "page" => 1));
        //$this->redirect('Homepage:');
    }

    protected function createComponentPagination()
    {
        return new \PaginationControl();

    }

}

