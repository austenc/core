<?php namespace Hdmaster\Core\Scopes;

use Illuminate\Database\Eloquent\ScopeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ClientOnlyScope implements ScopeInterface
{

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $column = $builder->getModel()->getQualifiedClientColumn();
        $builder->where($column, \Config::get('core.client.abbrev'));
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
        $column   = $builder->getModel()->getQualifiedClientColumn();

        foreach ((array) $query->wheres as $key => $where) {
            if ($where['column'] == $column && $where['operator'] == '=' && $where['value'] == \Config::get('core.client.abbrev')) {
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
