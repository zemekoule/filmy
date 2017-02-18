<?php
/**
 * Created by PhpStorm.
 * User: Jarda
 * Date: 14.01.2017
 * Time: 9:50
 */

namespace App\Model;

class ArticleManager extends Manager
{

    public function getPublicArticles()
    {

        return $this->connection->table('posts')
            ->select('category.*, posts.*');
    }

    public function getFilmById($id)
    {
        return $this->connection->table('posts')->get($id);
    }

    public function deleteFilm($id)
    {
        return $this->connection->table('posts')->where('id', $id)->limit(1)->delete();
    }


}