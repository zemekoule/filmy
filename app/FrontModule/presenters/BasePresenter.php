<?php

namespace FrontModule;

use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;

/**
 * Base presenter
 * Class BasePresenter
 * @package FrontModule
 *
 */
abstract class BasePresenter extends Presenter
{
    use \Nextras\Application\UI\SecuredLinksPresenterTrait;

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

    /*
    protected function createComponentPagination()
    {
        return new \PaginationControl();

    }
    */

}

