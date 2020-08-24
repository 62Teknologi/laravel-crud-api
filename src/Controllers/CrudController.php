<?php

namespace EnamDuaTeknologi\LaravelCrudApi\Controllers;

use App\Http\Controllers\Controller;
use EnamDuaTeknologi\LaravelCrudApi\Models\Crud;
use EnamDuaTeknologi\LaravelCrudApi\Traits\Crudable;
use Illuminate\Http\Request;

class CrudController extends Controller
{
    use Crudable;

    protected $model;
    protected $entities;
    protected $table;

    public function __construct(Request $request)
    {
        $this->model = self::getModel($request->route('table'));
        $this->table = $request->route('table');
    }
}
