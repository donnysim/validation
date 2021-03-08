<?php

declare(strict_types=1);

namespace DonnySim\Validation\Laravel;

use DonnySim\Validation\Laravel\Rules\Exists;
use DonnySim\Validation\Laravel\Rules\Unique;

trait RuleSet
{
    /**
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|string $target
     * @param string $column
     *
     * @return static
     */
    public function existsInDatabase($target, string $column): self
    {
        $this->rules[] = new Exists($target, $column);

        return $this;
    }

    /**
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|string $target
     * @param string $column
     * @param \DonnySim\Validation\FieldReference|string|int|string[]|int[] $except
     * @param string $exceptColumn
     *
     * @return static
     */
    public function uniqueInDatabase($target, string $column, $except = null, string $exceptColumn = 'id'): self
    {
        $this->rules[] = Unique::make($target, $column)->except($except, $exceptColumn);

        return $this;
    }
}
