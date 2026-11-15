<?php

namespace App\Providers;

use App\Models\Saran;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Angka lencana saran (admin: saran baru, pengguna: saran yang baru dibalas), dihitung sekali per permintaan
        View::composer(['beranda', 'layouts.*', 'admin.*', 'saran.*'], function ($view) {
            $atribut = request()->attributes;

            if (! $atribut->has('lencana_saran')) {
                $atribut->set('lencana_saran', auth()->check() ? Saran::lencana(auth()->user()) : 0);
            }

            $view->with('lencanaSaran', $atribut->get('lencana_saran'));
        });
    }
}
