<?php

namespace SkyaTura\EloquentREST\Classes;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Routing\Controller;

class ResponseDataClass
{
    protected $meta;
    protected $data;
    protected $errors;

    public function __construct($object, $showRelations = null, $recursiveIndex = null)
    {
        if($object === null){
            $this->data = null;
            return;
        }
        $result = [];
        if(is_a($object,Collection::class)){
            foreach ($object->all() as $item) {
                $result[] = $this->formatData($item, null, $showRelations, $recursiveIndex);
            }
            $this->data = $result;
        } else {
            $this->data = $this->formatData($object, null, $showRelations, $recursiveIndex);
        }
    }

    static public function formatData($object, $asArray = null, $showRelations = null, $recursiveIndex = null)
    {
        $recursiveIndex = $recursiveIndex ? $recursiveIndex : 0;
        $dataObj = [
            'type' => (!empty($object->typeAlias)) ? $object->typeAlias : $object->getTable(),
            'id' => ($object->id) ? $object->id : null,
        ];
        $attributes = $object->getAttributes();

        foreach($object->getHidden() as $hide){
            unset($attributes[$hide]);
        }
        if (!empty($attributes['id'])) {
            unset($attributes['id']);
        }
        if (!empty($attributes)) {
            $dataObj['attributes'] = $attributes;
        }

        if ($showRelations && method_exists($object, 'getRelationships')) {
            $defaultRelationParam = [
                'maxSublevel' => 1,
            ];
            $objRelationships = $object->getRelationships();
            $dataRelations = [];
            foreach ($objRelationships as $type => $relations) {
                foreach ($relations as $rel=>$params) {
                    $params = array_merge($defaultRelationParam, $params);
                    if($params['maxSublevel'] && $recursiveIndex <= $params['maxSublevel']){
                        $relCollection = $object->$rel()->get();
                        if(in_array($type,['to-one','belongsTo','toOne'])){
                            $relCollection = $relCollection->first();
                        }
                        $relData = new ResponseDataClass($relCollection, $recursiveIndex + 1);
                        $relData = $relData->toArray(true);
                        $dataRelations[$rel] = $relData;
                    }
                }
            }
            $dataObj['relationships'] = $dataRelations;
        }
        return ($asArray) ? $dataObj : (object)$dataObj;
    }

    public
    function toJson()
    {
        return json_encode($this->toArray());
    }

    public
    function toArray($relationship = null)
    {
        $result = [];
        if (empty($errors)) {
            $result['data'] = $this->data;
        } elseif ($relationship) {
            return null;
        } else {
            $result['errors'] = $this->errors;
        }
        return $result;
    }
}