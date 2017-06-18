<?php

namespace SkyaTura\EloquentREST\Traits;

use Illuminate\Http\Request;

trait ResourceTrait
{
    use DefaultActionsTrait;
    /**
     * @var Request
     */
    protected $request;

    /**
     * The requested resource path
     *
     * @var array
     */
    protected $resourcePath;

    /**
     * @var array
     */
    public $customActions;

    /**
     * @var array
     */
    private $defaultMethods = [
        'GET' => ['index', 'show'],
        'POST' => ['store', null],
        'PUT' => [null, 'replace'],
        'PATCH' => [null, 'update'],
        'DELETE' => [null, 'delete'],
    ];

    /**
     * @var string
     */
    protected $ref;
    protected $relationResource;

    /**
     * @param Request $request
     * @param string $path
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resource(Request $request, $path = '')
    {
        $path = explode('/', $path);
        $resourcePath = [];
        if (!empty($path)) {
            $this->ref = array_splice($path, 0, 1)[0];
            while (!empty($path)) {
                if (count($path) >= 2) {
                    $relationship = array_splice($path, 0, 2);
                    $resourcePath[$relationship[0]] = $relationship[1];
                } else {
                    $this->relationResource = array_splice($path, 0, 1)[0];
                }
            }
        }
        $this->resourcePath = $resourcePath;
        $this->request = $request;

        $method = $this->getAction($request->method());
        $parameters = array_prepend($resourcePath, $request);
        return $this->callAction($method, $parameters);
    }

    protected function getAction($method)
    {
        $ref = $this->ref;
        $methodPath = strtolower($method . ($ref) ? '.' . $ref : '');
        if (!empty($this->customActions[$methodPath]))
            return $this->customActions[$methodPath];

        if(empty($this->defaultMethods[$method]))
            return '__call';

        $actions = $this->defaultMethods[$method];
        $action = empty($ref)?$actions[0]:$actions[1];

        return $action?$action:'__call';
    }

    /**
     * @param array $params
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function defaultModel($params = [])
    {
        return new $this->defaultModel($params);
    }

    private function findOrCreate($model, $ref){
        if(empty($ref)) return is_string($model)? new $model : $model;

        switch ($ref){
            case 'first':
                return (is_string($model))?$model::first() : $model->first();
            case 'last':
                return (is_string($model))?$model::latest()->first() : $model->get()->last();
            default:
                return (is_string($model))?$model::find($ref) : $model->find($ref);
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function model()
    {
        $params = $this->resourcePath;
        $model = $this->defaultModel();
        $relationResource = $this->relationResource;
        $ref = $this->ref;

        $obj = $this->findOrCreate($model, $ref);

        if (!empty($params)) {
            foreach ($params as $rel => $id)
                $obj = $this->findOrCreate($obj->$rel(), $id);
        }

        return (empty($relationResource)) ? $obj : $obj->$relationResource;
    }
}