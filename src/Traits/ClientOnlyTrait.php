<?php namespace Hdmaster\Core\Traits;

use Hdmaster\Core\Scopes\ClientOnlyScope;

trait ClientOnlyTrait
{

    /**
     * Boot the trait and apply the global scope
     */
    public static function bootClientOnlyTrait()
    {
        static::addGlobalScope(new ClientOnlyScope);
    }

    /**
     * Get the name of the column for applying the scope.
     * 
     * @return string
     */
    public function getClientColumn()
    {
        return defined('static::CLIENT_COLUMN') ? static::CLIENT_COLUMN : 'client';
    }
    
    /**
     * Get the fully qualified column name for applying the scope.
     * 
     * @return string
     */
    public function getQualifiedClientColumn()
    {
        return $this->getTable().'.'.$this->getClientColumn();
    }
}
