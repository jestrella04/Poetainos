<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\RedirectResponse;
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
     * Store a newly created resource in storage, then return to the admin table.
     */
    public function store(): RedirectResponse
    {
        $page = new Page;

        $this->validatePage($page);

        retryOnSlugCollision(function () use ($page): void {
            $page->slug = slugify($page->getTable(), request('title'));
            $this->save($page);
        });

        Inertia::flash(['message' => 'pages.page-created', 'color' => 'success']);

        return back();
    }

    /**
     * Update the specified resource in storage, then return to the admin table.
     */
    public function update(Page $page): RedirectResponse
    {
        $this->validatePage($page);
        $this->save($page);

        Inertia::flash(['message' => 'pages.page-updated', 'color' => 'success']);

        return back();
    }

    /**
     * Remove the specified resource from storage, then return to the admin table.
     */
    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        Inertia::flash(['message' => 'pages.page-deleted', 'color' => 'success']);

        return back();
    }

    private function validatePage(Page $page): void
    {
        request()->validate([
            'title' => ['required', 'string', Rule::unique(Page::class)->ignore($page), 'min:3', 'max:40'],
            'text' => 'required|string|min:100',
        ]);
    }

    private function save(Page $page): void
    {
        $page->title = request('title');
        $page->text = request('text');
        $page->save();
    }
}
