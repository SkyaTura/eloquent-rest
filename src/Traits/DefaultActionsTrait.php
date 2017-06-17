<?php

namespace SkyaTura\EloquentREST\Traits;

use Illuminate\Http\Request;

trait DefaultActionsTrait
{
    use ResponseTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $obj = $this->model();
        //$filtered = $this->filter($obj, $request);
        return $this->response($obj->get());
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, ...$params)
    {
        $resId = array_splice($params, -1, 1);

        $obj = $this->model();
        $filtered = $this->filter($obj, $request);
        $filtered = $this->filter($obj->where('id', '=', $resId), $request);
        return $this->response($filtered->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $model = $this->defaultModel($request->all());
        $model->save();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $class = $this->defaultModel;
        /**
         * @var $model \Illuminate\Database\Eloquent\Model
         */
        $model = $class::find($id);
        $fillable = $model->getFillable();
        $newValues = $request->intersect($fillable);
        $model->fill($newValues);
        $model->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}