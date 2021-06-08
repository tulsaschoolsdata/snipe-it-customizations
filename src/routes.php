<?php

Route::get('login/graph', 'TulsaPublicSchools\SnipeItCustomizations\Http\Controllers\Auth\LoginController@redirectToGraphProvider');
Route::get('login/graph/callback', 'TulsaPublicSchools\SnipeItCustomizations\Http\Controllers\Auth\LoginController@handleGraphProviderCallback');

// A hack to redirect any "/uploads" URLs to S3
Route::get('uploads/{path}', 'TulsaPublicSchools\SnipeItCustomizations\Http\Controllers\UploadsController@redirectToS3Public')->where('path', '.+');
