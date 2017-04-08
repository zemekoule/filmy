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

        return $this->connection->table('post')
            ->select('category.*, post.*');
    }

    public function getFilmById($id)
    {
        return $this->connection->table('post')->get($id);
    }

    public function deleteFilm($id)
    {
        return $this->connection->table('post')->where('id', $id)->limit(1)->delete();
    }


}