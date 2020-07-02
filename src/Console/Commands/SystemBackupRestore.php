<?php

namespace TulsaPublicSchools\SnipeItCustomizations\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupCollection;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatus;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatusFactory;

class SystemBackupRestore extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'snipeit:backup-restore';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command restores a database dump and unzips all of the uploaded files in the upload directories.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:backup-restore {name?} {--backup=} {--auto}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->argument('name') ?: $this->option('auto')
            ? $this->nameChoices()->first()
            : $this->choice('Choose a backup', $this->nameChoices()->toArray(), 0);

        $version = $this->option('backup') ?: $this->option('auto')
            ? $this->versionChoices($name)->first()
            : $this->choice('Choose a version', $this->versionChoices($name)->toArray(), 0);

        $backup = $this->selectBackup($name, $version);

        $this->info("Loading backup: {$backup->path()}");
        $zipFilePath = $this->unzipBackup($backup);

        $this->info('Restoring:');
        $this->restore($zipFilePath);
    }

    /** @var null|Collection */
    private $_statuses = null;

    protected function statuses() {
        if (null === $this->_statuses) {
            $this->_statuses = BackupDestinationStatusFactory::createForMonitorConfig(config('backup.monitorBackups'));
        }

        return $this->_statuses;
    }

    protected function nameChoices() {
        return $this->statuses()->map(function (BackupDestinationStatus $backupDestinationStatus) {
            return $backupDestinationStatus->backupName();
        });
    }

    protected function versionChoices($name) {
        $backupDestinationStatus = $this->selectBackupDestinationStatus($name);

        // TODO: error if $backupDestinationStatus is null

        $prefix = config('backup.backup.destination.filename_prefix');
        $prefix_length = strlen($prefix);

        return $backupDestinationStatus->backupDestination()->backups()
            ->map(function (Backup $backup) use ($name) {
                return preg_replace("#^${name}/#", '', $backup->path());
            })
            ->filter(function ($filename) use ($prefix, $prefix_length) {
                return substr($filename, 0, $prefix_length) === $prefix;
            })
            ->sort()->reverse()->values();
    }

    protected function selectBackupDestinationStatus($name) {
        return $this->statuses()->filter(function (BackupDestinationStatus $backupDestinationStatus, $key) use ($name) {
            return $backupDestinationStatus->backupName() === $name;
        })->first();
    }

    protected function selectBackup($name, $version) {
        $backupDestinationStatus = $this->selectBackupDestinationStatus($name);

        return $backupDestinationStatus->backupDestination()->backups()->filter(function (Backup $backup, $key) use ($name, $version) {
            return $backup->path() === "$name/$version";
        })->first();
    }

    protected function restore($zipFilePath) {
        $zip = new \ZipArchive();
        $zip->open($zipFilePath);

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $stream = $zip->getStream($filename);

            $this->line("- {$filename}");

            if (preg_match('#^db-dumps/#', $filename)) {
                $this->restoreDatabase($stream);
            } else {
                $this->restoreFile($stream, $filename);
            }
        }

    }

    protected function unzipBackup(Backup $backup) {
        $this->_zipFileHandle = $zipFileHandle = tmpfile();

        fwrite($zipFileHandle, stream_get_contents($backup->stream()));

        return stream_get_meta_data($zipFileHandle)['uri'];
    }

    protected function restoreFile($stream, $filename) {
        $relative = preg_replace('#^'.base_path().'/#', '', "/$filename");
        $absolute = base_path($relative);

        file_put_contents($absolute, stream_get_contents($stream));
    }

    protected function restoreDatabase($stream) {
        // TODO: what if there were multiple databases?
        // TOOD: pick the connection based on the db-dumps/{$type}-{$dbName}.sql?

        $sqlFileHandle = tmpFile();
        fwrite($sqlFileHandle, stream_get_contents($stream));
        $sqlFilePath = stream_get_meta_data($sqlFileHandle)['uri'];

        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);
        $user = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $database = config('database.connections.mysql.database');
        $charset = config('database.connections.mysql.charset');
        $collation = config('database.connections.mysql.collation');

        $credentials = implode("\n", [
            '[client]',
            "host = {$host}",
            "port = {$port}",
            "user = {$user}",
            "password = {$password}",
        ]);

        $myFileHandle = tmpFile();
        fwrite($myFileHandle, $credentials);
        $myFilePath = stream_get_meta_data($myFileHandle)['uri'];

        $mysql = escapeshellcmd('mysql');
        $defaults_file = escapeshellarg("--defaults-file={$myFilePath}");
        $drop = escapeshellarg("DROP DATABASE IF EXISTS {$database};");
        $create = escapeshellarg("CREATE DATABASE {$database} CHARACTER SET {$charset} COLLATE {$collation};");

        $this->exec([$mysql, $defaults_file, '-e', $drop]);
        $this->exec([$mysql, $defaults_file, '-e', $create]);
        $this->exec([$mysql, $defaults_file, escapeshellarg($database), '<', escapeshellarg($sqlFilePath)]);
    }

    protected function exec($args) {
        $this->line( shell_exec(implode(' ', $args)) );
    }
}
