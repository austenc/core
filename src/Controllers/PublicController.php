<?php namespace Hdmaster\Core\Controllers;

use View;
use Input;
use \Student;

class PublicController extends BaseController
{

    protected $student;

    public function __construct(Student $student)
    {
        $this->student = $student;
    }


    public function search()
    {
        $search  = Input::get('q');
        $results = null;
        
        // is there a search to perform?
        if ($search) {
            // make sure they have a first/last name
            // comment this to enable searches like 'r' or 'ri' 
            if ((strpos($search, ' ') === false) && (strpos($search, ',') === false)) {
                $search = '0';
            }
                
            // something submitted, try searching for it
            // TODO: refactor this
            $results = $this->student->with('certifications')->nameLike($search)->get()->all();
        }
        
        return View::make('core::public.search')->with('results', $results);
    }
}
