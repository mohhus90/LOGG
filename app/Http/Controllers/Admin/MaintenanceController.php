<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class MaintenanceController extends Controller
{
    private function backupDir(): string
    {
        $path = storage_path('app/backups');
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }
        return $path;
    }

    public function index()
    {
        $this->authorize_super();

        // قائمة النسخ الاحتياطية
        $backupDir  = $this->backupDir();
        $backupFiles = collect(File::files($backupDir))
            ->filter(fn($f) => str_ends_with($f->getFilename(), '.sql'))
            ->sortByDesc(fn($f) => $f->getMTime())
            ->map(fn($f) => [
                'name'    => $f->getFilename(),
                'size'    => $this->formatBytes($f->getSize()),
                'date'    => date('Y-m-d H:i:s', $f->getMTime()),
            ])->values();

        // آخر 200 سطر من log file
        $logPath  = storage_path('logs/laravel.log');
        $logLines = [];
        if (File::exists($logPath)) {
            $lines    = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $logLines = array_slice(array_reverse($lines), 0, 200);
        }

        // المستخدمون المسجلون في الجلسات (session file-based)
        $activeAdmins = \App\Models\Admin::select('id','name','email','updated_at')
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();

        return view('admin.maintenance.index', compact('backupFiles', 'logLines', 'activeAdmins'));
    }

    public function backupNow()
    {
        $this->authorize_super();

        $result = $this->runBackup();

        if ($result['success']) {
            return redirect()->route('maintenance.index')
                ->with('success', 'تم إنشاء النسخة الاحتياطية: ' . $result['filename']);
        }
        return redirect()->route('maintenance.index')
            ->with('error', 'فشل إنشاء النسخة الاحتياطية: ' . $result['message']);
    }

    public function download(Request $request)
    {
        $this->authorize_super();

        $filename = basename($request->query('file', ''));
        $path     = $this->backupDir() . DIRECTORY_SEPARATOR . $filename;

        if (!$filename || !File::exists($path) || !str_ends_with($filename, '.sql')) {
            return redirect()->route('maintenance.index')->with('error', 'الملف غير موجود.');
        }

        return response()->download($path);
    }

    public function restore(Request $request)
    {
        $this->authorize_super();

        $request->validate(['backup_file' => 'required|string']);

        $filename = basename($request->input('backup_file'));
        $path     = $this->backupDir() . DIRECTORY_SEPARATOR . $filename;

        if (!File::exists($path) || !str_ends_with($filename, '.sql')) {
            return redirect()->route('maintenance.index')->with('error', 'الملف غير موجود.');
        }

        try {
            $sql = File::get($path);
            // تنفيذ ملف SQL بشكل آمن عبر PDO
            DB::unprepared($sql);
            return redirect()->route('maintenance.index')
                ->with('success', 'تم استعادة النسخة الاحتياطية بنجاح من: ' . $filename);
        } catch (\Throwable $e) {
            return redirect()->route('maintenance.index')
                ->with('error', 'فشل الاستعادة: ' . $e->getMessage());
        }
    }

    public function deleteBackup(Request $request)
    {
        $this->authorize_super();

        $filename = basename($request->query('file', ''));
        $path     = $this->backupDir() . DIRECTORY_SEPARATOR . $filename;

        if ($filename && File::exists($path) && str_ends_with($filename, '.sql')) {
            File::delete($path);
            return redirect()->route('maintenance.index')->with('success', 'تم حذف الملف: ' . $filename);
        }
        return redirect()->route('maintenance.index')->with('error', 'الملف غير موجود.');
    }

    public function clearLogs()
    {
        $this->authorize_super();

        $logPath = storage_path('logs/laravel.log');
        if (File::exists($logPath)) {
            File::put($logPath, '');
        }
        return redirect()->route('maintenance.index')->with('success', 'تم مسح ملف السجلات بنجاح.');
    }

    // ─── helpers ───────────────────────────────────────────

    private function authorize_super()
    {
        if (!Auth::guard('admin')->check() || !Auth::guard('admin')->user()->is_super_admin) {
            abort(403, 'غير مصرح لك بالوصول إلى هذه الصفحة.');
        }
    }

    private function runBackup(): array
    {
        $db       = config('database.connections.mysql.database');
        $user     = config('database.connections.mysql.username');
        $pass     = config('database.connections.mysql.password');
        $host     = config('database.connections.mysql.host');
        $port     = config('database.connections.mysql.port', 3306);
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $this->backupDir() . DIRECTORY_SEPARATOR . $filename;

        // محاولة mysqldump أولاً
        $passArg  = $pass ? "-p" . escapeshellarg($pass) : '';
        $cmd      = "mysqldump --host={$host} --port={$port} --user={$user} {$passArg} {$db} > " . escapeshellarg($filepath) . " 2>&1";
        exec($cmd, $output, $code);

        if ($code === 0 && File::exists($filepath) && File::size($filepath) > 100) {
            return ['success' => true, 'filename' => $filename];
        }

        // fallback: PHP-based backup via PDO
        try {
            $sql = $this->phpBackup($db, $user, $pass, $host, $port);
            File::put($filepath, $sql);
            return ['success' => true, 'filename' => $filename];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function phpBackup(string $db, string $user, string $pass, string $host, int $port): string
    {
        $pdo    = new \PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $output = "-- NEXA ERP Database Backup\n-- Date: " . date('Y-m-d H:i:s') . "\n-- Database: {$db}\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";

        $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            // DROP + CREATE
            $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_NUM);
            $output .= "\n-- Table: {$table}\n";
            $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $output .= $create[1] . ";\n\n";

            // INSERT DATA
            $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $vals   = array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote((string)$v), $row);
                $output .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $vals) . ");\n";
            }
            $output .= "\n";
        }

        $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
        return $output;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }
}
