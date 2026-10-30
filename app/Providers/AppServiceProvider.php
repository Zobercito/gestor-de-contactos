<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\Contact;
use App\Models\Import;
use App\Models\Note;
use App\Models\Segment;
use App\Models\User;
use App\Observers\ContactObserver;
use App\Observers\NoteObserver;
use App\Observers\SegmentObserver;
use App\Observers\UserObserver;
use App\Policies\ActivityLogPolicy;
use App\Policies\ContactPolicy;
use App\Policies\ImportPolicy;
use App\Policies\NotePolicy;
use App\Policies\SegmentPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Gate::policy(Contact::class, ContactPolicy::class);
        Gate::policy(Segment::class, SegmentPolicy::class);
        Gate::policy(Import::class, ImportPolicy::class);
        Gate::policy(Note::class, NotePolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(ActivityLog::class, ActivityLogPolicy::class);

        Contact::observe(ContactObserver::class);
        Segment::observe(SegmentObserver::class);
        Note::observe(NoteObserver::class);
        User::observe(UserObserver::class);
    }
}
