<?php
Route::group(['prefix' => 'crud/{table}'], function () {
    Route::get('/', 'CrudController@index');
    Route::get('/{id}', 'CrudController@show');
    Route::post('/', 'CrudController@store');
    Route::put('/{id}', 'CrudController@update');
    Route::delete('/{id}', 'CrudController@destroy');
});
