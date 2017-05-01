<?php
/**
 * Created by PhpStorm.
 * User: Jarda
 * Date: 14.01.2017
 * Time: 11:51
 */

namespace App\Model;

class CategoryManager extends Manager {
    public function getCategories() {
        return $this->connection->table('category')
            ->order('id')
            ->order('name');
    }
}