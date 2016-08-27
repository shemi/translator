<?php

/**
 * @var Illuminate\Routing\Router $router
 */

$router->get('/', [
	'uses' => 'HomeController@index',
    'as' => 'translator.home'
]);

$router->post('finder/find', 'FinderController@find');

$router->get('finder/hint', 'FinderController@hint');

$router->resource('groups', 'GroupsController');

$router->post('import', 'GroupsController@import');


$router->get('/{group}', [
	'uses' => 'TranslationsController@index',
	'as' => 'translator.translations.index'
]);

$router->post('/{group}', [
	'uses' => 'TranslationsController@store',
	'as' => 'translator.translations.store'
]);

$router->post('/{group}/publish', [
	'uses' => 'GroupsController@publish',
    'as' => 'translator.groups.publish'
]);

$router->delete('/{group}', [
	'uses' => 'TranslationsController@delete',
	'as' => 'translator.translations.delete'
]);

$router->put('/{group}/update', [
	'uses' => 'TranslationsController@update',
	'as' => 'translator.translations.update'
]);


//$router->get('view/{group}', 'Controller@getView');

//$router->controller('/', 'Controller');