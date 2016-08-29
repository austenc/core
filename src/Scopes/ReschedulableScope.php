<?php namespace Hdmaster\Core\Scopes;

use Illuminate\Database\Eloquent\ScopeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReschedulableScope implements ScopeInterface
{

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->where($builder->getModel()->getTable() . '.status', '!=', 'rescheduled');
    }

    /**
     * Remove the scope from the given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function remove(Builder $builder, Model $model)
    {
        $query    = $builder->getQuery();
        $bindings = $query->getRawBindings()['where'];

        foreach ((array) $query->wheres as $key => $where) {
            if ($where['column'] == $builder->getModel()->getTable() . '.status' && $where['operator'] == '!=' && $where['value'] == 'rescheduled') {
                // unset the where clause
                unset($query->wheres[$key]);
                $query->wheres = array_values($query->wheres);

                // unset the bindings
                unset($bindings[$key]);
                $query->setBindings(array_values($bindings));
            }
        }
    }
}
