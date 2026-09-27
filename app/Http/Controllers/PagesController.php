<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PagesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('pages/PoPagesIndex', [
            'meta' => [
                'title' => getPageTitle([__('Pages')]),
            ],
            'pages' => Page::all(),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Page $page): Response
    {
        $page->text = interpolateSiteSettings($page->text);

        return Inertia::render('pages/PoPagesShow', [
            'meta' => [
                'title' => getPageTitle([$page->title]),
            ],
            'page' => $page,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return array{message: string, id: int}
     */
    public function store(): array
    {
        $page = new Page;

        $this->validatePage($page);

        $page->slug = slugify($page->getTable(), request('title'));

        return $this->save($page, __('Page created successfully'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return array{message: string, id: int}
     */
    public function update(Page $page): array
    {
        $this->validatePage($page);

        return $this->save($page, __('Page updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return array<string, string>
     */
    public function destroy(Page $page): array
    {
        $page->delete();

        return [
            'message' => __('Page deleted successfully'),
        ];
    }

    private function validatePage(Page $page): void
    {
        request()->validate([
            'title' => ['required', 'string', Rule::unique(Page::class)->ignore($page), 'min:3', 'max:40'],
            'text' => 'required|string|min:100',
        ]);
    }

    /**
     * @return array{message: string, id: int}
     */
    private function save(Page $page, string $message): array
    {
        $page->title = request('title');
        $page->text = request('text');
        $page->save();

        return [
            'message' => $message,
            'id' => $page->id,
        ];
    }
}
