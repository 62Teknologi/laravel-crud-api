<?php

namespace EnamDuaTeknologi\LaravelCrudApi\Models;

use Illuminate\Database\Eloquent\Model;

class Crud extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    public function setGuarded($values) {
    	$this->guarded = $values;
    	return $this;
    }

    public function getGuarded() {
    	return $this->guarded;
    }
}
