<?php
namespace App\Model;

class ArticleManager extends Manager {

    public function getPublicArticles() {
        return $this->connection->table('post')
            ->select('category.*, post.*');
    }

    public function getFilmById($id) {
        return $this->connection->table('post')->get($id);
    }

    public function deleteFilm($id){
        return $this->connection->table('post')->where('id', $id)->limit(1)->delete();
    }
}