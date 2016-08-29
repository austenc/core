<?php namespace Hdmaster\Core\Traits;

trait FacilityTrait
{

    /**
     * Register event bindings
     */
    public static function bootFacilityTrait()
    {
        // Soft Delete
        // Delete any user model related
        self::deleted(function ($facility) {
            $facility->user->delete();
        });
    }
}
