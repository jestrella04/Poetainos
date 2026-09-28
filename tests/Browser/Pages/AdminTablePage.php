<?php

namespace Tests\Browser\Pages;

/**
 * One of the admin tables (categories, pages, tags, users, writings,
 * complaints) and the dialogs it opens for its rows.
 */
class AdminTablePage extends Page
{
    public const DELETE_PASSWORD_INPUT = '#admin-delete-password';

    public const CONFIRM_DELETE_BUTTON = '#admin-delete-submit';

    public const CREATE_CATEGORY_BUTTON = '#admin-category-create';

    public const CATEGORY_NAME_INPUT = '#admin-category-name';

    public const CATEGORY_DESCRIPTION_INPUT = '#admin-category-description';

    public const CATEGORY_SUBMIT_BUTTON = '#admin-category-submit';

    public const PAGE_TITLE_INPUT = '#admin-page-title';

    public const PAGE_SUBMIT_BUTTON = '#admin-page-submit';

    public const COMPLAINT_NOTE_INPUT = '#admin-complaint-closing-note';

    public const COMPLAINT_CLOSE_BUTTON = '#admin-complaint-close-submit';

    public static function open(string $routeName): self
    {
        return new self(static::visitUrl(route($routeName)));
    }

    public static function deleteButton(int $rowId): string
    {
        return "#admin-delete-{$rowId}";
    }

    public static function editButton(int $rowId): string
    {
        return "#admin-edit-{$rowId}";
    }

    public static function complaintButton(int $complaintId): string
    {
        return "#admin-complaint-{$complaintId}";
    }

    /**
     * The Spanish text of a frontend i18n key, as the snackbar shows it.
     */
    public static function message(string $key): string
    {
        $messages = json_decode((string) file_get_contents(resource_path('js/i18n/es.json')), true);

        return (string) data_get($messages, $key);
    }

    public function deleteRow(int $rowId, ?string $password = null): static
    {
        $this->browser->click(self::deleteButton($rowId));

        if ($password !== null) {
            $this->browser->type(self::DELETE_PASSWORD_INPUT, $password);
        }

        $this->browser->click(self::CONFIRM_DELETE_BUTTON);

        return $this;
    }

    public function createCategory(string $name, string $description): static
    {
        $this->browser->click(self::CREATE_CATEGORY_BUTTON)
            ->type(self::CATEGORY_NAME_INPUT, $name)
            ->type(self::CATEGORY_DESCRIPTION_INPUT, $description)
            ->click(self::CATEGORY_SUBMIT_BUTTON);

        return $this;
    }

    public function renamePage(int $pageId, string $title): static
    {
        $this->browser->click(self::editButton($pageId))
            ->clear(self::PAGE_TITLE_INPUT)
            ->type(self::PAGE_TITLE_INPUT, $title)
            ->click(self::PAGE_SUBMIT_BUTTON);

        return $this;
    }

    public function closeComplaint(int $complaintId, string $note): static
    {
        $this->browser->click(self::complaintButton($complaintId))
            ->type(self::COMPLAINT_NOTE_INPUT, $note)
            ->click(self::COMPLAINT_CLOSE_BUTTON);

        return $this;
    }
}
