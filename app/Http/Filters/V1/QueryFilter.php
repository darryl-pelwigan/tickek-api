<?php

namespace App\Http\Filters\V1;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class QueryFilter {

    protected $builder;
    protected $request;
    protected $sortable = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(Builder $builder)
    {
        $this->builder = $builder;

        foreach ($this->request->all() as $key => $value) {
            if(method_exists($this, $key)) {
                $this->$key($value);
            };
        }

        return $builder;
    }

    protected function filter($arr)
    {
        foreach ($arr as $key => $value) {
            if(method_exists($this, $key)) {
                $this->$key($value);
            };
        }

        return $this->builder;
    }

    protected function sort($value)
    {
        $sortAttributes = explode(',', $value);

        foreach ($sortAttributes as $attribute) {
            $direction = 'asc';

            if(strpos($attribute, '-') === 0) {
                $direction = 'desc';
                $attribute = substr($attribute, 1);
            }

            if( ! in_array($attribute, $this->sortable) &&
                ! array_key_exists($attribute, $this->sortable)) {
                continue;
            }

            $columnName = $this->sortable[$attribute] ?? null;

            if ($columnName == null) {
                $columnName = $attribute;
            }

            $this->builder->orderBy($columnName, $direction);
        }
    }

}
