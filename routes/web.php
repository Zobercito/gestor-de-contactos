<?php

use App\Http\Controllers\ContactExportController;
use App\Http\Controllers\DemoLoginController;
use App\Livewire\Activity\Index as ActivityIndex;
use App\Livewire\Contacts\Form as ContactForm;
use App\Livewire\Contacts\Index as ContactsIndex;
use App\Livewire\Contacts\Show as ContactShow;
use App\Livewire\Dashboard;
use App\Livewire\Imports\Index as ImportsIndex;
use App\Livewire\Imports\Show as ImportShow;
use App\Livewire\Imports\Wizard as ImportWizard;
use App\Livewire\Segments\Index as SegmentsIndex;
use App\Livewire\Users\Index as UsersIndex;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::post('demo/login/{role}', DemoLoginController::class)
    ->middleware('guest')
    ->name('demo.login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');

    Route::get('contacts', ContactsIndex::class)->name('contacts.index');
    Route::get('contacts/export', ContactExportController::class)->name('contacts.export');
    Route::get('contacts/create', ContactForm::class)->name('contacts.create');
    Route::get('contacts/{contact}', ContactShow::class)->whereNumber('contact')->name('contacts.show');
    Route::get('contacts/{contact}/edit', ContactForm::class)->whereNumber('contact')->name('contacts.edit');

    Route::get('segments', SegmentsIndex::class)->name('segments.index');
    Route::get('imports', ImportsIndex::class)->name('imports.index');
    Route::get('imports/sample', fn () => response()->download(config('contacts.sample_csv'), 'contactos-ejemplo.csv'))->name('imports.sample');
    Route::get('imports/create', ImportWizard::class)->name('imports.create');
    Route::get('imports/{import}', ImportShow::class)->whereNumber('import')->name('imports.show');
    Route::get('activity', ActivityIndex::class)->name('activity.index');
    Route::get('users', UsersIndex::class)->name('users.index');

    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
