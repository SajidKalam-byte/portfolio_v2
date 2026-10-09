<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ContentCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Throwable;

/**
 * Cache maintenance for hosts without SSH access.
 */
class MaintenanceController extends Controller
{
    public function __invoke(string $action): RedirectResponse
    {
        try {
            $message = match ($action) {
                'clear-cache' => $this->clearContentCache(),
                'optimize' => $this->run('optimize', 'Configuration, routes, views, and events cached.'),
                'clear-optimization' => $this->run('optimize:clear', 'All framework caches cleared.'),
            };
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'That didn’t work: '.$e->getMessage());
        }

        return back()->with('status', $message);
    }

    private function clearContentCache(): string
    {
        ContentCache::flush();

        return 'Content cache cleared. Pages will reflect the latest data.';
    }

    private function run(string $command, string $message): string
    {
        Artisan::call($command);

        return $message;
    }
}
