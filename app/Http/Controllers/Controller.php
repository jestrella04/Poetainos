<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $pagination;

    protected $aura;

    protected $auraHome;

    protected $blockedUsers;

    public function __construct()
    {
        $this->pagination = getSiteConfig('pagination');
        $this->auraHome = getSiteConfig('aura.min_at_home');
    }

    public function getBlockedUsers()
    {
        $blockedUsers = auth()->check()
            ? auth()->user()->blockedAuthors()->pluck('blocked_user_id')->toArray()
            : [0];

        return $blockedUsers;
    }
}
