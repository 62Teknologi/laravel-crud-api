<?php

namespace EnamDuaTeknologi\LaravelCrudApi\Transformers;

use EnamDuaTeknologi\LaravelCrudApi\Models\Crud;
use Illuminate\Support\Facades\Schema;

class FieldTransformer
{
    public static function transform($array, $table)
    {
        return array_map(function ($value) use ($table) {
            $type = explode(" ", str_replace(["(", ")"], " ", $value->Type));

            $return = [
                'field' => $value->Field,
                'type' => $type[0]
            ];

            if ($value->Field == 'parent_id' && request()->has('set_relation')) {
                $return = self::setRelation($return, $table, 'parent');
            } elseif ($value->Field == 'password') {
                $return['type'] = $value->Field;
                $return['table_hidden'] = true;
            } elseif ($value->Field == 'image' || strpos($value->Field, '_image')) {
                $return['type'] = 'image';
                $return['table_hidden'] = true;
            } elseif (strpos($value->Field, '_id') && request()->has('set_relation')) {
                $objName = str_replace('_id', '', $value->Field);
                $return = self::setRelation($return, $objName);
            }

            if (isset($type[1])) {
                $return['length'] = $type[1];
            }

            if ($value->Default) {
                $return['default'] = $value->Default;
            }

            if ($value->Null == "YES") {
                $return['nullable'] = true;
            }

            if ($value->Key == 'PRI' || $value->Field == 'created_at') {
                $return['form_hidden']
                = $return['table_hidden']
                = true;
            }

            if ($value->Field == 'updated_at') {
                $return['form_hidden'] = true;
            }


            return $return;
        }, $array);
    }

    protected static function setRelation($return, $objName, $fieldName = null)
    {
        $tableName = (!$fieldName)
            ? $objName.'s'
            : $objName;

        $fields = $result = array_intersect(
            Schema::getColumnListing($tableName),
            ['id', 'title', 'code', 'description', 'full_name']
        );

        if (!empty($fields)) {
            $fieldName = $fieldName ?? $objName;

            $return['relation'] = [
                'field' => $fieldName,
                'data' => (new Crud())->setTable($tableName)
                    ->select($fields)
                    ->get()
            ];

            $return['type'] = 'relation';
        }

        return $return;
    }
}
