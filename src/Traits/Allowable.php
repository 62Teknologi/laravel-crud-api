<?php

namespace EnamDuaTeknologi\LaravelCrudApi\Traits;

use Illuminate\Http\Request;

trait Allowable
{
    public $trustable = true;

    public function allows($permission)
    {
        $permission = array_merge(is_array($permission) ? $permission : [$permission], ['*']);

        return array_intersect($permission, session('permissions'))
            ?: redirect(env('APP_URL').'/admin/401')->send();
    }
}
