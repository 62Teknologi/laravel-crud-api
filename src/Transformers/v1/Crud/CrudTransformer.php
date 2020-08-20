<?php

namespace EnamDuaTeknologi\LaravelCrudApi\Transformers\v1\Crud;

use EnamDuaTeknologi\LaravelCrudApi\Models\Crud;
use League\Fractal\TransformerAbstract;
use Illuminate\Support\Facades\Schema;

class CrudTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        $table = $model->getTable();
        $return = $model->toArray();

        foreach ($return as $key => $value) {
            if ($key == 'parent_id') {
                $return = self::setRelation($return, $table, 'parent');
            } elseif (strpos($key, '_id')) {
                $objName = str_replace('_id', '', $key);
                $return = self::setRelation($return, $objName);
            }
        }

        return $return;
    }

    protected static function setRelation($return, $objName, $fieldName = null)
    {
        $tableName = (!$fieldName)
            ? $objName.'s'
            : $objName;

        $fields = $result = array_intersect(
            Schema::getColumnListing($tableName),
            ['id', 'code', 'description', 'full_name']
        );
        
        if (!empty($fields)) {
            $fieldName = $fieldName ?? $objName;

            $return[$fieldName] = (new Crud())->setTable($tableName)
                ->select($fields)
                ->find($return[$fieldName.'_id']);
        }

        return $return;
    }
}
