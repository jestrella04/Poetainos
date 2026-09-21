<?php

namespace App\Notifications;

use App\Models\Category;

class CategoryFeaturedRandom extends SocialPostNotification
{
    public function __construct(protected Category $category)
    {
        $this->message = __('Discover all the beauty we have for you under the ":category" category.', [
            'category' => $this->category->name,
        ]);
        $this->url = $this->category->path();
    }
}
