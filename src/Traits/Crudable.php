<?php

namespace EnamDuaTeknologi\LaravelCrudApi\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use EnamDuaTeknologi\LaravelCrudApi\Transformers\v1\Crud;
use \Illuminate\Pagination\Paginator;
use App\Entities\Afdeling;

trait Crudable
{
    public function index($table)
    {
        ;
        /***** WARNING, FUCK1NG STUPID $H1T CODE BELOW, REMOVE ASAP!!! *******/
        Paginator::currentPageResolver(function () {
            return request('page', 1);
        });
        /********************************************************************/

        $fields = Crud\FieldTransformer::transform(
            DB::select('describe ' . $table),
            $table
        );

        $query = $this->model;

        $transformer = self::getTransformer('Crud\\'.(self::toKebabCase($table)).'Transformer');

        // should be private setFilter()
        if (request()->has('search') && request('search')) {
            $query = $query->where(function ($subQuery) use ($fields) {
                array_map(function ($field) use (&$subQuery) {
                    if ($field['type'] == 'varchar' || $field['type'] == 'longtext') {
                        $subQuery = $subQuery->orWhere($field['field'], 'like', '%'.request('search').'%');
                    }
                }, $fields);

                return $subQuery;
            });
        }

        // should be private setFilter()
        array_map(function ($field) use (&$query) {
            if (request()->has($field['field']) && $field['type'] == 'relation' && request($field['field'])) {
                $query = $query->whereIn($field['field'], explode(',', request($field['field'])));
            }
        }, $fields);

        // should be private setSort()
        $desc = request()->has('desc')
            ? 'desc'
            : 'asc';

        $query = (request('sort'))
            ? $query->orderBy(request('sort'), $desc)
            : $query;

        $return = fractal($query->paginate(request('per_page', 15)))
            ->transformWith(new $transformer)
            ->toArray();

        $return['fields'] = $fields;

        $return['message'] = 'Success';

        return $return;
    }

    public function show($table, $id)
    {
        $transformer = self::getTransformer(
            '\\Crud\\Show\\'.(self::toKebabCase($table)).'Transformer'
        );

        return $return = fractal($this->model->find($id))
            ->transformWith(new $transformer())
            ->toArray();
    }

    public function store($table)
    {
        return $this->show(
            $table,
            $this->model->create(request()->all())->id
        );
    }

    public function update($table, $id)
    {
        $this->model->find($id)->update(request()->all());
        return $this->show(
            $table,
            $id
        );
    }

    public function destroy($table, $id)
    {
        $this->model->find($id)->delete();
        return ['message' => 'Delete Success'];
    }

    /**
     * todo : should be helper
     */
    protected static function getTransformer($string)
    {
        return class_exists($string)
            ? $string
            : '\\EnamDuaTeknologi\\LaravelCrudApi\\Transformers\\v1\\Crud\\CrudTransformer';
    }

    /**
     * todo : should be helper
     */
    protected static function getModel($tableName)
    {
        $class = '\\App\\Entities\\'.(self::toKebabCase($tableName));

        return class_exists($class)
            ? (new $class)
            : (new Crud())->setTable($tableName);
    }

    /**
     * todo : should be helper
     */
    protected static function toKebabCase($string)
    {
        return rtrim(str_replace('_', '', ucwords($string, '_')), 's');
    }
}
