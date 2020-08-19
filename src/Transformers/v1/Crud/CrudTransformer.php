<?php

namespace EnamDuaTeknologi\LaravelCrudApi\Transformers\v1\Crud;

use League\Fractal\TransformerAbstract;
use App\Models\Crud;

class CrudTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        $table = $model->getTable();
        $model = $model->toArray();

        foreach ($model as $key => $value) {
            if($key == 'parent_id') {
                $objName = str_replace('_id', '', $key);
                
                $model[$objName] = (new Crud())->setTable($table)
                    ->select('id', 'code', 'description')
                    ->find($model[$key]);
            }elseif(strpos($key, '_id')) {
                $objName = str_replace('_id', '', $key);
                
                $model[$objName] = (new Crud())->setTable($objName.'s')
                    ->select('id', 'code', 'description')
                    ->find($model[$key]);
            }
        }

        return $model;
    }
}