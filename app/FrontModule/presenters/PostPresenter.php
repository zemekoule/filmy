<?php
namespace FrontModule;


use Nette;

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

    /**
     * @var \App\Model\TagsManager @inject
     */
    public $TagsManager;

    public function __construct(Nette\Database\Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    public function renderShow($postId)
    {

        $post = $this->database->table('post')->get($postId);
        if (!$post) {
            $this->error('Stránka nebyla nalezena');
        }
		bdump($post);
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


        $gengre = $this->TagsManager->getTags()->fetchPairs('id','name');

        $form->addCheckboxList('gengre', 'Žánr:', $gengre)
            ->setRequired('Zadejte alespoň jeden žánr.');

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
        //bdump($values->gengre);
        $postId = $this->getParameter('postId');
        //$id = $this-> getQuery('id'); // vrací GET parametr 'id' (nebo NULL)

        if ($postId) {
            $post = $this->database->table('post')->get($postId);
            $post->update([
                'title' => $values->title,
                'category' => $values->category,
                'year' => $values->year,
                'csfd_id' => $values->csfd_id,
                'content' => $values->content
            ]);

            // aktualizace štítků
            $tags = $this->TagsManager->getTagsPost($postId)->fetchPairs('id','tag_id');
            // přidání štítků
            $tagsAdd = array_diff($values->gengre, $tags);
            bdump($tagsAdd, 'štítky pro přidání');
            foreach ($tagsAdd as $item) {
                $this->TagsManager->insertPostTags(['post_id' => $postId, 'tag_id' => $item]);
            }
            $tagsDelete = array_diff($tags, $values->gengre);
            foreach ($tagsDelete as $key=>$item) {
               $this->TagsManager->deleteTagPostById($key);
            }
            bdump($tagsDelete, 'štítky pro smazání');


        } else {
            $post = $this->database->table('post')->insert([
                'title' => $values->title,
                'category' => $values->category,
                'year' => $values->year,
                'csfd_id' => $values->csfd_id,
                'content' => $values->content
            ]);

            $lastId = $post->getPrimary();
            foreach ($values->gengre as $item) {
                $this->TagsManager->insertPostTags(['post_id' => $lastId, 'tag_id' => $item]);
            }


        }

        $this->flashMessage('Film: '.$post->title.' ('.$post->year.') byl úspěšně uložen.', 'success');
        $this->redirect('Homepage:default');

    }


    public function actionEdit($postId)
    {

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }

        $post = $this->database->table('post')->get($postId);

        if (!$post) {
            $this->error('Příspěvek nebyl nalezen');
        }

        $tags = $this->TagsManager->getTagsPost($postId)->fetchPairs('id','tag_id');

        $this['postForm']->setDefaults([
            'title' => $post->title,
            'category' => $post->category,
            'year' => $post->year,
            'csfd_id' => $post->csfd_id,
            'content' => $post->content,
            'gengre' => $tags
        ]);
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
                $zanryItems = [];
                $this->image = $xml->obrazek;
                if (count($xml->zanry->zanr) > 0) {

                    foreach ($xml->zanry->zanr as $item) {
                        $gengreImport = $this->TagsManager->getTagIdByName(trim($item))->fetchField();
                        if ($gengreImport) {
                            $zanryItems[] = $gengreImport;
                        }
                    }
                    bdump($zanryItems,'seznam žánrů');
                }
                bdump(count($xml->zanry->zanr),'žánry');
                $this['postForm']->setDefaults([
                    'title' => $xml->nazev,
                    "year" => $xml->rok,
                    'csfd_id' => $xml->id,
                    'content' => $xml->obsah,
                    'gengre' => $zanryItems
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