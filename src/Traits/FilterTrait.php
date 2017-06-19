<?php

namespace SkyaTura\EloquentREST\Traits;

use Illuminate\Http\Request;
use SkyaTura\EloquentREST\Helpers\ParamHelper as Helper;

trait FilterTrait
{
    /**
     * This array references Request parameters to internal methods
     *
     * @var array
     */
    private $filterMethods = [
        '_select' => 'selectFields',
        '_where' => 'where',
        '_sort' => 'orderBy',
        '_group' => 'groupBy',
        '_pagination' => 'pagination',
    ];

    /**
     * Columns in this array will never be shown
     *
     * @var array
     */
    public $ignoredFields = [
        'password',
    ];

    /**
     * Columns in this array will always be shown
     * @var array
     */
    public $persistFields = [
        'id',
    ];

    /**
     * @var array
     */
    public $defaultOrderBy = ['id'];

    /**
     * @param \Illuminate\Database\Eloquent\Builder $obj
     * @param array $fields
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function selectFields($obj, $fields)
    {
        if (empty($fields)) return $obj;
        $fields = Helper::commaFields($fields);
        $fields = array_merge($fields, $this->persistFields);
        $fields = array_diff($fields, $this->ignoredFields);

        return $obj->select($fields);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $obj
     * @param $queries
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function where($obj, $queries)
    {
        if (empty($queries)) return $obj;
        if (is_string($queries))
            $queries = json_decode($queries);

        foreach ($queries as $query)
            $obj = $obj->where($query[0], $query[1], $query[2]);
        return $obj;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $obj
     * @param string $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function orderBy($obj, $columns)
    {
        if (empty($columns)) return $obj;
        $columns = Helper::commaFields($columns);
        foreach ($columns as $column) {
            if (Helper::str_starts_with('-', $column))
                $obj->orderBy(substr($column, 1), 'desc');
            else
                $obj->orderBy($column, 'asc');
        }
        return $obj;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $obj
     * @param string $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function groupBy($obj, $columns)
    {
        if (empty($columns)) return $obj;
        $columns = Helper::commaFields($columns);
        if (!in_array('id', $columns))
            $columns[] = 'id';
        return $obj->groupBy($columns);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $obj
     * @param string $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function pagination($obj, $query)
    {
        $query = Helper::commaFields($query, [
            "unique" => false,
        ]);
        $limit = $query[0];
        $offset = (!empty($query[1])) ? $query[1] : 0;
        return $obj->limit($limit)->offset($offset);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $obj
     * @param Request $request
     * @param array $custom_clauses
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function filter($obj, Request $request, $custom_clauses = [])
    {
        $methods = $this->filterMethods;
        $ignore = $this->filterMethods;

        $filterExceptions = array_merge(array_keys($methods), $ignore);
        $filters = $request->except($filterExceptions);
        $filterArray = [];
        foreach ($filters as $filter => $arg) {
            $filterArray[] = [$filter, '=', $arg];
        };
        if (!empty($filterArray))
            $obj = $this->where($obj, $filterArray);

        $clauses = $request->intersect(array_keys($methods));
        foreach ($clauses as $clause => $arg) {
            $obj = $this->$methods[$clause]($obj, $arg);
        };

        foreach ($custom_clauses as $clause => $arg) {
            $obj = $this->$methods[$clause]($obj, $arg);
        };

        return $obj;
    }
}