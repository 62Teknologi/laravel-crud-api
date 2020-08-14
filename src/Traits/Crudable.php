<?php

namespace EnamDuaTeknologi\Crud\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use EnamDuaTeknologi\Crud\Transformers\v1\Crud;

trait Crudable
{
    static $transformer;

    public function index()
    {
        $transformer = self::setTransformer(
            'Crud\\'.(self::toKebabCase($this->model->getTable())).'Transformer'
        );

        $return = fractal($this->model->paginate(15))
            ->transformWith(new $transformer)
            ->toArray();

        $return['fields'] = Crud\FieldTransformer::transform(
            DB::select('describe ' . $this->model->getTable())
        );

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
            : 'EnamDuaTeknologi\\Crud\\Transformers\\v1\\Crud\\CrudTransformer';
    }

    /**
     * todo : should be helper
     */
     private static function toKebabCase($string) {
        return rtrim(str_replace('_', '', ucwords($string, '_')), 's');
    }
}
