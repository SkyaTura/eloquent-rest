<?php

namespace SkyaTura\EloquentREST\Traits;

use Illuminate\Http\Request;

trait ResponseTrait
{
    private function responseAs($obj, $type = 'json', $code = 200)
    {
        return response()->$type($obj, $code);
    }

    public function responseError($msg = 'Unknown error', $code = 500, $type = 'json')
    {
        return response()->$type(['error' => $msg], $code);
    }

    public function responseEmpty($type = 'json')
    {
        return response()->$type([], 204);
    }

    public function response($object, $type = 'json')
    {
        if (!$object) return $this->responseEmpty($type);

        return $this->responseAs($object, $type);
    }
}