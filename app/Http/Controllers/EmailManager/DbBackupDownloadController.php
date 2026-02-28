<?php

namespace App\Http\Controllers\EmailManager;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class DbBackupDownloadController
{
    private string $disk = 'local';
    private string $tmpDir = 'tmp_db_backup';

    public function index()
    {
        return view('email-manager.db-backup.index');
    }

    public function download(Request $request)
    {
        $request->validate([
            '_token' => 'required',
        ]);

        try {
            // 1) Check Zip extension
            if (!class_exists(ZipArchive::class)) {
                return back()->with('error', 'ZIP extension is not enabled on the server (ZipArchive missing).');
            }

            $connection = config('database.default');
            $driver = config("database.connections.$connection.driver");

            if ($driver !== 'mysql') {
                return back()->with('error', "Direct DB backup supports MySQL only. Current driver: {$driver}");
            }

            // 2) Ensure temp dir exists
            Storage::disk($this->disk)->makeDirectory($this->tmpDir);

            $dbName = (string) config("database.connections.$connection.database");
            $stamp = now()->format('Y-m-d_His');
            $baseName = Str::slug($dbName ?: 'database') . "_backup_{$stamp}";

            $sqlFile = "{$this->tmpDir}/{$baseName}.sql";
            $zipFile = "{$this->tmpDir}/{$baseName}.zip";

            // 3) Write SQL dump to file progressively (no big memory)
            $sqlFullPath = Storage::disk($this->disk)->path($sqlFile);
            $this->dumpMysqlDatabaseToFile($sqlFullPath);

            // 4) Create ZIP
            $zipFullPath = Storage::disk($this->disk)->path($zipFile);

            $zip = new ZipArchive();
            $opened = $zip->open($zipFullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            if ($opened !== true) {
                // cleanup
                Storage::disk($this->disk)->delete($sqlFile);
                return back()->with('error', 'Could not create ZIP file. Check storage permissions.');
            }

            $zip->addFile($sqlFullPath, "{$baseName}.sql");
            $zip->close();

            // 5) Delete SQL after zip created
            Storage::disk($this->disk)->delete($sqlFile);

            // 6) Download zip and delete after send
            return response()->download($zipFullPath, "{$baseName}.zip")->deleteFileAfterSend(true);

        } catch (\Throwable $e) {
            Log::error('DB Backup download failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Dump MySQL database to a file (streaming + chunked reads)
     */
    private function dumpMysqlDatabaseToFile(string $sqlFullPath): void
    {
        $fh = fopen($sqlFullPath, 'w');
        if (!$fh) {
            throw new \RuntimeException("Cannot write SQL file: {$sqlFullPath}");
        }

        try {
            fwrite($fh, "-- Laravel DB Backup\n");
            fwrite($fh, "-- Generated: " . now()->toDateTimeString() . "\n\n");
            fwrite($fh, "SET NAMES utf8mb4;\n");
            fwrite($fh, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            $tables = DB::select('SHOW TABLES');
            if (empty($tables)) {
                fwrite($fh, "SET FOREIGN_KEY_CHECKS=1;\n");
                return;
            }

            $firstRow = (array) $tables[0];
            $tableKey = array_key_first($firstRow);

            foreach ($tables as $row) {
                $rowArr = (array) $row;
                $table = $rowArr[$tableKey];

                fwrite($fh, "\n-- ----------------------------\n");
                fwrite($fh, "-- Table: `{$table}`\n");
                fwrite($fh, "-- ----------------------------\n\n");
                fwrite($fh, "DROP TABLE IF EXISTS `{$table}`;\n");

                $create = DB::select("SHOW CREATE TABLE `{$table}`");
                $createArr = (array) $create[0];
                $createSql = $createArr['Create Table'] ?? array_values($createArr)[1] ?? '';
                fwrite($fh, $createSql . ";\n\n");

                // Chunked reads (avoid loading whole table into memory)
                $this->dumpTableRowsChunked($fh, $table);
            }

            fwrite($fh, "\nSET FOREIGN_KEY_CHECKS=1;\n");
        } finally {
            fclose($fh);
        }
    }

    private function dumpTableRowsChunked($fh, string $table): void
    {
        // Determine columns
        $first = DB::table($table)->limit(1)->first();
        if (!$first) {
            return;
        }

        $columns = array_keys((array) $first);
        $colList = '`' . implode('`,`', $columns) . '`';

        // Use id if exists for stable chunking
        $hasId = in_array('id', $columns, true);

        if ($hasId) {
            DB::table($table)->orderBy('id')->chunkById(500, function ($rows) use ($fh, $table, $columns, $colList) {
                foreach ($rows as $r) {
                    $vals = $this->rowToSqlValues((array) $r, $columns);
                    fwrite($fh, "INSERT INTO `{$table}` ({$colList}) VALUES ({$vals});\n");
                }
            }, 'id');
        } else {
            // fallback: simple chunk (still safer than get all)
            DB::table($table)->orderBy($columns[0])->chunk(500, function ($rows) use ($fh, $table, $columns, $colList) {
                foreach ($rows as $r) {
                    $vals = $this->rowToSqlValues((array) $r, $columns);
                    fwrite($fh, "INSERT INTO `{$table}` ({$colList}) VALUES ({$vals});\n");
                }
            });
        }

        fwrite($fh, "\n");
    }

    private function rowToSqlValues(array $row, array $columns): string
    {
        $out = [];
        $pdo = DB::getPdo();

        foreach ($columns as $col) {
            $val = $row[$col] ?? null;

            if ($val === null) {
                $out[] = 'NULL';
            } elseif (is_bool($val)) {
                $out[] = $val ? '1' : '0';
            } elseif (is_numeric($val)) {
                $out[] = (string) $val;
            } else {
                $out[] = $pdo->quote((string) $val);
            }
        }

        return implode(',', $out);
    }
}