<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Contact;
use App\Models\Import;
use App\Models\Segment;
use Carbon\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $today = Carbon::today();
        $total = Contact::query()->count();
        $newThisMonth = Contact::query()->where('created_at', '>=', $today->copy()->startOfMonth())->count();
        $segments = Segment::query()->withCount('contacts')->orderByDesc('contacts_count')->get();
        $b2b = Contact::query()->where('contact_type', 'B2B')->count();
        $b2c = Contact::query()->where('contact_type', 'B2C')->count();

        $birthdayEntries = Contact::query()
            ->whereNotNull('birthdate')
            ->get(['id', 'name', 'email', 'company', 'birthdate', 'city'])
            ->map(function (Contact $contact) use ($today) {
                $next = $contact->birthdate->copy()->year($today->year)->startOfDay();

                if ($next->lt($today)) {
                    $next->addYear();
                }

                return [
                    'contact' => $contact,
                    'date' => $next,
                    'days' => (int) $today->diffInDays($next),
                ];
            })
            ->filter(fn (array $entry) => $entry['days'] <= 30);

        $birthdays = $birthdayEntries->sortBy('days')->take(6)->values();
        $birthdaysCount = $birthdayEntries->count();

        $imports = Import::query()->with('user')->latest()->take(4)->get();
        $activity = ActivityLog::query()->with(['user', 'subject'])->latest()->take(6)->get();

        $segmentMax = max(1, (int) $segments->max('contacts_count'));
        $lastImport = Import::query()->where('status', 'completed')->latest()->first();

        return view('livewire.dashboard', [
            'total' => $total,
            'newThisMonth' => $newThisMonth,
            'segments' => $segments,
            'segmentMax' => $segmentMax,
            'b2b' => $b2b,
            'b2c' => $b2c,
            'birthdays' => $birthdays,
            'birthdaysCount' => $birthdaysCount,
            'imports' => $imports,
            'activity' => $activity,
            'lastImport' => $lastImport,
            'today' => $today,
        ])->layout('components.layouts.app');
    }
}
