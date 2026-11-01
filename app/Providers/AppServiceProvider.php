<?php

namespace App\Providers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view): void {
            static $shared = null;
            if ($shared === null) {
                $request = request();

                if ($request->filled('tahun')) {
                    $reqTahun = (int) $request->input('tahun');
                    if ($reqTahun >= 2020 && $reqTahun <= 2035 && $request->hasSession()) {
                        $request->session()->put('tahun_anggaran', $reqTahun);
                    }
                }

                $activeTahun = (int) (
                    ($request->filled('tahun') ? $request->input('tahun') : null)
                    ?? ($request->hasSession() ? $request->session()->get('tahun_anggaran') : null)
                    ?? Controller::defaultTahun()
                );

                $shared = [
                    'activeTahunGlobal' => $activeTahun,
                    'availableTahunOptions' => Controller::availableTahunOptions(),
                    'appSettings' => Setting::getAll(),
                ];
            }

            $view->with($shared);
        });
    }
}
