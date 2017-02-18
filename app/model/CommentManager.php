<?php
/**
 * Created by PhpStorm.
 * User: Jarda
 * Date: 12.02.2017
 * Time: 11:35
 */

namespace App\Model;

class CommentManager extends Manager
{
    public function CommentDelete($id)
    {
        return $this->connection->table('comments')
            ->where('id',$id)
            ->limit(1)
            ->delete();
    }
}