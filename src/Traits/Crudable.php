<?php

namespace EnamDuaTeknologi\LaravelCrudApi\Traits;

use Illuminate\Support\Facades\DB;
use \Illuminate\Pagination\Paginator;
use EnamDuaTeknologi\LaravelCrudApi\Transformers\FieldTransformer;
use EnamDuaTeknologi\LaravelCrudApi\Models\Crud;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

trait Crudable
{
    public $query;

    // should be in config
    protected $transformerPath = "\\App\\Transformers";

    public function index($table)
    {
        /***** WARNING, FUCK1NG STUPID $H1T CODE BELOW, REMOVE ASAP!!! *******/
        Paginator::currentPageResolver(function () {
            return request('page', 1);
        });
        /********************************************************************/

        $fields = FieldTransformer::transform(
            DB::select('describe ' . $table),
            $table
        );

        foreach (($this->model->options ?? []) as $key => $value) {
            $colums = array_intersect(
                Schema::getColumnListing($value),
                ['id', 'title', 'code', 'description', 'full_name']
            );

            $field = [
                'field' => $value,
                'type' => 'option',
                'data' => DB::select('select '.implode($colums, ',').' from ' . $value),
            ];

            $fields[] = $field;
        }

        $query = $this->query()
            ->setFilter($fields)
            ->setHasBelongFilter()
            ->setSort()
            ->get();

        $transformer = self::getTransformer($this->transformerPath.'\\'.self::toKebabCase($table).'Transformer');

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
            $this->transformerPath.'\\Show\\'.(self::toKebabCase($table)).'Transformer'
        );

        return $return = fractal($this->model->find($id))
            ->transformWith(new $transformer())
            ->toArray();
    }

    public function store($table)
    {
        try {
            if (method_exists($this->model, '_create')) {
                return $this->model->_create(request()->all());
            }

            $data = $this->model->create(request()->all());

            if (method_exists($this->model, '_created')) {
                $this->model->_created($data);
            }

            return $this->show(
                $table,
                $data->id
            );
        } catch (\Exception $e) {
            return  response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update($table, $id)
    {
        try {
            if (method_exists($this->model, '_update')) {
                return $this->model->_update($id, request()->all());
            }

            $data = $this->model->find($id);
            $data->update(request()->all());

            if (method_exists($this->model, '_updated')) {
                $this->model->_updated($data);
            }

            return $this->show(
                $table,
                $id
            );
        } catch (\Exception $e) {
            return  response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy($table, $id)
    {
        try {
            $data = $this->model->find($id);
            $data->delete();

            if (method_exists($this->model, '_deleted')) {
                $this->model->_deleted($data);
            }

            return ['message' => 'Delete Success'];
        } catch (\Exception $e) {
            return  response()->json(['message' => $e->getMessage()], 500);
        }
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

    /**
     * to do : refactor, to decrease redudancy code
     */
    protected function setHasBelongFilter()
    {
        if ($this->model->hasFilters) {
            $table = $this->model->hasFilters[0][0];
            $on = $table.'.'.$this->model->hasFilters[0][1];
            $fields = isset($this->model->hasFilters[0][2])
                ? $this->model->hasFilters[0][2]
                : [];

            if (request()->has($table)) {
                $fields = array_merge_recursive($fields, request($table));

                $this->query = $this->query->join($table, function ($subQuery) use ($on, $fields, $table) {
                    $subQuery = $subQuery->on($on, '=', $this->model->getTable().'.id');
                    
                    foreach ($fields as $key => $value) {
                        $subQuery = $subQuery->where($table.'.'.$key, $value);
                    };

                    return $subQuery;
                });
            }
        }

        if ($this->model->belongFilters) {
            $table = $this->model->belongFilters[0][0];
            $on = $table.'.id';
            $fields = isset($this->model->belongFilters[0][2])
                ? $this->model->belongFilters[0][2]
                : [];

            if (request()->has($table)) {
                $fields = array_merge_recursive($fields, request($table));

                $this->query = $this->query->join($table, function ($subQuery) use ($on, $fields, $table) {
                    $subQuery = $subQuery->on($on, '=', $this->model->getTable().'.'.$this->model->belongFilters[0][1]);
                    
                    foreach ($fields as $key => $value) {
                        $subQuery = $subQuery->where($table.'.'.$key, $value);
                    };

                    return $subQuery;
                });
            }
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
            : '\\EnamDuaTeknologi\\LaravelCrudApi\\Transformers\\CrudTransformer';
    }

    /**
     * todo : should be helper
     */
    protected static function getModel($tableName)
    {
        $tableName = self::toKebabCase($tableName);
        $entities = '\\App\\Entities\\'.($tableName);
        $models = '\\App\\Models\\'.($tableName);

        return class_exists($models)
            ? (new $models)
            : ( class_exists($entities)
                ? (new $entities)
                : (new Crud())->setTable($tableName));
    }

    /**
     * todo : should be helper
     */
    protected static function toKebabCase($string)
    {
        return Str::singular(str_replace('_', '', ucwords($string, '_')));
    }
}
