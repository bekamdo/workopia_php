<?php
$router -> get("/workopia/public/","HomeController@index");
$router -> get("/workopia/public/listings","ListingController@index");
$router -> get("/workopia/public/listings/search/","ListingController@search");

$router -> get("/workopia/public/listings/create/","ListingController@create",['auth']);

$router -> get("/workopia/public/listings/{id}","ListingController@show");

$router -> get("/workopia/public/listings/edit/{id}","ListingController@edit",['auth']);


 
$router -> post("/workopia/public/listings","ListingController@store",['auth']);
$router -> put("/workopia/public/listings/{id}","ListingController@update",['auth']);
$router -> delete("/workopia/public/listings/{id}","ListingController@destroy");


$router -> get("/workopia/public/auth/register","UserController@create",['guest']);
$router -> get("/workopia/public/auth/login","UserController@login",['guest']);

$router -> post("/workopia/public/auth/register","UserController@store",['guest']);
$router -> post("/workopia/public/auth/logout","UserController@logout",['auth']);

$router -> post("/workopia/public/auth/login","UserController@authenticate",['guest']);


?>