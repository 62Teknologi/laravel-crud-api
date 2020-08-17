<?php

namespace EnamDuaTeknologi\LaravelCrudApi\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use EnamDuaTeknologi\LaravelCrudApi\Transformers\v1\Crud;

trait Crudable
{
    static $transformer;

    public function index(Request $request)
    {
        $fields = Crud\FieldTransformer::transform(
            DB::select('describe ' . $this->model->getTable()),
            $this->model->getTable()
        );

        $query = $this->model;

        $transformer = self::setTransformer(
            'Crud\\'.(self::toKebabCase($this->model->getTable())).'Transformer'
        );

        // should be private setFilter()
        if ($request->search) {
            $query = $query->where(function ($subQuery) use(&$request,  $fields) {
                array_map(function($field) use(&$request, &$subQuery) {
                    if($field['type'] == 'varchar' || $field['type'] == 'longtext') $subQuery = $subQuery->orWhere($field['field'], 'like', '%'.$request->search.'%');
                }, $fields);

                return $subQuery;
            });

            $query = $query->where(function ($subQuery) use(&$request,  $fields) {
                array_map(function($field) use(&$request, &$subQuery) {
                    if($request->has($field['field']) && $field['type'] == 'relation') $subQuery = $subQuery->orWhereIn($field['field'], explode(',', $request->input($field['field'])));
                }, $fields);

                return $subQuery;
            });
        }

        // should be private setSort()
        $desc = $request->has('desc')
            ? $request->desc
            : 'asc';

        $sort = ($request->sort)
            ? $this->model->orderBy('name', 'desc')
            : $this->model;

        // paginate default should accept params
        $return = fractal($query->paginate(15))
            ->transformWith(new $transformer)
            ->toArray();

        $return['fields'] = $fields;

        $return['message'] = 'Success';

        return $return;
    }

    public function show($id)
    {
        $transformer = self::setTransformer(
            'Crud\\Show\\'.(self::toKebabCase($this->model->getTable())).'Transformer'
        );

        return $return = fractal($this->model->find($id))
            ->transformWith(new $transformer())
            ->toArray();
    }

    public function store(Request $request)
    {
        return $this->show(
            $this->model->create($request->all())->id
        );
    }

    public function update(Request $request, $id)
    {
        $this->model->find($id)->update($request->all());
        return $this->show($id);
    }

    public function destroy($id)
    {
        $this->model->find($id)->delete();
        return ['message' => 'Delete Success'];
    }

    /**
     * todo : should be helper
     */
     private static function setTransformer($string) {
        return class_exists($string)
            ? $string
            : 'EnamDuaTeknologi\\LaravelCrudApi\\Transformers\\v1\\Crud\\CrudTransformer';
    }

    /**
     * todo : should be helper
     */
     private static function toKebabCase($string) {
        return rtrim(str_replace('_', '', ucwords($string, '_')), 's');
    }
}
