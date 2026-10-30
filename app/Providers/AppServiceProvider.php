<?php

namespace App\Providers;

use App\Http\Controllers\Controller;
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
        View::composer('layouts.app', function ($view): void {
            $request = request();

            if ($request->filled('tahun')) {
                $reqTahun = (int) $request->input('tahun');
                if ($reqTahun >= 2020 && $reqTahun <= 2035 && $request->hasSession()) {
                    $request->session()->put('tahun_anggaran', $reqTahun);
                }
            }

            $activeTahun = (int) ($view->getData()['tahun']
                ?? ($request->filled('tahun') ? $request->input('tahun') : null)
                ?? ($request->hasSession() ? $request->session()->get('tahun_anggaran') : null)
                ?? Controller::defaultTahun());

            $view->with([
                'activeTahunGlobal' => $activeTahun,
                'availableTahunOptions' => Controller::availableTahunOptions(),
            ]);
        });
    }
}
