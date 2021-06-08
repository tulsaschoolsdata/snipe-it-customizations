<?php

namespace TulsaPublicSchools\SnipeItCustomizations\Http\Controllers;

use App\Http\Controllers\Controller;

class UploadsController extends Controller
{
    public function redirectToS3Public($path)
    {
        $disk = \Storage::disk('public');

        if (!$disk->exists($path)) {
            return abort(404);
        }

        $url = $disk->url($path);

        return redirect()->away($url);
    }
}
