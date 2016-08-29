<?php namespace Hdmaster\Core\Models\Testplan;

use Illuminate\Support\Collection as Collection;
use Illuminate\Support\MessageBag as MessageBag;
use Validator;
use Input;
use Hdmaster\Core\Readinglevel;
use Session;
use Response;
use \Subject;
use \Exam;

class Testplan extends \Eloquent
{

    use \ClientOnlyTrait;

    protected $guarded    = ['id'];

    // Validation rules
    public static $rules = [
        'name'          => 'required',
        'timelimit'     => 'required|numeric',
        'minimum_score' => 'required|numeric',
        'max_attempts'  => 'required|integer',
        'subjects'      => 'array_has_one'
    ];

    public $errors;
    public $messages;

    public function __construct($attributes = array())
    {
        parent::__construct($attributes); // Eloquent
        $this->errors   = new MessageBag;
        $this->messages = new MessageBag;
    }

    /**
     * A testplan has one exam
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
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

    /**
     * Convert from json to a regular array
     */
    public function getItemsBySubjectAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * Grab items by subject without subjects that have zero items, and with id's mapped to report_as if needed
     */
    public function getItemsBySubjectMappedAttribute()
    {
        $parsedJson = json_decode($this->getOriginal('items_by_subject'), true);
        $itemsBySubject = is_array($parsedJson) ? array_filter($parsedJson) : [];
        
        if (! empty($itemsBySubject)) {
            $subjects = Subject::whereIn('id', array_keys($itemsBySubject))->get();

            foreach ($itemsBySubject as $id => $num) {
                $subject = $subjects->find($id);
                
                // does this subject report_as another?
                if ($subject && ! empty($subject->report_as)) {
                    // combine this total with an existing key for report_as id if it exists
                    if (array_key_exists($subject->report_as, $itemsBySubject)) {
                        $itemsBySubject[$subject->report_as] += $num;
                    } else {
                        // otherwise create a key for it with this total
                        $itemsBySubject[$subject->report_as] = $num;
                    }

                    // remove this subject's key from the main array, since it's reporting as another now
                    unset($itemsBySubject[$id]);
                }
            }
        }

        return $itemsBySubject;
    }

    /**
     * Calculates alpha coefficient from an array of items
     *
     * @return 	boolean
     */
    public function checkAlpha($params)
    {
        // --------------------------------------------------------
        // RELIABILITY COEFFICIENT CALCULATION / CHECK
        // 
        // Using item response theory we'll 'brute force' it
        // by making up random people across the ability scale, 
        // then we'll calculate what it is and compare it to plan params
        // --------------------------------------------------------																
        $population  = 1000; // number of candidates	  
        $delta       = 0.20; // subdivision of ability scale
        $normal      = true; // normal distribution?	
        //create random scores across the ability scale	
        $theta       = -3.0;
        $itemcount   = 0;
        $seriescount = 0;

        while ($theta<=3.1) {
            if ($normal) {
                //sample size larger near mean

                $sample_size = round($population*(1/sqrt(2.0*pi()))*exp(-1.0*($theta*$theta)/2.0)*$delta);
            } else {
                //sample size same at each point

                $sample_size = $population;
            }
            $total=0;
            if ($sample_size) {
                for ($i=0; $i<$sample_size; $i++) {
                    $score = $this->_random_score($params, count($params), $theta);
                    $corrected[$itemcount]=$score;
                    $total += substr($score, 0, 3);
                    $itemcount++;
                }
                $ave=round($total/$sample_size);
                $seriescount++;
                $series[$seriescount]['X'] = $theta;
                $series[$seriescount]['Y'] = ($total/$sample_size)/100;
                $series[$seriescount]['n'] = $sample_size;
            }
            $theta+=$delta;
        }

        // STRING ARRAY ($corrected) STRUCTURE
        // RAW
        // %% SCR  ITEM RESPONSES 1=CORRECT
        // == == ===============================================================================
        // 72 57 0101111110011111111100111111100101110111101111011011011110011101101111011011100
        // 66 52 0111011100011111001111101000010001101111111011111101111001011111111100101001011

        $acount = count($params); //number of items on exam
        $total  = 0;
        $count  = $itemcount;

        foreach ($corrected as $tline) {
            $tscore = substr($tline, 3, 3); //scores must be raw scores
            $total += $tscore;
        }
        $mean=$total/$count;

        $sum_squares=0;
        foreach ($corrected as $tline) {
            $tscore = substr($tline, 3, 3);
            $sum_squares += ($mean-$tscore)*($mean-$tscore);
        }
        $test_variance = $sum_squares/$count;

        $item_variances = 0;
        for ($i=0; $i<$acount; $i++) {
            // $total_items # columns 

            $p=0;
            foreach ($corrected as $tline) { // column $i
                $tanswers = substr($tline, 6);
                $p+=$tanswers[$i];
            }
            $p=$p/$count;
            $item_variances+=$p*(1-$p);
        }

        // Is there more than one item to calculate? Avoids division by zero below
        if ($acount > 1) {
            $alpha = ($acount/($acount-1))*(1.0-($item_variances/$test_variance));

            if (($alpha >= $this->reliability) && ($alpha <= $this->reliability_max)) {
                $this->messages->add('generate', 'Reliability '.$alpha.' in range ('.$this->reliability.','.$this->reliability_max.')');
                return true;
            } else {
                $this->errors->add('generate', 'Alpha coefficient of '.$alpha.' not within range ('.$this->reliability.', '.$this->reliability_max.')');
                return false;
            }
        }
    } // end checkAlpha()

    /**
     * Checks the cutscore for a test while generating
     */
    public function checkCutscore($params)
    {
        // --------------------------------------------------------																
        // CUT SCORE CALCULATION / CHECK
        // 
        // Call _random_score 1000 times with target_theta from testplan
        // Then we average the results and see if it's within the range
        // --------------------------------------------------------																	
        $avg_score = $this->_estimate_cut_score($params, $this->target_theta);
        if (($avg_score >= $this->cut_score) && ($avg_score <= $this->cut_score_max)) {
            // valid cutscore
            $this->messages->add('generate', 'Cut score '.$avg_score.' in range ('.$this->cutscore.','.$this->cutscore_max.')');
            return true;
        } else {
            $this->errors->add('generate', 'Cut Score of '.$avg_score.' not within range ('.$this->cutscore.', '.$this->cutscore_max.')');
            return false;
        }
    }

    /** 
     * Using IRT parameters from a mocked-up test, generates an estimated average score
     * Returns the score in percent
     * 
     * @param	array	a list of IRT scores in array like |discrimination|difficulty|guessing|
     * @param	double	a target theta to use for calculating random scores
     * @param	int		number of random people (scores) to generate
     * @param	int		number of iterations to go through (one run of population per iteration)
     * @return	double
     */
    private function _estimate_cut_score($params, $target_theta, $population=1000, $iterations=1)
    {
        $total_averages = 0;
        // # of 'outer' iterations
        for ($x=0; $x<$iterations; $x++) {
            $scores = 0;
            // population
            for ($i=0; $i<$population; $i++) {
                $rand_score = substr($this->_random_score($params, count($params), $target_theta), 0, 2);
                $scores += $rand_score;
            }
            $total_averages += $scores/$population;
        }

        return ($total_averages/$iterations)/100;
    }

    /** Return a random score for a test item given item response theory value
     * 
     * @param	array	item parameters
     * @param	int		number of items/parameters
     * @param	double	the theta value
     */
    private function _random_score($param, $paramcount, $theta)
    {
        $total = 0;
        $item = "";
        for ($i=0; $i<$paramcount; $i++) {
            if (rand(0, getrandmax())/getrandmax() <= $this->_item_response($param[$i][0], $param[$i][1], $param[$i][2], $theta)) {
                $total++;
                $item = $item."1";
            } else {
                $item = $item."0";
            }
        }
        $score = round(100*$total/$paramcount);
        return str_pad($score, 3).str_pad($total, 3).$item; //percentage, raw, items-right/wrong	
        //72 57 0101111110011111111100111111100101110111101111011011011110011101101111011011100
    }

    /**
     * Item Response Theory (IRT) score, returns result of the equation
     * 
     * @param	double	a - the discrimination parameter
     * @param	double	b - the difficulty parameter
     * @param	double	c - the guessing parameter
     * @param	double	theta - the person's ability level
     */
    private function _item_response($a, $b, $c, $theta)
    {
        return $c+(1.0-$c)/(1.0+exp(-1.0*$a*($theta-$b)));
    }


    /**
     * gets IRT parameters as an array
     */
    public function irtParams($temp_items, $temp_without_stats)
    {
        // ----------------------------------------------------------
        // LEGACY CODE
        // IRT parameters from each item
        // Gets all the correct irt params into an array
        $params = array();
        $total_checked = 1;
        foreach ($temp_items as $item) {
            // make sure we only use items with statistics being taken into account
            if ($total_checked > $temp_without_stats) {
                //discrimination|difficulty|guessing
                $params[] = array($item->stats->first()->discrimination, $item->stats->first()->difficulty, $item->stats->first()->guessing);
            }

            $total_checked++;
        }

        return $params;
    }

    /**
     * Checks the reading level for a set of items
     * 
     */
    public function checkReadingLevel($temp_items)
    {
        $test_string = "";

        foreach ($temp_items as $item) {
            $test_string .= $item->stem.'. '.$item->theAnswer->content.'. ';
        }

        $reading_level = Readinglevel::reading_level($test_string);

        if (($reading_level >= $this->readinglevel) && ($reading_level <= $this->readinglevel_max)) {
            $this->messages->add('generate', 'Reading level '.$reading_level.' in range ('.$this->readinglevel.','.$this->readinglevel_max.')');
            return true;
        } else {
            $this->errors->add('generate', 'Reading level of '.round($reading_level, 2).' not within range ('.$this->readinglevel.', '.$this->readinglevel_max.')');
            return false;
        }
    }

    /**
     * Eager loads items grouped by subjects for this exam
     */
    public function getItemPool()
    {
        if ($this->exam_id) {
            return Subject::with(
                ['testitems' => function ($query) {
                    $query->where('testitems.status', '=', 'active');
                    $query->where('exam_testitem.exam_id', '=', $this->exam_id);
                }],
                'testitems.enemies',
                'testitems.stats',
                'testitems.theAnswer',
                'testitems.distractors'
            )->get();
        } else {
            return null;
        }
    }

    /**
     * Generates a testform for this plan
     */
    public function generateForm()
    {
        // is there a testplan record?
        if ($this->id) {
            // make sure we haven't passed the max attempts defined for this testplan
            $attempt = Input::get('attempt', 1);

            if ($attempt > $this->max_attempts) {
                Session::flash('danger', 'No suitable testform could be generated in '.$this->max_attempts.' attempts.');
                return Response::json(['redirect' => route('testplans.index')]);
            }

            $start_time     = microtime(true);
            $conditions_met = false; // For end-checking of params

            // each param flag
            $valid_pvalue   = false;
            $valid_alpha    = false;
            $valid_cut      = false;
            $valid_reading  = false;

            $subjects       = $this->getItemPool();

            $total_items    = array_sum((array) $this->items_by_subject);

            // PVALUE CHECKS for entire itemset
            $attempt = 1;
            do {
                $pvalue_total   = 0;

                // Get items for each subject randomly
                $temp_items         = array();
                $temp_all_ids       = array();
                $temp_all_enemies   = array();
                $temp_without_stats = 0; // used for keeping track of how many items we've already ignored stats for

                // Cherry-pick some items
                foreach ($this->items_by_subject as $subject_id => $num_items) {
                    // if num_items is empty, skip to next subject!
                    if (empty($num_items)) {
                        continue;
                    }

                    $subject = $subjects->find($subject_id);

                    // Are there any items for this subject?
                    if (! empty($subject) && ! $subject->itemPool->isEmpty()) {
                        // NEED TO RANDOMIZE GROUP OF ITEMS SOMEHOW					
                        for ($i = 0; $i < $num_items; $i++) {
                            // set a limit here so it won't loop trying to find items forever
                            $item_lookup_start_time = microtime(true);
                            $item_picked = false;
                            do {
                                $current_time = microtime(true);
                                // have we spent over a minute looking for an item with suitable statistics?
                                if (($current_time - $item_lookup_start_time) > 20) {
                                    Session::flash('danger', 'Unable to find testitem with suitable statistics for subject: '.$subject->name);
                                    return Response::json(['redirect' => route('testplans.index')]);
                                }

                                // Pick random item 
                                $item = $subject->itemPool->random(1);

                                // Make sure item NOT in current list
                                if (in_array($item->id, $temp_all_ids)) {
                                    continue;
                                } // it's in the list, try again

                                // Ok.. now make sure it's not in enemies list
                                if (in_array($item->id, $temp_all_enemies)) {
                                    continue;
                                }

                                // Check that none of its enemies are either, if there ARE enemies
                                if (! $item->enemies->isEmpty()) {
                                    foreach ($item->enemies->lists('id')->all() as $enemy_id) {
                                        if (in_array($enemy_id, $temp_all_enemies)) {
                                            // enemies found, try new item!
                                            continue 2;
                                        }
                                    }
                                }

                                // Add this item to temp items, depending on if statistics match or not
                                if (($this->ignore_stats > 0) && ($temp_without_stats < $this->ignore_stats)) {
                                    // ignoring stats, we'll just add it
                                    $temp_items[] = $item;
                                    $temp_without_stats++;
                                } else {
                                    // Make sure PBS, and item pvalues fit
                                    if ($item->stats->first() && ($item->stats->first()->pvalue >= $this->item_pvalue) && ($item->stats->first()->pvalue <= $this->item_pvalue_max) && ($item->stats->first()->pbs >= $this->pbs)) {
                                        // stats match, add it!
                                        $temp_items[] = $item;

                                        // add p-value
                                        $pvalue_total += $item->stats->first()->pvalue;
                                    } else {
                                        // invalid pvalue for this item, skip it!
                                        continue;
                                    }
                                }

                                // Add this item's ID to temp_all_ids AND enemies
                                $temp_all_ids[]     = $item->id;
                                $temp_all_enemies[] = $item->id;

                                // Add this item's enemies to temp_all_enemies
                                $temp_all_enemies   = array_merge($temp_all_enemies, $item->enemies->lists('id')->all());

                                $item_picked = true;
                                //END
                            } while ($item_picked === false);
                        } // end for 0:num_items  
                    } // end if 'subject has items'
                    else {
                        Session::flash('danger', 'Unable to find testitems for subject: '.$subject->name);
                        return Response::json(['redirect' => route('testplans.index')]);
                    }
                }  // end foreach subject


                // DOUBLE CHECK THAT we don't have any duplicate items
                if (count(array_unique($temp_all_ids)) !== $total_items) {
                    $this->errors->add('generate', 'Error generating test -- the number of testitems found does not match the plan item total.');
                }


                // Check the P-VALUE
                // ARE WE IGNORING STATS for all items or just some??
                if ($this->ignore_stats >= $total_items) {
                    $valid_pvalue = true;
                } else {
                    // Get average p-value for all items
                    // Pvalue avg - make sure it is within plan range
                    $pvalue_avg = $pvalue_total / (count($temp_items) - $temp_without_stats);

                    // Is the pvalue of the items w/ statistics within plan parameters
                    if (($this->pvalue <= $pvalue_avg) && ($this->pvalue_max >= $pvalue_avg)) {
                        // valid pvalue
                        $this->messages->add('generate', 'P-value average ('.$pvalue_avg.') is between '.$this->pvalue.' and '.$this->pvalue_max);
                        $valid_pvalue = true;
                    } else {
                        // not a valid pvalue, make sure we haven't passed max attempts
                        if ($attempt >= $this->max_pvalue_attempts) {
                            Session::flash('danger', 'After '.$this->max_pvalue_attempts.' attempts, could NOT find test p-value between '.$plan->pvalue.' and '.$plan->pvalue_max);
                            return Response::json(['redirect' => route('testplans.index')]);
                        }
                    }
                }
            } while ($valid_pvalue === false);
            // END check for pvalue

            // DOUBLE CHECK # temp_items matches total items?
            if (count($temp_items) !== $total_items) {
                $this->errors->add('generate', "Error generating test. Number of items in pool doesn't match total test items");
            }

            // Ignoring stats for every item?
            if ($this->ignore_stats >= $total_items) {
                // fake it
                $valid_alpha = true;
                $valid_cut   = true;
                $this->messages->add('generate', 'Statistics being ignored for all items with exception of reading level.');
            } else {
                // calculate the stats

                $irtParams   = $this->irtParams($temp_items, $temp_without_stats);
                $valid_alpha = $this->checkAlpha($irtParams);
                $valid_cut   = $this->checkCutscore($irtParams);
            } // ignore stats check			

            $valid_reading = $this->checkReadingLevel($temp_items);
        

            // Are all the conditions met?
            if (($valid_pvalue === true) && ($valid_alpha === true) && ($valid_reading === true) && ($valid_cut === true)) {
                $conditions_met = true;
            }

            // Check if any errors now
            if ($conditions_met === true) {
                // return new test
                return new Collection($temp_items);
            } else {
                // some errors found
                return false;
            }
        }

        // No plan record
        return false;
    }
}
