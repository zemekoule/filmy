<?php

namespace App\Presenters;

//use Nette;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;


abstract class BasePresenter extends Presenter
{

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

}