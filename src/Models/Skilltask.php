<?php namespace Hdmaster\Core\Models\Skilltask;

use Input;
use Validator;
use Flash;
use Session;
use Config;
use \SkilltaskStep;
use \SkilltaskSetup;
use \SkilltaskResponse;
use \Skilltest;
use \Skillexam;
use \Sorter;

class Skilltask extends \Eloquent
{
    protected $fillable = ['skillexam_id', 'parent_id', 'title', 'long_title',
                           'scenario', 'note', 'setup_review', 'weight', 'minimum', 'avg_time', 'status'];

    protected $rules = [
        'title'            => 'required',
        'scenario'    => 'required',
        'weight'        => 'required',
        'minimum'        => 'required',
        'step_ids'        => 'required',
        'step_weights'    => 'check_task_weights',
        'skillexam_id'    => 'required'
    ];

    public static function boot()
    {
        parent::boot();

        // Attach event handler, on deleting of the task
        Skilltask::deleting(function ($task) {
            // delete steps
            foreach ($task->steps as $step) {
                $step->delete();
            }

            // delete setups
            foreach ($task->setups as $setup) {
                $setup->delete();
            }

            // delete enemies?
            foreach ($task->enemies as $enemy) {
                $enemy->delete();
            }
        });
    }

    // relations
    public function steps()
    {
        return $this->hasMany(SkilltaskStep::class)->orderBy('ordinal', 'ASC');
    }
    public function setups()
    {
        return $this->hasMany(SkilltaskSetup::class);
    }
    public function responses()
    {
        return $this->hasMany(SkilltaskResponse::class);
    }
    public function skillexams()
    {
        return $this->belongsToMany(Skillexam::class, 'skillexam_tasks');
    }
    public function tests()
    {
        return $this->belongsToMany(Skilltest::class, 'skilltest_tasks');
    }
    public function enemies()
    {
        return $this->belongsToMany(self::class, 'skilltask_enemies', 'skilltask_id', 'enemy_id')
            ->withPivot('comments')
            ->orderBy('enemy_id', 'ASC');
    }
    public function parent()
    {
        return $this->hasOne(Skilltask::class, 'id', 'parent_id');
    }
    public function children()
    {
        return $this->hasMany(Skilltask::class, 'parent_id', 'id');
    }


    // scopes
    public function scopeActiveTasks($query)
    {
        return $query->where('status', '=', 'active');
    }


    // accessors
    public function getEnemyStringAttribute()
    {
        $enemies = $this->enemies();
        return implode(', ', $enemies->lists('id')->all());
    }

    public function getEnemyLinksAttribute()
    {
        $enemyList = [];
        $enemies = $this->enemies()->lists('id')->all();

        foreach ($enemies as $e) {
            $enemyList[] = '<a href="/tasks/'.$e.'/edit" target="_blank">'.$e.'</a>';
        }

        return implode(', ', $enemyList);
    }

    public function getIsArchivedAttribute()
    {
        return ! empty($this->archived_at);
    }


    /**
     * Saves current or default search filter
     */
    public function setSearchFilter()
    {
        // sidebar filter
        // active|draft|archived
        if (Input::get('s')) {
            Session::put('tasks.search.filter', Input::get('s'));
        } else {
            // if filter isnt already set, set to default
            if (! Session::has('tasks.search.filter')) {
                Session::put('tasks.search.filter', 'active');
            }
        }
    }

    /**
     * Check for seach terms and apply them to query
     */
    public function addSearchTerms($query, $types, $terms)
    {
        if ($types !== null) {
            foreach ($types as $k => $type) {
                $search = $terms[$k];

                switch ($type) {
                    case 'Title':
                        $query->where('title', 'like', '%'.$search.'%');
                    break;
                    case 'Scenario':
                        $query->where('scenario', 'like', '%'.$search.'%');
                    break;
                    case 'Weight':
                        $query->where('weight', $search);
                    break;
                    case 'Minimum':
                        $query->where('minimum', $search);
                    break;

                    default:
                        // do nothing for now, just show all records
                }
            }
        }

        return $query;
    }

    /**
     * Handle searching of skill tasks
     */
    public function handleSearch()
    {
        // set session vars to track which region we are filtering
        $this->setSearchFilter();

        $searchTypes   = Session::get('tasks.search.types');
        $searchQueries = Session::get('tasks.search.queries');
        $filter        = Session::get('tasks.search.filter');

        $draft    = Skilltask::with('steps', 'setups')->where('status', 'draft');
        $active   = Skilltask::with('steps', 'setups')->where('status', 'active');
        $archived = Skilltask::with('steps', 'setups')->where('status', 'archived');
        $all      = Skilltask::with('steps', 'setups');

        $q       = $this->addSearchTerms($all, $searchTypes, $searchQueries)->orderBy(Input::get('sort', 'status'), Sorter::order())->get();
        $qDraft  = $this->addSearchTerms($draft, $searchTypes, $searchQueries)->get();
        $qActive = $this->addSearchTerms($active, $searchTypes, $searchQueries)->get();
        $qArch   = $this->addSearchTerms($archived, $searchTypes, $searchQueries)->get();

        // count results from each region
        $r['count']['active']   = count($qActive);
        $r['count']['archived'] = count($qArch);
        $r['count']['draft']    = count($qDraft);
        $r['tasks'] = $q;

        return $r;
    }

    public function archive()
    {
        $this->status = 'archived';
        $this->archived_at = date('Y-m-d H:i:s');
        $this->save();

        // archive all TESTS that this TASK was on
        foreach ($this->tests()->get() as $test) {
            $test->archive();
        }

        Flash::success('Skill Task archived');

        return true;
    }

    /**
     * Adds a new skilltask from the add form
     */
    public function addWithInput()
    {
        $task = Skilltask::create([
            'parent_id'  => Input::get('parent_id') ? Input::get('parent_id') : null,
            'title'      => Input::get('title'),
            'long_title' => Input::get('long_title'),
            'scenario'   => Input::get('scenario'),
            'note'       => Input::get('note'),
            'weight'     => strtoupper(Input::get('weight')),
            'minimum'    => Input::get('minimum'),
            'avg_time'   => Input::get('avg_time'),
            'status'     => 'draft'
        ]);
        
        // steps
        $stepIds      = Input::get('step_ids');
        $stepOutcomes = Input::get('step_outcomes');
        $stepOrder    = Input::get('step_order');
        $stepWeight   = Input::get('step_weights');
        $stepAlt      = Input::get('step_alts');
        $stepComment  = Input::get('step_comments');
        $stepKey      = Input::get('step_key') ? Input::get('step_key') : [];
        // step variable inputs
        $inputIds      = Input::get('input_id');
        $inputStepIds  = Input::get('input_step_id');

        if (! empty($stepIds)) {
            foreach ($stepIds as $i => $stepId) {
                // create step
                $step = SkilltaskStep::create([
                    'skilltask_id'     => $task->id,
                    'weight'           => $stepWeight[$i],
                    'is_key'           => !empty($stepKey) && array_key_exists($i, $stepKey) ? 1 : 0,
                    'ordinal'          => $stepOrder[$i],
                    'expected_outcome' => $stepOutcomes[$i],
                    'alt_display'      => $stepAlt[$i],
                    'comments'         => !empty($stepComment[$i]) ? $stepComment[$i] : null
                ]);

                // if this step id had an input
                if (is_array($inputStepIds) && in_array($stepId, $inputStepIds)) {
                    $newStepInputIds = [];
                    $inputKeys       = array_keys($inputStepIds, $stepId);
                    
                    foreach ($inputKeys as $key) {
                        $inputId           = $inputIds[$key];
                        $newStepInputIds[] = $inputId;
                    }

                    $step->inputs()->sync($newStepInputIds);
                }
            }
        }

        // any setups?
        $setupIds      = Input::get('setup_ids');
        $setups        = Input::get('setups');
        $setupComments = Input::get('setup_comments');
        if (! empty($setupIds)) {
            foreach ($setupIds as $i => $id) {
                SkilltaskSetup::create([
                    'skilltask_id'    => $task->id,
                    'setup'            => $setups[$i],
                    'comments'        => $setupComments[$i]
                ]);
            }
        }

        // update enemies
        $task->updateEnemies();
        
        // Hook to skillexams
        if (Input::get('skillexam_id')) {
            $task->skillexams()->sync(Input::get('skillexam_id'));
        }
        
        // was this a cloned task?
        if (Input::get('cloned')) {
            Flash::success('Skill task cloned.');
        }

        return $task->id;
    }

    /** 
     * Update entire task including steps
     */
    public function updateWithInput()
    {
        $this->title      = Input::get('title');
        $this->long_title = Input::get('long_title');
        $this->scenario   = Input::get('scenario');
        $this->note       = Input::get('note');
        $this->weight     = strtoupper(Input::get('weight'));
        $this->minimum    = Input::get('minimum');
        $this->avg_time   = Input::get('avg_time');
        
        // update skillexams (that this task belongs to)
        $skillexams       = Input::get('skillexam_id');
        if (! empty($skillexams)) {
            // only update if at least 1 skillexam

            $this->skillexams()->sync($skillexams);
        }

        // update steps
        $stepIds      = Input::get('step_ids');
        $stepOutcomes = Input::get('step_outcomes');
        $stepOrder    = Input::get('step_order');
        $stepWeight   = Input::get('step_weights');
        $stepAlts     = Input::get('step_alts');
        $stepComments = Input::get('step_comments');
        $stepKeys     = Input::get('step_key') ? Input::get('step_key') : array();
        if (! empty($stepIds)) {
            foreach ($stepIds as $i => $id) {
                if ($id == -1) {
                    SkilltaskStep::create([
                        'skilltask_id'        => $this->id,
                        'weight'            => $stepWeight[$i],
                        'is_key'            => array_key_exists($i, $stepKeys) ? 1 : 0,
                        'ordinal'            => $stepOrder[$i],
                        'expected_outcome'    => $stepOutcomes[$i],
                        'comments'            => (! empty($stepComments[$i]) ? $stepComments[$i] : null)
                    ]);
                } else {
                    $step = SkilltaskStep::find($id);
                    
                    $step->weight = $stepWeight[$i];
                    $step->is_key = array_key_exists($i, $stepKeys) ? 1 : 0;
                    $step->ordinal = $stepOrder[$i];
                    $step->expected_outcome = $stepOutcomes[$i];
                    $step->comments = $stepComments[$i];
                    $step->save();
                }
            }
        }

        // update setups
        $setupIds      = Input::get('setup_ids');
        $setupSetups   = Input::get('setups');
        $setupComments = Input::get('setup_comments');
        if (! empty($setupIds)) {
            foreach ($setupIds as $i => $id) {
                if ($id == -1) {
                    SkilltaskSetup::create([
                        'skilltask_id'    => $this->id,
                        'setup'            => $setupSetups[$i],
                        'comments'        => $setupComments[$i]
                    ]);
                } else {
                    $setup = SkilltaskSetup::find($id);

                    $setup->setup = $setupSetups[$i];
                    $setup->comments = !empty($setupComments[$i]) ? $setupComments[$i] : null;
                    $setup->save();
                }
            }
        }

        // update enemies
        $this->updateEnemies();

        return $this->save();
    }

    public function updateEnemies()
    {
        // current tasks enemies (in db)
        $currEnemies = $this->enemies->lists('id')->all();
        // enemies from the form
        $enemyIds = Input::get('enemies') ? array_map('trim', explode(',', Input::get('enemies'))) : [];

        // remove reverse enemy relation (of deleted enemies)
        $deletedEnemies = array_diff($currEnemies, $enemyIds);
        foreach ($deletedEnemies as $del) {
            Skilltask::find($del)->enemies()->detach($this->id);
        }

        // enemies of enemy
        if (! empty($enemyIds)) {
            $otherEnemies = [];

            foreach ($enemyIds as $enemyId) {
                // get all enemies for the current enemy
                $curr_enemy = Skilltask::with('enemies')->find($enemyId);

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

        // add all enemies to main task
        $this->enemies()->sync($enemyIds);

        // loop thru all enemies so we can do the reverse enemy entry
        // (set this task as enemy for the current enemy)
        foreach ($enemyIds as $e) {
            $task = Skilltask::with('enemies')->find($e);

            // enemies are THIS item, all the current enemies, 
            // and all the other enemies defined / found above for this item too
            $newEnemies = array_unique(array_merge([$this->id], $this->enemies->lists('id')->all(), $enemyIds));

            $enemyKey = array_search($e, $newEnemies);

            if ($enemyKey) {
                $newEnemies = array_except($newEnemies, [$enemyKey]);
            }

            $task->enemies()->sync($newEnemies);
        }
    }

    /**
     * Validation for a skill task
     * @param  array
     * @return boolean
     */
    public function validate()
    {
        $rules = $this->rules;

        $messages = [
            'step_ids.required'               => 'At least one Step is required.',
            'step_weights.check_task_weights' => 'All Steps must have a weight value.',
            'skillexam_id.required'           => 'At least one Skillexam is required.'
        ];

        // Create a validation Instance
        $v = Validator::make(Input::get(), $rules, $messages);

        // add rules for
        //   - checking steps (must be at least 1, must have a weight and outcome set)
        //   - checking setups (can be empty, if set must have setup listed)

        if ($v->passes()) {
            return true;
        }

        $this->errors = $v->messages();

        return false;
    }
}
