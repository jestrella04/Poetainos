<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Complaint;
use App\Models\DailySelection;
use App\Models\Like;
use App\Models\Shelf;
use App\Models\User;
use App\Models\Writing;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

/**
 * The site's recent activity, newest first, read straight from the tables
 * that already record it: sign-ups, writings, comments, likes, bookmarks,
 * writings of the day and complaints. Every source is reduced to the same
 * row (kind, subject_id, user_id, writing_id, created_at) and unioned, so
 * the feed needs no table of its own. `user_id` is who acted, or the
 * reported user for a user complaint; `writing_id` is the writing involved.
 */
class ActivityFeed
{
    /**
     * One page of activity, each row with its user and writing attached
     * (null when there is none, or it was deleted since).
     *
     * @return Paginator<int, array{kind: string, subject_id: int, created_at: Carbon, user: User|null, writing: Writing|null}>
     */
    public function page(int $perPage): Paginator
    {
        $currentPage = Paginator::resolveCurrentPage();
        $offset = ($currentPage - 1) * $perPage;

        // One row past the page tells the paginator whether another page follows
        $rows = $this->newestFirst(DB::query()->fromSub($this->activity($offset + $perPage + 1), 'activity'))
            ->offset($offset)
            ->limit($perPage + 1)
            ->get();

        $users = User::forAuthorSummary()
            ->whereIn('id', $this->idsOf($rows, 'user_id'))
            ->get()
            ->keyBy('id');

        $writings = Writing::select('id', 'title', 'slug')
            ->whereIn('id', $this->idsOf($rows, 'writing_id'))
            ->get()
            ->keyBy('id');

        $activity = $rows->map(fn (stdClass $row): array => [
            'kind' => (string) $row->kind,
            'subject_id' => (int) $row->subject_id,
            'created_at' => Carbon::parse($row->created_at),
            'user' => $users->get($row->user_id),
            'writing' => $writings->get($row->writing_id),
        ]);

        return (new Paginator($activity, $perPage, $currentPage, ['path' => Paginator::resolveCurrentPath()]))
            ->withQueryString();
    }

    /**
     * Every source counted on its own, which its indexes answer, instead of counting the union.
     */
    public function count(): int
    {
        return array_sum(array_map(fn (Builder $source): int => $source->count(), $this->sources()));
    }

    /**
     * The newest `$limit` rows of every source, unioned: no page up to the
     * one being read can hold a row that isn't among its source's newest,
     * so the union never reads a source in full.
     */
    private function activity(int $limit): Builder
    {
        $sources = array_map(fn (Builder $source): Builder => $this->newestFirst($source)->limit($limit), $this->sources());

        return array_reduce(
            array_slice($sources, 1),
            fn (Builder $union, Builder $source): Builder => $union->unionAll($source),
            $sources[0],
        );
    }

    /**
     * The feed's order, by the columns every source selects under the same
     * names. A source joining another table aliases its `created_at`, so the
     * order reads the selected column rather than an ambiguous one.
     */
    private function newestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('created_at')->orderBy('kind')->orderByDesc('subject_id');
    }

    /**
     * @return list<Builder>
     */
    private function sources(): array
    {
        return [
            $this->signUps(),
            $this->writings(),
            $this->comments(),
            $this->likes(),
            $this->bookmarks(),
            $this->writingsOfTheDay(),
            $this->complaints(),
        ];
    }

    private function signUps(): Builder
    {
        return User::query()->toBase()
            ->selectRaw("'joined' as kind, id as subject_id, id as user_id, null as writing_id, created_at")
            ->whereNotNull('created_at');
    }

    private function writings(): Builder
    {
        return Writing::query()->toBase()
            ->selectRaw("'published' as kind, id as subject_id, user_id, id as writing_id, created_at")
            ->whereNotNull('created_at');
    }

    private function comments(): Builder
    {
        return Comment::query()->toBase()
            ->selectRaw("'commented' as kind, id as subject_id, user_id, writing_id, created_at")
            ->whereNotNull('created_at');
    }

    /**
     * Likes of writings and of comments; a comment like points at the commented writing.
     */
    private function likes(): Builder
    {
        return Like::query()->toBase()
            ->leftJoin('comments', function ($join): void {
                $join->on('comments.id', '=', 'likes.likeable_id')
                    ->where('likes.likeable_type', (new Comment)->getMorphClass());
            })
            ->selectRaw(
                'case when likes.likeable_type = ? then ? else ? end as kind, likes.id as subject_id, likes.user_id, '
                .'case when likes.likeable_type = ? then likes.likeable_id else comments.writing_id end as writing_id, likes.created_at as created_at',
                [(new Writing)->getMorphClass(), 'liked_writing', 'liked_comment', (new Writing)->getMorphClass()],
            )
            ->whereNotNull('likes.created_at');
    }

    private function bookmarks(): Builder
    {
        return Shelf::query()->toBase()
            ->selectRaw("'bookmarked' as kind, writing_id as subject_id, user_id, writing_id, created_at")
            ->whereNotNull('created_at');
    }

    private function writingsOfTheDay(): Builder
    {
        return DailySelection::query()->toBase()
            ->selectRaw("'writing_of_the_day' as kind, id as subject_id, null as user_id, writing_id, created_at")
            ->whereNotNull('created_at');
    }

    /**
     * Complaints are anonymous: the row carries the reported user, or the
     * reported writing (the commented one for a comment).
     */
    private function complaints(): Builder
    {
        $writingType = (new Writing)->getMorphClass();
        $commentType = (new Comment)->getMorphClass();
        $userType = (new User)->getMorphClass();

        return Complaint::query()->toBase()
            ->leftJoin('comments', function ($join) use ($commentType): void {
                $join->on('comments.id', '=', 'complaints.complainable_id')
                    ->where('complaints.complainable_type', $commentType);
            })
            ->selectRaw(
                'case complaints.complainable_type when ? then ? when ? then ? else ? end as kind, complaints.id as subject_id, '
                .'case when complaints.complainable_type = ? then complaints.complainable_id end as user_id, '
                .'case when complaints.complainable_type = ? then complaints.complainable_id else comments.writing_id end as writing_id, '
                .'complaints.created_at as created_at',
                [$writingType, 'reported_writing', $commentType, 'reported_comment', 'reported_user', $userType, $writingType],
            )
            ->whereNotNull('complaints.created_at');
    }

    /**
     * @param  Collection<int, stdClass>  $rows
     * @return Collection<int, int>
     */
    private function idsOf(Collection $rows, string $column): Collection
    {
        return $rows->pluck($column)->filter()->unique()->values();
    }
}
