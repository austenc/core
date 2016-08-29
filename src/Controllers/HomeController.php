<?php namespace Hdmaster\Core\Controllers;

use View;
use Auth;
use \Testevent;

class HomeController extends BaseController
{


    public function index()
    {
        $user = Auth::user();

        // Logged in as admin?
        if ($user) {
            switch ($user->userable_type) {
                case 'Admin':
                case 'Staff':
                    return View::make('core::admin.home')->with([
                        'today'    => Testevent::with('facility', 'exams', 'observer')->today()->get(),
                        'upcoming' => Testevent::with('facility', 'exams', 'observer')->upcoming()->limit(10)->get()
                    ]);
                    break;

                case 'Agency':
                    return View::make('core::agency.home')->with([
                        'today'    => Testevent::with('facility', 'exams', 'observer')->today()->get(),
                        'upcoming' => Testevent::with('facility', 'exams', 'observer')->upcoming()->limit(10)->get()
                    ]);
                    break;

                case 'Student':
                    return View::make('core::students.home')->with([
                        'user'           => $user,
                        'certifications' => $user->userable->certifications()->get()
                    ]);
                    break;

                case 'Instructor':
                    return View::make('core::instructors.home')->with([
                        'user' => $user
                    ]);
                    break;

                case 'Facility':
                    return View::make('core::facilities.home')->with([
                        'user' => $user
                    ]);
                    break;

                case 'Observer':
                    return View::make('core::observers.home')->with([
                        'today'  => Testevent::with('facility', 'exams', 'observer')->where('observer_id', $user->userable->id)->today()->get(),
                        'user'   => $user,
                        'events' => $user->userable->all_future_events
                    ]);
                    break;

                case 'Proctor':
                    return View::make('core::proctors.home')->with([
                        'user' => $user
                    ]);
                    break;

                case 'Actor':
                    return View::make('core::actors.home')->with([
                        'user'     => $user
                    ]);
                    break;

                default:
                    // fall to default home view
            }
        }

        // Show typical homepage
        return View::make('core::home')->with('includeCalendar', true);
    }
}
