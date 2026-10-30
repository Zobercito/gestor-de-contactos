<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $contact_id
 * @property int $user_id
 * @property string $body
 * @property-read Contact $contact
 * @property-read User $user
 */
class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'user_id',
        'body',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
