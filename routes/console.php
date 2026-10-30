<?php

use Illuminate\Support\Facades\Schedule;

if (config('demo.enabled')) {
    Schedule::command('demo:reset --force')
        ->dailyAt(config('demo.reset_at', '03:00'))
        ->withoutOverlapping();
}
