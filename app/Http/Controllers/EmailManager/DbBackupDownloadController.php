<?php

namespace App\Http\Controllers\EmailManager;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class DbBackupDownloadController
{
    private string $disk = 'local';
    private string $tmpDir = 'tmp_db_backup';

    public function index()
    {
        // ✅ For normal controller views, ->layout() does NOT work.
        // The view must wrap itself in the component layout (layouts.app).
        return view('email-manager.db-backup.index');
    }

    public function download(Request $request)
    {
        $request->validate([
            '_token' => 'required',
        ]);

        // If you want only admins, add your own check:
        // abort_unless(auth()->user()?->is_admin, 403);

        $connection = config('database.default');
        $driver = config("database.connections.$connection.driver");

        if ($driver !== 'mysql') {
            return back()->with('error', "Direct DB backup supports MySQL only. Current driver: {$driver}");
        }

        Storage::disk($this->disk)->makeDirectory($this->tmpDir);

        $dbName = (string) config("database.connections.$connection.database");
        $stamp = now()->format('Y-m-d_His');
        $baseName = Str::slug($dbName ?: 'database') . "_backup_{$stamp}";

        $sqlFile = "{$this->tmpDir}/{$baseName}.sql";
        $zipFile = "{$this->tmpDir}/{$baseName}.zip";

        // Build SQL dump (in-memory)
        $sql = $this->dumpMysqlDatabase();

        // Save SQL to temp
        Storage::disk($this->disk)->put($sqlFile, $sql);

        // Create zip
        $zipFullPath = Storage::disk($this->disk)->path($zipFile);
        $sqlFullPath = Storage::disk($this->disk)->path($sqlFile);

        $zip = new ZipArchive();
        if ($zip->open($zipFullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            Storage::disk($this->disk)->delete($sqlFile);
            return back()->with('error', 'Could not create ZIP file.');
        }

        $zip->addFile($sqlFullPath, "{$baseName}.sql");
        $zip->close();

        // Delete SQL (only zip remains)
        Storage::disk($this->disk)->delete($sqlFile);

        // Download zip and delete after sent
        return response()->download($zipFullPath, "{$baseName}.zip")->deleteFileAfterSend(true);
    }

    private function dumpMysqlDatabase(): string
    {
        $sql = '';
        $sql .= "-- Laravel DB Backup\n";
        $sql .= "-- Generated: " . now()->toDateTimeString() . "\n\n";
        $sql .= "SET NAMES utf8mb4;\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        $tables = DB::select('SHOW TABLES');
        if (empty($tables)) {
            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
            return $sql;
        }

        $firstRow = (array) $tables[0];
        $tableKey = array_key_first($firstRow);

        foreach ($tables as $row) {
            $rowArr = (array) $row;
            $table = $rowArr[$tableKey];

            $sql .= "\n-- ----------------------------\n";
            $sql .= "-- Table: `{$table}`\n";
            $sql .= "-- ----------------------------\n\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";

            $create = DB::select("SHOW CREATE TABLE `{$table}`");
            $createArr = (array) $create[0];
            $createSql = $createArr['Create Table'] ?? array_values($createArr)[1] ?? '';
            $sql .= $createSql . ";\n\n";

            $rows = DB::table($table)->get();
            if ($rows->isEmpty()) {
                continue;
            }

            $columns = array_keys((array) $rows->first());
            $colList = '`' . implode('`,`', $columns) . '`';

            foreach ($rows as $r) {
                $rArr = (array) $r;
                $values = [];

                foreach ($columns as $col) {
                    $val = $rArr[$col];

                    if (is_null($val)) {
                        $values[] = 'NULL';
                    } elseif (is_bool($val)) {
                        $values[] = $val ? '1' : '0';
                    } elseif (is_numeric($val)) {
                        $values[] = (string) $val;
                    } else {
                        $values[] = DB::getPdo()->quote((string) $val);
                    }
                }

                $sql .= "INSERT INTO `{$table}` ({$colList}) VALUES (" . implode(',', $values) . ");\n";
            }

            $sql .= "\n";
        }

        $sql .= "\nSET FOREIGN_KEY_CHECKS=1;\n";
        return $sql;
    }
}