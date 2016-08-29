<?php namespace Hdmaster\Core\Models\Stat;

use \Testitem;

class Stat extends \Eloquent
{
    protected $fillable = ['item_id', 'count', 'difficulty', 'discrimination', 'guessing', 'pvalue', 'angoff', 'pbs'];

    /**
     * A stat belongs to one testitem 
     */
    public function testitem()
    {
        return $this->belongsTo(Testitem::class);
    }
}
