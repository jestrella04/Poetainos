<?php

namespace App\Http\Controllers;

use App\Jobs\RecalculateAura;
use App\Models\Category;
use App\Models\DailySelection;
use App\Models\Tag;
use App\Models\User;
use App\Models\Writing;
use App\Services\ContentDeleter;
use App\Services\ViewCounter;
use App\Services\WritingPublisher;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class WritingsController extends Controller
{
    private const HOME_AUTHORS_SHOWN = 4;

    private const HOME_TAGS_SHOWN = 6;

    private const LIKERS_SHOWN = 5;

    private const RELATED_SHOWN = 5;

    /**
     * The home page: the newest writings, the pick of the day, and the writers and tags to discover.
     *
     * @return Response|Paginator<int, Writing>
     */
    public function home(): Response|Paginator
    {
        return $this->listing(
            Writing::query(),
            ['title' => getPageTitle([]), 'canonical' => route('home')],
            [
                'isHome' => true,
                'pickOfTheDay' => fn (): ?Writing => DailySelection::current()?->visibleWriting($this->blockedAuthorIds()),
                'authors' => fn () => User::forAuthorSummary(withKarma: true)
                    ->withCount('writings')
                    ->ranked()
                    ->take(self::HOME_AUTHORS_SHOWN)
                    ->get(),
                'tags' => fn () => Tag::withCount('writings')
                    ->orderByDesc('writings_count')
                    ->has('writings')
                    ->take(self::HOME_TAGS_SHOWN)
                    ->get(),
            ],
        );
    }

    /**
     * The writings awarded a Golden Flower.
     *
     * @return Response|Paginator<int, Writing>
     */
    public function awards(): Response|Paginator
    {
        return $this->listing(
            Writing::whereNotNull('home_posted_at'),
            ['title' => getPageTitle([__('Golden Flowers')]), 'canonical' => route('writings.awards')],
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return $this->form(new Writing, __('Publish a writing'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return array<string, string>
     */
    public function store(Request $request, WritingPublisher $publisher): array
    {
        $user = $this->requireAuthUser();

        $publisher->ensureBelowDailyPostLimit($user);
        $request->validate($this->rules($user));

        $writing = $publisher->create($user, $this->formInput($request), $this->uploadedCover($request));

        RecalculateAura::dispatch($user);
        $this->rememberAgreements($request, $user);

        return [
            'url' => $writing->path(),
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(Writing $writing, ViewCounter $viewCounter): Response
    {
        $viewCounter->count($writing);

        $writing->loadCount(['likes', 'comments', 'shelf'])->loadExists(Writing::viewerReactions())->load([
            'author' => fn ($query) => $query->forAuthorSummary(withKarma: true),
            'categories:id,name,slug',
            'tags:id,name,slug',
        ]);

        $user = Auth::user();

        return Inertia::render('writings/PoWritingsShow', [
            'meta' => [
                'title' => getPageTitle([
                    $writing->title,
                    $writing->author?->getName() ?? '',
                ]),
                'canonical' => $writing->path(),
                'description' => $writing->excerpt(),
                'image' => $writing->coverUrl(),
            ],
            'writing' => $writing,
            'likers' => $writing->likers(self::LIKERS_SHOWN),
            'related' => [
                'from_author' => randomSample(
                    Writing::whereNot('id', $writing->id)->where('user_id', $writing->user_id),
                    self::RELATED_SHOWN,
                ),
                'from_category' => randomWritingsWithAuthor(
                    Writing::whereNot('id', $writing->id)
                        ->visibleTo($this->blockedAuthorIds())
                        ->whereIn(
                            'id',
                            DB::table('category_writing')
                                ->select('writing_id')
                                ->whereIn('category_id', $writing->categories->modelKeys())
                        ),
                    self::RELATED_SHOWN,
                ),
            ],
            'isAuthorBlocked' => $user !== null && $writing->author !== null ? $user->isAuthorBlocked($writing->author) : false,
        ]);
    }

    /**
     * Display a random resource.
     */
    public function random(): RedirectResponse|Redirector
    {
        return redirect(Writing::randomByRandomAuthor()->path());
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Writing $writing): Response
    {
        $this->authorize('update', $writing);

        return $this->form($writing, __('Update writing'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return array<string, string>
     */
    public function update(Request $request, Writing $writing, WritingPublisher $publisher): array
    {
        $this->authorize('update', $writing);

        $agreeingUser = $this->agreeingUser($writing);
        $request->validate($this->rules($agreeingUser));

        $publisher->update($writing, $this->formInput($request), $this->uploadedCover($request));

        RecalculateAura::dispatch($writing->author);
        $this->rememberAgreements($request, $agreeingUser);

        return [
            'url' => $writing->path(),
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return array<int, mixed>
     */
    public function destroy(Writing $writing, ContentDeleter $deleter): array
    {
        $this->authorize('delete', $writing);

        $deleter->deleteWriting($writing);

        return [];
    }

    /**
     * The writing form page, for a new writing or for editing an existing one.
     */
    private function form(Writing $writing, string $title): Response
    {
        $mainCategories = Category::select('id', 'name')
            ->whereNull('parent_id')
            ->with('descendants')
            ->get();

        return Inertia::render('writings/PoWritingsForm', [
            'meta' => [
                'title' => getPageTitle([$title]),
            ],
            'writing' => [
                'data' => $writing,
                'main_category' => $writing->exists ? $writing->mainCategory()->value('id') : null,
                'categories' => $writing->exists ? $writing->altCategories()->pluck('id') : [],
                'tags' => $writing->exists ? $writing->tags()->pluck('name') : null,

            ],
            'isUpdate' => $writing->exists,
            'main_categories' => $mainCategories,
            'max-file-size' => getSiteConfig('uploads_max_file_size'),
            // Nobody else can accept the agreements for the author, so the form only asks the author
            'agreement' => $this->agreeingUser($writing)?->isInAgreement() ?? true,
        ]);
    }

    /**
     * A page of the given writings, hidden authors excluded, sorted by the requested order.
     *
     * @param  Builder<Writing>  $writings
     * @param  array<string, mixed>  $meta
     * @param  array<string, mixed>  $extraProps
     * @return Response|Paginator<int, Writing>
     */
    private function listing(Builder $writings, array $meta, array $extraProps = []): Response|Paginator
    {
        $sort = resolveSort(['latest', 'popular', 'likes']);

        return $this->writingsIndex(
            $writings->visibleTo($this->blockedAuthorIds())->withListingRelations()->sorted($sort),
            $sort,
            $meta,
            $extraProps,
        );
    }

    /**
     * The user whose agreements the writing form records: whoever publishes a
     * new writing, or the author editing their own. Nobody, when someone else
     * (an admin) edits it.
     */
    private function agreeingUser(Writing $writing): ?User
    {
        $user = $this->requireAuthUser();

        return $writing->exists === false || $writing->author?->is($user) === true ? $user : null;
    }

    /**
     * The validation rules of the writing form. The form posts unchecked
     * agreements even when the user already accepted them, so those are only
     * required until then.
     *
     * @return array<string, mixed>
     */
    private function rules(?User $agreeingUser): array
    {
        $agreementRules = $agreeingUser === null || $agreeingUser->isInAgreement() ? [] : [
            'service_agreement' => 'sometimes|required|accepted',
            'privacy_agreement' => 'sometimes|required|accepted',
        ];

        return [
            'title' => 'required|string|min:3|max:100',
            'main_category' => ['required', 'integer', Rule::exists('categories', 'id')->whereNull('parent_id')],
            'categories' => 'required|array|exists:categories,id|max:2',
            'text' => 'required|string|min:10|max:4000',
            'tags' => 'nullable|array|max:'.WritingPublisher::MAX_TAGS,
            'tags.*' => 'string|min:1|max:'.WritingPublisher::MAX_TAG_LENGTH,
            'link' => 'nullable|url|max:250',
            'cover' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:'.getSiteConfig('uploads_max_file_size'),
            ...$agreementRules,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formInput(Request $request): array
    {
        return [
            'title' => $request->input('title'),
            'text' => $request->input('text'),
            'link' => $request->input('link'),
            'main_category' => $request->input('main_category'),
            'categories' => $request->input('categories'),
            'tags' => $request->input('tags'),
        ];
    }

    private function uploadedCover(Request $request): ?UploadedFile
    {
        $cover = $request->file('cover');

        return $cover instanceof UploadedFile ? $cover : null;
    }

    /**
     * Persist the user agreements so they aren't asked again.
     */
    private function rememberAgreements(Request $request, ?User $user): void
    {
        if (isTruthy($request->input('service_agreement')) && isTruthy($request->input('privacy_agreement'))) {
            $user?->acceptAgreements();
        }
    }
}
