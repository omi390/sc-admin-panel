<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Exceptions\CannotStartDump;
use Spatie\DbDumper\Exceptions\DumpFailed;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return void
     * @throws CannotStartDump
     * @throws DumpFailed
     */
    public function handle()
    {
        $sql_file_name = 'database_backup_'.date("Y-m-d_H-i").'.sql';

        MySql::create()
            ->setDbName(env('DB_DATABASE'))
            ->setUserName(env('DB_USERNAME'))
            ->setPassword(env('DB_PASSWORD'))
            ->setHost(env('DB_HOST', '127.0.0.1'))
            ->setPort(env('DB_PORT', '3306'))
            ->setDumpBinaryPath(env('DUMP_BINARY_PATH', '/usr/bin/'))
            ->dumpToFile($sql_file_name);
    }
}
