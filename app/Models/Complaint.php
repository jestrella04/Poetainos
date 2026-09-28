<?php

namespace App\Models;

use Database\Factories\ComplaintFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Complaints are intentionally anonymous: there is no reporter/user_id
 * column, and the store endpoint doesn't require authentication. Don't add
 * reporter attribution here without checking with product first.
 *
 * @mixin IdeHelperComplaint
 */
class Complaint extends Model
{
    /** @use HasFactory<ComplaintFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'reasons',
        'comment',
        'closed_at',
        'closed_comment',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'reported_url',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reasons' => 'array',
        ];
    }

    /**
     * Get the parent complainable model (writing, user or comment).
     *
     * @return MorphTo<Model, $this>
     */
    public function complainable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Where the reported content can be seen: the writing, the commented
     * writing or the user's profile. Null when the complainable relation
     * wasn't loaded, so serializing a complaint never queries it.
     *
     * @return Attribute<?string, never>
     */
    protected function reportedUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if ($this->relationLoaded('complainable') === false) {
                return null;
            }

            return match (true) {
                $this->complainable instanceof Writing => $this->complainable->path(),
                $this->complainable instanceof Comment => $this->complainable->writing?->path(),
                $this->complainable instanceof User => $this->complainable->path(),
                default => null,
            };
        });
    }
}
