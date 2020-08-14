<?php

namespace EnamDuaTeknologi\LaravelCrudApi\Transformers\v1\Crud;

use League\Fractal\TransformerAbstract;
use App\Models\Crud;

class CrudTransformer extends TransformerAbstract
{
    public function transform($model)
    {
    	$model = $model->toArray();

    	foreach ($model as $key => $value) {
    		if(strpos($key, '_id')) {
    			$objName = str_replace('_id', '', $key);

    			$model[$objName] = (new Crud())->setTable($objName.'s')
		            ->select('id', 'code', 'description')
		            ->find($model[$key])
		            ->toArray();
    		}
    	}

        return $model;
    }
}
