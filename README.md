# Snipe-IT Customizations for Tulsa Public Schools

## Override Hacks

It’s not possible to [merge config values](https://laravel.com/docs/5.5/packages#configuration) over existing values defined in the application’s config. So we resort to tracking our own copy and clobbering the originals.

```console
$ composer require tulsaschoolsdata/snipe-it-customizations:dev-master
$ cp vendor/tulsaschoolsdata/snipe-it-customizations/src/config/backup.php config/backup.php
$ cp vendor/tulsaschoolsdata/snipe-it-customizations/src/config/filesystems.php config/filesystems.php
```
