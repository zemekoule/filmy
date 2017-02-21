<?php

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;


class RouterFactory
{
	use Nette\StaticClass;

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$router = new RouteList;

		/*
        $router[] = new Route('<presenter>/<action>/[/<id>]', array(
            'module' => 'Front',
            'presenter' => 'Homepage',
            'action' => 'default',
            'id' => NULL,
        ));
        */

        $router[] = new Route('<presenter>/<action>[/<postId>]', [
            'module' => 'Front',
            'presenter' => [
                Route::VALUE => 'Homepage',
                Route::FILTER_TABLE => [
                    // řetězec v URL => presenter
                    'ucet' => 'Sign',
                    'clanek' => 'Post',
                    'stitek' => 'Tags',
                    'stranka' => 'page',
                ],
            ],
            'action' => [
                Route::VALUE => 'default',
                Route::FILTER_TABLE => [
                    // řetězec v URL => presenter
                    'prihlasit' => 'in',
                    'odhlasit' => 'out',
                    'hledat' => 'find',
                    'novy' => 'create',
                    'upravit' => 'edit',
                    'stranka' => 'page',
                ],
            ],
            'postId' => NULL,
        ]);

		return $router;
	}

}
