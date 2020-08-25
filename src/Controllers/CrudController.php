<?php

namespace EnamDuaTeknologi\LaravelCrudApi\Controllers;

use App\Http\Controllers\Controller;
use EnamDuaTeknologi\LaravelCrudApi\Traits\Crudable;

class CrudController extends Controller
{
    use Crudable;

    protected $model;
    protected $entities;
    protected $table;

    public function __construct()
    {
        $this->model = self::getModel(request()->route('table'));
        $this->table = request()->route('table');
    }
}
