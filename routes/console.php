<?php

use Illuminate\Support\Facades\Schedule;

// Cek tiap menit apakah ada waktu salat yang tiba, lalu kirim push notification.
// Aktifkan dengan cron: * * * * * php /path/ke/proyek/artisan schedule:run
// (saat pengembangan: php artisan schedule:work)
Schedule::command('salat:kirim')->everyMinute()->withoutOverlapping();
