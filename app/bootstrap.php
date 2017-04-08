<?php

require __DIR__ . '/../vendor/autoload.php';

//use Nette\Application\Routers\RouteList;
//use Nette\Application\Routers\Route;

$configurator = new Nette\Configurator;

//$configurator->setDebugMode(FALSE);
//$configurator->setDebugMode('178.17.1.164'); // enable for your remote IP
$configurator->enableTracy(__DIR__ . '/../log','jaroslav.maly@play-hracky.cz');

$configurator->setTimeZone('Europe/Prague');
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
//$configurator->addConfig(__DIR__ . '/config/config.local.neon');

if (strpos($_SERVER['HTTP_HOST'], 'local.filmy.spuntanela.cz') !== false) {
    $configurator->addConfig(__DIR__ . '/config/config.local.neon');
} else {
    $configurator->addConfig(__DIR__ . '/config/config.production.neon');
}

//$router = new RouteList;
//$router[] = new Route('article/<id>', 'Article:view');
//$router[] = new Route('rss.xml', 'Feed:rss');
//$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
/*
$router[] = new Route('<presenter>/<action>[/<postId>]', [
    'module' => 'Front',
    'presenter' => [
        Route::VALUE => 'Homepage',
        Route::FILTER_TABLE => [
            // řetězec v URL => presenter
            'ucet' => 'Sign',
            'clanek' => 'Post',
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
*/
//$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');


return  $configurator->createContainer();
//$container->addService('router', $router);

//return $container;
