<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\DailySelectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperDailySelection
 */
class DailySelection extends Model
{
    /** @use HasFactory<DailySelectionFactory> */
    use HasFactory;

    private const REPEAT_EXCLUSION_DAYS = 30;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'selected_on',
        'writing_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'selected_on' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Writing, $this>
     */
    public function writing(): BelongsTo
    {
        return $this->belongsTo(Writing::class);
    }

    /**
     * The featured writing with its listing relations, unless its author is blocked.
     *
     * @param  array<int>  $blockedUserIds
     */
    public function visibleWriting(array $blockedUserIds): ?Writing
    {
        return Writing::visibleTo($blockedUserIds)
            ->withListingRelations()
            ->find($this->writing_id);
    }

    /**
     * Today's pick, created on first call and returned as-is afterwards.
     */
    public static function pickForToday(): self
    {
        return self::firstOrCreate(
            ['selected_on' => Carbon::today()],
            fn (): array => ['writing_id' => self::randomEligibleWriting()->id],
        );
    }

    /**
     * The most recent pick made up to and including today, if any.
     */
    public static function current(): ?self
    {
        return self::where('selected_on', '<=', Carbon::today())
            ->orderByDesc('selected_on')
            ->first();
    }

    /**
     * Picks a random author, then a random writing by them, leaving out
     * writings featured recently. Falls back to every writing when all
     * of them were featured recently.
     */
    private static function randomEligibleWriting(): Writing
    {
        $excludedIds = self::where('selected_on', '>=', Carbon::today()->subDays(self::REPEAT_EXCLUSION_DAYS))
            ->pluck('writing_id');

        if (Writing::whereNotIn('id', $excludedIds)->doesntExist()) {
            $excludedIds = collect();
        }

        return User::whereHas('writings', fn ($query) => $query->whereNotIn('id', $excludedIds))
            ->inRandomOrder()
            ->firstOrFail()
            ->writings()
            ->whereNotIn('id', $excludedIds)
            ->inRandomOrder()
            ->firstOrFail();
    }
}
