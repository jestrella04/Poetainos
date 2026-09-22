<?php

namespace Tests\Browser\Pages;

use App\Models\Category;
use App\Models\Writing;

class WritingFormPage extends Page
{
    public const TITLE_INPUT = '#writing-title';

    public const MAIN_CATEGORY_SELECT = '#writing-main-category';

    public const ALT_CATEGORIES_SELECT = '#writing-alt-categories';

    public const TEXT_INPUT = '#writing-text';

    public const TERMS_SWITCH = '#agreement-terms';

    public const PRIVACY_SWITCH = '#agreement-privacy';

    public const SUBMIT_BUTTON = '#writing-submit';

    public const DELETE_BUTTON = '#writing-delete';

    public const CONFIRM_DELETE_BUTTON = '#writing-delete-submit';

    public const SUCCESS_ALERT = '#writing-alert';

    public static function openCreate(): self
    {
        return new self(static::visitUrl(route('writings.create')));
    }

    public static function openEdit(Writing $writing): self
    {
        return new self(static::visitUrl(route('writings.edit', $writing->slug)));
    }

    public static function mainCategoryOption(Category $category): string
    {
        return "#main-category-option-{$category->id}";
    }

    public static function altCategoryOption(Category $category): string
    {
        return "#alt-category-option-{$category->id}";
    }

    public function fillTitle(string $title): static
    {
        $this->browser->type(self::TITLE_INPUT, $title);

        return $this;
    }

    public function selectMainCategory(Category $category): static
    {
        $this->browser->click(self::MAIN_CATEGORY_SELECT)
            ->click(self::mainCategoryOption($category));

        return $this;
    }

    public function selectAltCategory(Category $category): static
    {
        $this->browser->click(self::ALT_CATEGORIES_SELECT)
            ->click(self::altCategoryOption($category));

        return $this;
    }

    public function fillText(string $text): static
    {
        $this->browser->type(self::TEXT_INPUT, $text);

        return $this;
    }

    public function acceptAgreements(): static
    {
        $this->browser->check(self::TERMS_SWITCH)
            ->check(self::PRIVACY_SWITCH);

        return $this;
    }

    public function submit(): static
    {
        $this->browser->click(self::SUBMIT_BUTTON);

        return $this;
    }

    public function deleteWriting(): static
    {
        $this->browser->click(self::DELETE_BUTTON)
            ->click(self::CONFIRM_DELETE_BUTTON);

        return $this;
    }
}
