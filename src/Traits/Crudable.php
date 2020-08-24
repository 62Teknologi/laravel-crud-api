<?php

namespace EnamDuaTeknologi\LaravelCrudApi\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use EnamDuaTeknologi\LaravelCrudApi\Transformers\v1\Crud;
use \Illuminate\Pagination\Paginator;
use App\Entities\Afdeling;

trait Crudable
{
    public $query;

    public function index($table)
    {
        /***** WARNING, FUCK1NG STUPID $H1T CODE BELOW, REMOVE ASAP!!! *******/
        Paginator::currentPageResolver(function () {
            return request('page', 1);
        });
        /********************************************************************/

        $fields = Crud\FieldTransformer::transform(
            DB::select('describe ' . $table),
            $table
        );

        $query = $this->query()
            ->setFilter($fields)
            ->setHasFilter()
            ->setSort()
            ->get();

        $transformer = self::getTransformer('Crud\\'.(self::toKebabCase($table)).'Transformer');

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

    protected function query()
    {
        //$this->query = DB::table($this->model->getTable());
        $this->query = $this->model->select($this->model->getTable().'.*');
        return $this;
    }

    protected function get()
    {
        return $this->query;
    }

    protected function setFilter($fields)
    {
        if (request()->has('search') && request('search')) {
            $this->query = $this->query->where(function ($subQuery) use ($fields) {
                array_map(function ($field) use (&$subQuery) {
                    if ($field['type'] == 'varchar' || $field['type'] == 'longtext') {
                        $subQuery = $subQuery->orWhere($this->table.'.'.$field['field'], 'like', '%'.request('search').'%');
                    }
                }, $fields);

                return $subQuery;
            });
        }

        array_map(function ($field) {
            if (request()->has($field['field']) && request($field['field']) && ($field['type'] == 'int' || $field['type'] == 'relation')) {
                $this->query = $this->query->whereIn($this->table.'.'.$field['field'], explode(',', request($field['field'])));
            }
        }, $fields);

        return $this;
    }

    protected function setHasFilter()
    {
        if ($this->model->hasFilters) {
            $table = $this->model->hasFilters[0][0];
            $on = $table.'.'.$this->model->hasFilters[0][1];
            $fields = $this->model->hasFilters[0][2];

            $this->query = $this->query->join($table, function ($subQuery) use ($on, $fields, $table) {
                $subQuery = $subQuery->on($on, '=', $this->model->getTable().'.id');
                
                array_map(function ($field) use (&$subQuery, $table) {
                    $field[1] = ($field[1] == '?') ? request('has_'.$table.'_'.$field[0]) : $field[1];

                    if ($field[1]) {
                        $subQuery = $subQuery->where($table.'.'.$field[0], $field[1]);
                    }
                }, $fields);

                return $subQuery;
            });
        }

        return $this;
    }

    protected function setSort()
    {
        // should be private setSort()
        $desc = request()->has('desc')
            ? 'desc'
            : 'asc';

        $this->query = (request('sort')) 
            ? $this->query->orderBy(request('sort'), $desc)
            : $this->query;

        return $this;
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
