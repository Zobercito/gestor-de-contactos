<?php

namespace App\Models;

use App\Enums\ContactType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $company
 * @property string|null $job_title
 * @property ContactType|null $contact_type
 * @property string|null $business_type
 * @property string|null $gender
 * @property string|null $city
 * @property string|null $province
 * @property string|null $country
 * @property Carbon|null $birthdate
 * @property string|null $source
 * @property string|null $associated_brand
 * @property string|null $associated_company
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property-read string $initials
 * @property-read string $avatar_color
 * @property-read string|null $location
 * @property-read string|null $birthday_label
 * @property-read int|null $age
 * @property-read Collection<int, Segment> $segments
 * @property-read Collection<int, Note> $notes
 * @property-read User|null $creator
 * @property-read User|null $updater
 */
class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'job_title',
        'contact_type',
        'business_type',
        'gender',
        'city',
        'province',
        'country',
        'birthdate',
        'source',
        'associated_brand',
        'associated_company',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'contact_type' => ContactType::class,
        ];
    }

    public function segments(): BelongsToMany
    {
        return $this->belongsToMany(Segment::class)->withTimestamps();
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class)->latest();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%'.mb_strtolower($term).'%';

        return $query->where(function (Builder $query) use ($like) {
            $query->whereRaw('lower(name) like ?', [$like])
                ->orWhereRaw('lower(email) like ?', [$like])
                ->orWhereRaw('lower(phone) like ?', [$like])
                ->orWhereRaw('lower(company) like ?', [$like])
                ->orWhereRaw('lower(city) like ?', [$like]);
        });
    }

    public function getInitialsAttribute(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part) => Str::of($part)->substr(0, 1))
            ->implode('');
    }

    public function getAvatarColorAttribute(): string
    {
        $palette = ['#C65D3B', '#7A8B6F', '#D9A441', '#8C6A9E', '#4F7C8A', '#B56576'];

        return $palette[abs(crc32(mb_strtolower($this->name))) % count($palette)];
    }

    public function getLocationAttribute(): ?string
    {
        $parts = array_filter([$this->city, $this->province, $this->country]);

        return $parts === [] ? null : implode(', ', $parts);
    }

    public function getBirthdayLabelAttribute(): ?string
    {
        return $this->birthdate?->translatedFormat('j \d\e F');
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birthdate?->age;
    }
}
