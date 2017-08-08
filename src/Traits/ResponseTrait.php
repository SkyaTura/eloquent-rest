<?php

namespace SkyaTura\EloquentREST\Traits;

use Illuminate\Http\Request;
use SkyaTura\EloquentREST\Classes\ResponseDataClass;

trait ResponseTrait
{
    private function responseAs($obj, $type = 'json', $code = 200)
    {
        $response = new ResponseDataClass($obj);
        return response()->$type($response->toArray(), $code);
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
        return $this->responseAs($object, $type);
    }
}