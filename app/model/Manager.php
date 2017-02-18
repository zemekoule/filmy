<?php

/**
 * Created by PhpStorm.
 * User: Jarda
 * Date: 14.01.2017
 * Time: 11:07
 */
namespace App\Model;

use Nette;

abstract class Manager {

    use Nette\SmartObject;

    /** @var Nette\Database\Context */
    protected $connection;

    public function __construct(Nette\Database\Context $db) {
        $this->connection = $db;
    }
}