<?php

namespace SkyaTura\EloquentREST\Helpers;

class ParamHelper {
    public static function commaFields($input, $options = []){
        $default = [
            "trim" => true,
            "unique" => true,
            "filter" => true,
        ];
        $options = array_merge($default, $options);

        $fields = explode(',', $input);

        if($options['trim'])
            $fields = array_map('trim',$fields);
        if($options['unique'])
            $fields = array_unique($fields);
        if($options['filter'])
            $fields = array_filter($fields);

        return $fields;
    }

    public static function str_starts_with($query, $string){
        return (substr($string, 0, strlen($query)) === $query);
    }
}