<?php
$router->group(['prefix' => 'crud/{table}'], function ($router) {
    $router->get('/', 'CrudController@index');
    $router->get('/{id}', 'CrudController@show');
    $router->post('/', 'CrudController@store');
    $router->put('/{id}', 'CrudController@update');
    $router->delete('/{id}', 'CrudController@destroy');
});
