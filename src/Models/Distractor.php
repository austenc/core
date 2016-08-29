<?php namespace Hdmaster\Core\Models\Distractor;

use \Testitem;

class Distractor extends \Eloquent
{

    protected $fillable = ['content','testitem_id'];
    protected $map = [
        1 => 'A',
        2 => 'B',
        3 => 'C',
        4 => 'D',
        5 => 'E'
    ];

    /**
     * Gets an ABCDE letter matching the ordinal position of this distractor
     * @return string
     */
    public function getLetterAttribute()
    {
        return array_key_exists($this->ordinal, $this->map) ? $this->map[$this->ordinal] : '';
    }

    /**
     * A distractor belongs to a single testitem
     */
    public function testitem()
    {
        return $this->belongsTo('Testitem');
    }

    /**
     * A distractor _may_ be the answer to an item
     */
    public function answerTo()
    {
        return $this->belongsTo('Testitem', 'testitem_id', 'answer');
    }
}
