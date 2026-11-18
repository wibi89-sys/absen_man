<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Services\DatabaseManagementService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatabaseMaintenanceController extends Controller
{
    public function __construct(
        protected DatabaseManagementService $databaseService
    ) {}

    /**
     * Display database maintenance, backup list, and reset panel.
     */
    public function index(): View
    {
        $backups = $this->databaseService->listBackups();
        $dbSize = $this->databaseService->getDatabaseSize();
        $tables = $this->databaseService->getTables();

        $connectionInfo = [
            'database' => config('database.connections.mysql.database'),
            'host' => config('database.connections.mysql.host'),
            'port' => config('database.connections.mysql.port'),
            'driver' => config('database.connections.mysql.driver'),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug'),
        ];

        return view('setting.database', compact('backups', 'dbSize', 'tables', 'connectionInfo'));
    }

    /**
     * Create a new database backup.
     */
    public function createBackup(Request $request): RedirectResponse
    {
        $request->validate([
            'description' => ['nullable', 'string', 'max:150'],
        ]);

        try {
            $result = $this->databaseService->backup($request->input('description'));

            return redirect()->route('settings.database.index')->with('success', "Cadangan database '{$result['filename']}' ({$result['human_size']}) berhasil dibuat.");
        } catch (Exception $e) {
            return redirect()->route('settings.database.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Download backup file.
     */
    public function downloadBackup(Request $request, ?string $filename = null): BinaryFileResponse
    {
        $file = $filename ?: $request->query('file') ?: $request->input('filename');
        if (! $file) {
            abort(404, 'File cadangan tidak ditemukan.');
        }

        return $this->databaseService->downloadBackup($file);
    }

    /**
     * Restore database from an existing backup file.
     */
    public function restoreBackup(Request $request): RedirectResponse
    {
        $request->validate([
            'filename' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! Hash::check($request->password, Auth::user()->password)) {
            return back()->with('error', 'Konfirmasi kata sandi Anda salah. Pemulihan database dibatalkan demi keamanan.');
        }

        try {
            $result = $this->databaseService->restore($request->filename);

            return redirect()->route('settings.database.index')->with('success', $result['message']);
        } catch (Exception $e) {
            return redirect()->route('settings.database.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Upload .sql file and restore.
     */
    public function uploadRestore(Request $request): RedirectResponse
    {
        $request->validate([
            'sql_file' => ['required', 'file', 'max:51200'], // max 50MB
            'password' => ['required', 'string'],
        ]);

        if (! Hash::check($request->password, Auth::user()->password)) {
            return back()->with('error', 'Konfirmasi kata sandi Anda salah. Pemulihan database dibatalkan.');
        }

        try {
            $result = $this->databaseService->restore(null, $request->file('sql_file'));

            return redirect()->route('settings.database.index')->with('success', $result['message']);
        } catch (Exception $e) {
            return redirect()->route('settings.database.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Delete a backup file.
     */
    public function deleteBackup(Request $request, ?string $filename = null): RedirectResponse
    {
        $file = $filename ?: $request->input('filename') ?: $request->query('file');
        if (! $file) {
            return redirect()->route('settings.database.index')->with('error', 'Nama berkas cadangan diperlukan.');
        }

        if ($this->databaseService->deleteBackup($file)) {
            return redirect()->route('settings.database.index')->with('success', "Berkas cadangan '{$file}' berhasil dihapus.");
        }

        return redirect()->route('settings.database.index')->with('error', 'Berkas cadangan gagal dihapus atau tidak ditemukan.');
    }

    /**
     * Reset database.
     */
    public function resetDatabase(Request $request): RedirectResponse
    {
        $request->validate([
            'mode' => ['required', 'in:transactions,fresh'],
            'confirm_text' => ['required', 'in:RESET'],
            'password' => ['required', 'string'],
            'new_admin_password' => ['nullable', 'string', 'min:8'],
        ]);

        if (! Hash::check($request->password, Auth::user()->password)) {
            return back()->with('error', 'Konfirmasi kata sandi Anda tidak sesuai. Reset database dibatalkan.');
        }

        try {
            $result = $this->databaseService->reset($request->mode, $request->new_admin_password);

            if ($request->mode === 'fresh') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $cred = $result['admin_credentials'];

                return redirect()->route('login')->with('status', "Database berhasil direset total (Clean Slate). Masuk dengan akun: {$cred['username']} / sandi: {$cred['default_password']}");
            }

            return redirect()->route('settings.database.index')->with('success', $result['message']." Cadangan pengaman darurat: {$result['safety_backup']}");
        } catch (Exception $e) {
            return redirect()->route('settings.database.index')->with('error', $e->getMessage());
        }
    }
}
