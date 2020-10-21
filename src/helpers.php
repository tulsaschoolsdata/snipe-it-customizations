<?php

if (! function_exists('tps_version')) {
    function tps_version() {
        $path = base_path('version.tps.txt');

        if (is_file($path)) {
            return file_get_contents($path);
        }

        return '';
    }
}
