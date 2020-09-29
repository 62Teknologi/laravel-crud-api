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
                $return = self::getRelation($return, $table, 'parent');
            } elseif (strpos($key, '_id')) {
                $objName = str_replace('_id', '', $key);
                $return = self::getRelation($return, $objName);
            }
        }

        return $return;
    }

    protected static function getRelation($return, $objName, $fieldName = null)
    {
        $tableName = (!$fieldName)
            ? $objName.'s'
            : $objName;

        // to do : check intersect from entities
        $fields = $result = array_intersect(
            Schema::getColumnListing($tableName),
            ['id', 'title', 'code', 'description', 'full_name']
        );
        
        if (!empty($fields)) {
            $fieldName = $fieldName ?? $objName;

            $return[$fieldName] = $return[$fieldName] ?? self::getModel($tableName)
                ->select($fields)
                ->find($return[$fieldName.'_id']);
        }

        return $return;
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
