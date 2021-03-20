<?php

namespace EnamDuaTeknologi\LaravelCrudApi\Traits;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

trait Loggable
{
    public $loggable = true;
    
    public function setUpdatedByAttribute()
    {
        request()->request->add(['updated_by' => (Auth::user()) ? Auth::user()->id : 0]);
        $this->attributes['updated_by'] = request('updated_by');
    }

    public function getUpdatedByAttribute($value)
    {
        return User::select('id', 'username')->find($this->attributes['updated_by']);
    }
}
