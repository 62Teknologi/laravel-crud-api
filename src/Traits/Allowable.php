<?php

namespace EnamDuaTeknologi\LaravelCrudApi\Traits;

use Illuminate\Http\Request;

trait Allowable
{
    public $trustable = true;

    public function allows($permission)
    {
        $permission = array_merge(is_array($permission) ? $permission : [$permission], ['*']);

        return session('permissions') && array_intersect($permission, session('permissions'))
            ?: redirect(env('401_URL', env('APP_URL').'/401'))->send();
    }
}
