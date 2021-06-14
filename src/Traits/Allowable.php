<?php

namespace EnamDuaTeknologi\LaravelCrudApi\Traits;

use Illuminate\Http\Request;

trait Allowable
{
    public $allowable = true;

    public function allows($permission)
    {
        $permission = array_merge(is_array($permission) ? $permission : [$permission], ['*']);

        return session('permissions') && array_intersect($permission, session('permissions'))
            ?: redirect(config('app.401_url', env('APP_URL').'/401'))->send();
    }
}
