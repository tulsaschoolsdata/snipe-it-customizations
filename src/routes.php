<?php

Route::get('login/graph', 'TulsaPublicSchools\SnipeItCustomizations\Http\Controllers\Auth\LoginController@redirectToGraphProvider');
Route::get('login/graph/callback', 'TulsaPublicSchools\SnipeItCustomizations\Http\Controllers\Auth\LoginController@handleGraphProviderCallback');
