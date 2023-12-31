<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\User;

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
        $this->blockedUsers = auth()->check() ? User::find(auth()->user()->id)->getBlockedAuthors()->pluck('blocked_user_id') : [0];
    }
}
