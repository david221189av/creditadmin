<?php

namespace Terranet\Administrator\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface Sortable
{
    /**
     * @param Builder $query
     * @param Model $model
     * @param string $direction
     *
     * @return Builder
     */
    public function sortBy(Builder $query, Model $model, string $direction): Builder;
}
