<?php
/**
 * Created by PhpStorm.
 * User: Jarda
 * Date: 19.02.2017
 * Time: 1:17
 */

namespace App\Model;


class TagsManager extends Manager
{
    public function getTags()
    {
        return $this->connection->table('tags');
    }


    public function getTagsPost($postId)
    {
        return $this->connection->table('posts_tags')
            ->where('post_id', $postId);
    }

    public function deleteTag($id)
    {
        return $this->connection->table('tags')
            ->where('id', $id)
            ->limit(1)
            ->delete();
    }

    public function updateTag($id, $values)
    {
        return $this->getTags()->where('id', $id)->limit(1)->update($values);
    }

    public function insertTag($id, $values)
    {
        return $this->getTags()->where('id', $id)->limit(1)->insert($values);
    }

    public function insertPostTags($values)
    {
        return $this->connection->table('posts_tags')->insert($values);
    }


    public function deleteTagPostById($id)
    {
        return $this->connection->table('posts_tags')
            ->where('id', $id)
            ->limit(1)
            ->delete();
    }

    public function getTagIdByName($name)
    {
        return $this->connection->table('tags')
            ->where('name', $name)
            ->select('id')
            ->limit(1);
    }


}