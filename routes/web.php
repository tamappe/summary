<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', function () {
//    return view('welcome');
//});

Route::get('/save/rss1', 'ScrapingController@save_xml_rss1');
Route::get('/save/atom', 'ScrapingController@parse_xml_atom');

Route::get('/', 'Api\EntryController@index');
Route::get('/api/index', 'Api\EntryController@index');
Route::get('/api/index', 'Api\EntryController@index');
Route::get('/api/blog/{id}', 'Api\EntryController@blog');
Route::get('/api/blog', 'Api\BlogController@index');

Route::get('/admin/form', 'ScrapingController@form');
Route::post('/rss', 'ScrapingController@result_rss');
Route::post('/atom', 'ScrapingController@result_atom');

Route::get('/scraping', 'ScrapingController@rss2');