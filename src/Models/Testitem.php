<?php namespace Hdmaster\Core\Models\Testitem;

use Config;
use Input;
use Validator;
use \Vocab;
use \Subject;
use \Exam;
use \Distractor;
use \Testform;
use \Stat;
use \Sorter;

class Testitem extends \Eloquent
{
    protected $fillable = ['stem', 'user_id', 'answer', 'status', 'comments'];

    // Validation rules
    public static $rules = [
        'stem'        => 'required',
        'answer'      => 'required',
        'distractors' => 'array_has_one',
        'pvalue'      => 'numeric'
    ];

    // Errors from validation
    public $errors;

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'exam_testitem', 'testitem_id', 'subject_id')
                    ->where('exam_testitem.client', '=', Config::get('core.client.abbrev'));
    }

    /**
     * A testitem can have multiple exams
     */
    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_testitem', 'testitem_id', 'exam_id')
                    ->where('exam_testitem.client', '=', Config::get('core.client.abbrev'));
    }

    /**
     * A testitem can have one answer
     */
    public function theAnswer()
    {
        return $this->hasOne(Distractor::class, 'id', 'answer');
    }

    /**
     * A testitem can be a part of many different testforms
     */
    public function testforms()
    {
        return $this->belongsToMany(Testform::class)->orderBy('status');
    }

    /** 
     * A testitem may have many enemies, or be enemy of many
     */
    public function enemies()
    {
        return $this->belongsToMany(\Testitem::class, 'enemies', 'testitem_id', 'enemy_id');
    }

    /**
     * A testitem can have many distractors
     */
    public function distractors()
    {
        return $this->hasMany(Distractor::class)->orderBy('distractors.ordinal', 'ASC');
    }

    /**
     * A testitem can have many related vocab words
     */
    public function vocab()
    {
        return $this->belongsToMany(Vocab::class);
    }

    /**
     * A testitem can have many sets of statistics
     */
    public function stats()
    {
        return $this->hasMany(Stat::class)->orderBy('updated_at', 'DESC');
    }

    /**
     * Handle searching from testitems.index
     */
    public function handleSearch()
    {
        // search options
        $search = Input::get('search');
        $type   = Input::get('search_type');

        // get all testitems along with the answer
        $base = Testitem::with('theAnswer');

        // add search params
        switch ($type) {
            case 'Stem':
                $base->where('stem', 'LIKE', '%'.$search.'%');
                break;
            
            case 'Subject':
                $base->whereHas('subjects', function ($q) use ($search) {
                    $q->where('name', 'LIKE', '%'.$search.'%');
                })->get();
                break;

            default:
                break;
        }

        $baseAll    = clone $base;
        $baseActive = clone $base;
        $baseDraft  = clone $base;

        // counts for sidebar by status
        $r['count']['all']    = $baseAll->count();
        $r['count']['active'] = $baseActive->where('status', 'active')->count();
        $r['count']['draft']  = $baseDraft->where('status', 'draft')->count();

        $r['items'] = $base->orderBy(Input::get('sort', 'status'), Sorter::order())->paginate(Config::get('paginate.default'));

        return $r;
    }

    // Validation
    public function validate()
    {
        $rules = static::$rules;

        $validation = Validator::make(Input::all(), $rules);

        if ($validation->passes()) {
            return true;
        }

        $this->errors = $validation->messages();

        return false;
    }

    public function getStatusClassAttribute()
    {
        if ($this->status == 'draft') {
            return 'warning';
        } elseif ($this->status == 'active') {
            return 'success';
        } elseif ($this->status == 'archived') {
            return 'muted';
        } else {
            return '';
        }
    }

    public function getEnemyStringAttribute()
    {
        $enemies = $this->enemies();
        return implode(', ', $enemies->lists('id')->all());
    }

    public function getStatusTextAttribute()
    {
        return 'This item is <strong>'.$this->status.'</strong> status.';
    }

    public function getExcerptAttribute()
    {
        $return_length = 100;
        if (strlen($this->stem) > $return_length) {
            return substr($this->stem, 0, $return_length).'...';
        } else {
            return $this->stem;
        }
    }

    /** 
     * Updates vocab words attached to a given testitem
     * @return mixed
     */
    public function updateVocab()
    {
        // Update vocab
        $vocab = Input::get('vocab');
        if (! empty($vocab)) {
            $words = [];
            foreach (explode(',', $vocab) as $v) {
                $words[] = Vocab::firstOrCreate(['word' => $v])->id;
            }

            if (! empty($words)) {
                return $this->vocab()->sync($words);
            }
        }

        return null;
    }

    /**
     * Updates the exams / subjects that an item is tied to from input
     *
     * @return 	mixed
     */
    public function updateExamSubjects()
    {
        $subjects = Input::get('subjects');

        if (! empty($subjects)) {
            $toSync = [];
            foreach ($subjects as $examId => $subjectId) {
                $toSync[$subjectId] = ['exam_id' => $examId, 'client' => Config::get('core.client.abbrev')];
            }

            if (! empty($toSync)) {
                return $this->subjects()->sync($toSync);
            }
        }

        return null;
    }

    /**
     * Update the enemies from a form
     */
    public function updateEnemies()
    {
        // current item enemies
        $currEnemies = $this->enemies->lists('id')->all();
        // enemies from the form
        $enemyIds = Input::get('enemies') ? array_map('trim', explode(',', Input::get('enemies'))) : [];

        // remove reverse enemy relation (of deleted enemies)
        $deletedEnemies = array_diff($currEnemies, $enemyIds);
        foreach ($deletedEnemies as $del) {
            \Testitem::find($del)->enemies()->detach($this->id);
        }

        // enemies of enemy
        if (! empty($enemyIds)) {
            $otherEnemies = [];

            foreach ($enemyIds as $enemyId) {
                // get all enemies for the current enemy
                $curr_enemy = \Testitem::with('enemies')->find($enemyId);

                // if enemy has enemies, add them to enemyIds
                if (! $curr_enemy->enemies->isEmpty()) {
                    $otherEnemies = array_merge($otherEnemies, $curr_enemy->enemies->lists('id')->all());
                }
            }

            // Enemies and Enemy of Enemy
            $enemyIds = array_merge($enemyIds, $otherEnemies);
        }

        // ensure all enemy ids are int
        $enemyIds = array_map('intval', $enemyIds);

        // take out this item's id if it exists in the array
        if (($key = array_search($this->id, $enemyIds)) !== false) {
            unset($enemyIds[$key]);
        }

        // add all enemies to main item
        $this->enemies()->sync($enemyIds);

        // loop thru all enemies so we can do the reverse enemy entry
        // (set this item as enemy for the current enemy)
        foreach ($enemyIds as $e) {
            $item = \Testitem::with('enemies')->find($e);

            // enemies are THIS item, all the current enemies, 
            // and all the other enemies defined / found above for this item too
            $newEnemies = array_unique(array_merge([$this->id], $this->enemies->lists('id')->all(), $enemyIds));

            $enemyKey = array_search($e, $newEnemies);

            if ($enemyKey) {
                $newEnemies = array_except($newEnemies, [$enemyKey]);
            }

            $item->enemies()->sync($newEnemies);
        }
    }
}
