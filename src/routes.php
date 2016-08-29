<?php

Route::get('/flash', function() {
    return Redirect::route('home')->with('success', 'this is a test!');
});

// Homepage
Route::get('/', ['as' => 'home', 'uses' => 'Hdmaster\Core\Controllers\HomeController@index', 'before' => 'actionableTests']);

// Hides the warning message if on staging server
Route::get('/hide-staging-warning', ['as' => 'warning.hide_staging', 'uses' => function () {
    Session::put('staging.hide_warning', true);
    return Redirect::back();
}]);

// Error routes
Route::any('/403', function () {
    // use a view so we can redirect to this with Redirect::guest from error handler
    return Response::view('core::errors.403', [], 403);
});

// Log viewer -- assumes the logviewer package is loaded via composer (in state-level app, not core composer.json)
Route::get('logs', ['as' => 'logs.index', 'uses' => '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index', 'middleware' => 'auth']);

/*
|--------------------------------------------------------------------------
| User / Account Routes (most from Confide package)
|--------------------------------------------------------------------------
*/
Route::group(['namespace' => 'Hdmaster\Core\Controllers'], function () {
    Route::get('users', ['as' => 'users.index', 'uses' => 'UsersController@index']);
    Route::get('users/toggle-sidebar', ['as' => 'users.toggle_sidebar', 'uses' => 'UsersController@toggleSidebar']);
    Route::get('users/create', ['as' => 'users.create', 'uses' => 'UsersController@create']);
    Route::post('users', ['as' => 'users.store', 'uses' => 'UsersController@store']);
    Route::get('users/login', ['as' => 'users.login', 'uses' => 'UsersController@login']);
    Route::post('users/login', ['as' => 'users.do_login', 'uses' => 'UsersController@do_login']);
    Route::get('login', ['as' => 'login', 'uses' => 'UsersController@login']);
    Route::post('login', ['as' => 'do_login', 'uses' => 'UsersController@do_login']);
    Route::get('users/confirm/{code}', ['as' => 'users.confirm', 'uses' => 'UsersController@confirm']);
    Route::get('users/forgot', ['as' =>'users.forgot', 'uses' => 'UsersController@forgot_password']);
    Route::post('users/forgot', ['as' =>'users.forgot', 'uses' => 'UsersController@do_forgot_password']);
    Route::get('users/reset_password/{token}', ['as' =>'users.reset_password', 'uses' => 'UsersController@reset_password']);
    Route::post('users/reset_password', ['as' => 'users.do_reset_password', 'uses' => 'UsersController@do_reset_password']);
    Route::get('users/logout', ['as' =>'users.logout', 'uses' => 'UsersController@logout']);
    Route::get('logout/{timeout?}', ['as' =>'logout', 'uses' => 'UsersController@logout']);
    Route::get('users/{users}/edit', ['as' => 'users.edit', 'uses' => 'UsersController@edit', 'middleware' => 'auth']);
    Route::put('users/{users}/update', ['as' => 'users.update', 'uses' => 'UsersController@update', 'middleware' => 'auth']);
    Route::post('users/change', ['as' => 'users.change_role', 'uses' => 'UsersController@changeRole', 'middleware' => 'auth']);
    Route::get('users/{users}/add-role', ['as' => 'users.add_role', 'uses' => 'UsersController@addRole', 'middleware' => 'auth']);
    Route::post('users/store_role', ['as' => 'users.store_role', 'uses' => 'UsersController@storeRole', 'middleware' => 'auth']);
});


/*
|--------------------------------------------------------------------------
| (Protected via Auth) Other Routes
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth', 'namespace' => 'Hdmaster\Core\Controllers'], function () {
    // Edit any type of person by a type and user_id
    Route::get('person/edit/{type}/{user}', ['as' => 'person.edit', 'uses' => 'UsersController@editPersonByUser']);

    // User account page
    Route::get('account', ['as' => 'account', 'uses' => 'AccountController@index', 'before' => 'actionableTests']);
    Route::put('account/update/{type}/{id}', ['as' => 'account.update', 'uses' => 'AccountController@update']);
    Route::get('admins/{id}/edit', ['as' => 'admins.edit', 'uses' => 'AdminsController@edit']);
    Route::resource('admins', 'AdminsController');

    // Force change email
    Route::any('email/change', ['as' => 'email.change', 'uses' => 'UsersController@changeEmail']);
    // Generate fake email
    Route::get('generate/{type}/email', ['as' => 'generate.email', 'uses' => 'UsersController@fakeEmail']);
    // Generate random password
    Route::get('generate/password', ['as' => 'generate.password', 'uses' => 'UsersController@generatePassword']);
    // Staff
    Route::get('staff/{id}/edit/{type?}', ['as' => 'staff.edit', 'uses' => 'StaffController@edit']);
    Route::resource('staff', 'StaffController');
});


/*
|--------------------------------------------------------------------------
| Notifications
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth', 'namespace' => 'Hdmaster\Core\Controllers'], function () {
    Route::get('inbox', ['as' => 'notifications', 'uses' => 'UsersController@notifications', 'before' => 'actionableTests']);
    Route::post('inbox/update', ['as' => 'notifications.update', 'uses' => 'UsersController@updateNotifications']);
    Route::get('message/{id}', ['as' => 'notification.detail', 'uses' => 'UsersController@singleNotification', 'before' => 'actionableTests']);
    Route::get('message/{id}/unread', ['as' => 'notification.unread', 'uses' => 'UsersController@markNotificationUnread']);
    Route::get('message/{id}/delete', ['as' => 'notifications.delete', 'uses' => 'UsersController@deleteNotification']);
});


/*
|--------------------------------------------------------------------------
| Students
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth', 'namespace' => 'Hdmaster\Core\Controllers'], function () {
    // Search mass action
    Route::post('students/mass', ['as' => 'students.mass', 'uses' => 'StudentsController@mass']);
    // Student Populate
    Route::get('students/populate', ['as' => 'students.populate', 'uses' => 'StudentsController@populate', 'before' => 'nonProduction']);
    // Attaching an attempt image
    Route::match(['GET', 'POST'], 'students/attach/{attempt}/{type}', ['as' => 'students.attach_attempt_image', 'uses' => 'StudentsController@attachAttemptImage']);
    // Student Intermediate page
    Route::get('students/{id}/intermediate', ['as' => 'students.intermediate', 'uses' => 'StudentsController@intermediate']);
    // Search
    Route::match(['GET', 'POST'], 'students/search', ['as' => 'students.search', 'uses' => 'StudentsController@search']);
    Route::get('students/search/remove/{index}', ['as' => 'students.search.delete', 'uses' => 'StudentsController@searchDelete']);
    Route::get('students/search/clear', ['as' => 'students.search.clear', 'uses' => 'StudentsController@searchClear']);
    // Student owner (instructor owns student)
    Route::post('students/change_owner', ['as' => 'students.change_owner', 'uses' => 'StudentsController@changeOwner']);
    Route::get('students/{id}/change_owner', ['as' => 'students.change_single_owner', 'uses' => 'StudentsController@changeSingleOwner']);
    // Student Change Password (observer changing a students password)
    Route::match(['GET', 'POST'], 'students/{id}/change_password', ['as' => 'students.change_password', 'uses' => 'StudentsController@changePassword']);
    // Schedule (Knowledge/Skill)
    Route::get('students/{id}/knowledge/{examId}/schedule', ['as' => 'students.find.knowledge.event', 'uses' => 'StudentsController@findKnowledgeEvent']);
    Route::get('students/{id}/skill/{skillId}/schedule', ['as' => 'students.find.skill.event', 'uses' => 'StudentsController@findSkillEvent']);
    Route::post('students/knowledge/schedule', ['as' => 'students.schedule.knowledge.event', 'uses' => 'StudentsController@scheduleKnowledge']);
    Route::post('students/skill/schedule', ['as' => 'students.schedule.skill.event', 'uses' => 'StudentsController@scheduleSkill']);
    // Unschedule (Knowledge/Skill)
    Route::get('students/{student_id}/event/{event_id}/knowledge/{exam_id}/unschedule', ['as' => 'students.unschedule_knowledge', 'uses' => 'StudentsController@unscheduleKnowledge']);
    Route::get('students/{student_id}/event/{event_id}/skill/{skillexam_id}/unschedule', ['as' => 'students.unschedule_skill', 'uses' => 'StudentsController@unscheduleSkill']);
    // Student Resource (override students.edit)
    Route::get('students/{id}/edit', ['before' => 'prevent-access-ob-student', 'as' => 'students.edit', 'uses' => 'StudentsController@edit']);
    Route::resource('students', 'StudentsController');
    // Student Login As
    Route::get('students/{id}/loginas', ['as' => 'students.loginas', 'uses' => 'StudentsController@loginas']);
    // Student Generate Fake SSN
    Route::get('students/generate/ssn', ['as' => 'students.generate.ssn', 'uses' => 'StudentsController@fakeSsn']);
    // Student Schedule Detail
    Route::get('students/{id}/schedule/{attemptId}/{type}/detail', ['as' => 'students.scheduled.detail', 'uses' => 'StudentsController@scheduleDetail']);


    // Training
    // Add Training
    Route::get('students/{id}/training/add', ['as' => 'students.training.add.fresh', 'uses' => 'StudentsController@addTraining']);
    Route::get('students/{id}/discipline/{disciplineId}/training/add', ['uses' => 'StudentsController@addTraining']);
    Route::get('students/{id}/discipline/{disciplineId}/facility/{facilityId}/training/add', ['uses' => 'StudentsController@addTraining']);
    Route::get('students/{id}/discipline/{disciplineId}/facility/{facilityId}/training/{trainingId}/add', ['uses' => 'StudentsController@addTraining']);
    Route::post('students/{id}/training/store', ['as' => 'students.training.store', 'uses' => 'StudentsController@storeTraining']);
    // Update Training
    Route::get('students/{id}/training/{attempt_id}/edit', ['as' => 'students.training.edit', 'uses' => 'StudentsController@editTraining']);
    Route::post('students/{id}/training/update', ['as' => 'students.training.update', 'uses' => 'StudentsController@updateTraining']);
    // Archive and Restore Training
    Route::get('students/{id}/training/{attempt_id}/archive', ['as' => 'students.training.archive', 'uses' => 'StudentsController@archiveTraining']);
    Route::get('students/training/{training}/restore', ['as' => 'students.training.restore', 'uses' => 'StudentsController@restoreTraining']);
    // Other Training Views
    Route::get('students/{id}/trainings', ['as' => 'students.trainings', 'uses' => 'StudentsController@trainings', 'before' => 'actionableTests']);
    Route::get('students/{id}/training/{training_id}/detail', ['as' => 'students.training_detail', 'uses' => 'StudentsController@trainingDetail']);
    Route::get('students/{id}/training/{training_id}/certificate', ['as' => 'students.training_certificate', 'uses' => 'StudentsController@printTrainingCertificate']);
    // Get training expiration
    Route::any('students/training/expires', ['as' => 'students.training.expires', 'uses' => 'StudentsController@getTrainingExpiration']);
    // Get available trainings (add training)
    Route::get('students/{id}/discipline/{disciplineId}/available/trainings', ['as' => 'students.discipline.available.training', 'uses' => 'StudentsController@getAvailableDisciplineTrainings']);
    // Reassign Training
    Route::match(['GET', 'POST'], 'students/{id}/history/reassign', ['as' => 'students.history.reassign', 'uses' => 'StudentsController@reassignHistory']);

    // Testing (student portal)
    Route::get('students/{id}/tests', ['as' => 'students.tests', 'uses' => 'StudentsController@tests', 'before' => 'actionableTests']);

    // ADAs
    Route::get('students/{id}/add_ada', ['as' => 'students.add_ada', 'uses' => 'StudentsController@addAda']);
    Route::post('students/{id}/store_ada', ['as' => 'students.store_ada', 'uses' => 'StudentsController@storeAda']);
    Route::get('students/{id}/edit_ada/{ada}', ['as' => 'students.edit_ada', 'uses' => 'StudentsController@editAda']);
    Route::post('students/{id}/update/{ada}', ['as' => 'students.update_ada', 'uses' => 'StudentsController@updateAda']);
    // Archived
    Route::get('students/{id}/archived', ['as' => 'students.archived', 'uses' => 'StudentsController@archived']);
    Route::post('students/{id}/archived/update', ['as' => 'students.archived.update', 'uses' => 'StudentsController@archivedUpdate']);

    // Merge Student (add new student that already exists as inactive/archived)
    Route::get('students/{id}/duplicate', ['as' => 'students.duplicate', 'uses' => 'StudentsController@duplicate']);
    Route::post('students/{id}/merge', ['as' => 'students.merge', 'uses' => 'StudentsController@merge']);
    // Create with Params
    Route::get('students/create/discipline/{disciplineId}', ['uses' => 'StudentsController@create']);
    Route::get('students/create/discipline/{disciplineId}/training/{trainingId}', ['uses' => 'StudentsController@create']);
    Route::get('students/create/discipline/{disciplineId}/training/{trainingId}/program/{programId}', ['uses' => 'StudentsController@create']);
    // Student Account Status
    Route::get('students/{id}/status/edit', ['as' => 'students.status.edit', 'uses' => 'StudentsController@editStatus']);
    Route::post('students/{id}/status/update', ['as' => 'students.status.update', 'uses' => 'StudentsController@updateStatus']);

    // Student Attempt toggle Hold/Archive
    Route::get('students/{id}/attempt/{attemptId}/toggle/{type}/{action}', ['as' => 'students.attempt.toggle', 'uses' => 'StudentsController@toggleAttempt']);
});


/*
|--------------------------------------------------------------------------
| Instructors
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth', 'namespace' => 'Hdmaster\Core\Controllers'], function () {
    // Intermediate Login (program select)
    Route::get('instructors/login', ['as' => 'instructors.login', 'uses' => 'InstructorsController@login']);
    Route::post('instructors/login', ['as' => 'instructors.login.save', 'uses' => 'InstructorsController@updateLogin']);
    Route::get('instructors/login/reset', ['as' => 'instructors.login.reset', 'uses' => 'InstructorsController@resetLogin']);
    // Login As
    Route::get('instructors/{id}/loginas/{license?}', ['as' => 'instructors.loginas', 'uses' => 'InstructorsController@loginas']);
    // Swap Role (triggered on Intermediate Login)
    Route::post('instructors/role/swap', ['as' => 'instructors.role.swap', 'uses' => 'InstructorsController@swapRole']);
    // Populate
    Route::get('instructors/populate', ['as' => 'instructors.populate', 'uses' => 'InstructorsController@populate', 'before' => 'nonProduction']);
    // Resource
    Route::resource('instructors', 'InstructorsController');
    // Archived
    Route::get('instructors/{id}/archived', ['as' => 'instructors.archived', 'uses' => 'InstructorsController@archived']);
    Route::post('instructors/{id}/archived/update', ['as' => 'instructors.archived.update', 'uses' => 'InstructorsController@archivedUpdate']);
    // Search
    Route::match(['GET', 'POST'], 'instructors/search', ['as' => 'instructors.search', 'uses' => 'InstructorsController@search']);
    // Search mass action
    Route::post('instructors/mass', ['as' => 'instructors.mass', 'uses' => 'InstructorsController@mass']);
    // Remove search param
    Route::get('instructors/search/clear', ['as' => 'instructors.search.clear', 'uses' => 'InstructorsController@searchClear']);
    Route::get('instructors/search/delete{id}', ['as' => 'instructors.search.delete', 'uses' => 'InstructorsController@searchDelete']);
    // Activate/Deactivate Training
    Route::get('instructors/{id}/training/{trainingId}/activate', ['as' => 'instructors.training.activate', 'uses' => 'InstructorsController@activateTraining']);
    Route::get('instructors/{id}/training/{trainingId}/deactivate', ['as' => 'instructors.training.deactivate', 'uses' => 'InstructorsController@deactivateTraining']);

    // Deactivate Discipline
    Route::get('instructors/{id}/discipline/{disciplineId}/deactivate', ['as' => 'instructors.discipline.deactivate', 'uses' => 'InstructorsController@deactivateDiscipline']);

    // Show Archived Students per Discipline
    Route::get('instructors/{id}/discipline/{disciplineId}/students/archived', ['as' => 'instructors.discipline.students.archived', 'uses' => 'InstructorsController@archivedStudents']);

    // Remap & Delete Instructors
    Route::match(['GET', 'POST'], 'instructors/{id}/remap', ['as' => 'instructors.remap', 'uses' => 'InstructorsController@remapAndDelete']);
});

/*
|--------------------------------------------------------------------------
| Proctors
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth', 'namespace' => 'Hdmaster\Core\Controllers'], function () {
    // Populate
    Route::get('proctors/populate', ['as' => 'proctors.populate', 'uses' => 'ProctorsController@populate', 'before' => 'nonProduction']);
    // Resource
    Route::resource('proctors', 'ProctorsController');
    // Login As
    Route::get('proctors/{id}/loginas', ['as' => 'proctors.loginas', 'uses' => 'ProctorsController@loginas']);
    // Archived
    Route::get('proctors/{id}/archived', ['as' => 'proctors.archived', 'uses' => 'ProctorsController@archived']);
    Route::post('proctors/{id}/archived/update', ['as' => 'proctors.archived.update', 'uses' => 'ProctorsController@archivedUpdate']);
});

/*
|--------------------------------------------------------------------------
| Actors
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth', 'namespace' => 'Hdmaster\Core\Controllers'], function () {
    // Populate
    Route::get('actors/populate', ['as' => 'actors.populate', 'uses' => 'ActorsController@populate', 'before' => 'nonProduction']);
    // Resource
    Route::resource('actors', 'ActorsController');
    // Login As
    Route::get('actors/{id}/loginas', ['as' => 'actors.loginas', 'uses' => 'ActorsController@loginas']);
    // Archived
    Route::get('actors/{id}/archived', ['as' => 'actors.archived', 'uses' => 'ActorsController@archived']);
    Route::post('actors/{id}/archived/update', ['as' => 'actors.archived.update', 'uses' => 'ActorsController@archivedUpdate']);
});

/*
|--------------------------------------------------------------------------
| Observers
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth', 'namespace' => 'Hdmaster\Core\Controllers'], function () {
    // Populate
    Route::get('observers/populate', ['as' => 'observers.populate', 'uses' => 'ObserversController@populate', 'before' => 'nonProduction']);
    // Resource
    Route::resource('observers', 'ObserversController');
    // Login As
    Route::get('observers/{id}/loginas', ['as' => 'observers.loginas', 'uses' => 'ObserversController@loginas']);
    // Archived
    Route::get('observers/{id}/archived', ['as' => 'observers.archived', 'uses' => 'ObserversController@archived']);
    Route::post('observers/{id}/archived/update', ['as' => 'observers.archived.update', 'uses' => 'ObserversController@archivedUpdate']);
});

/*
|--------------------------------------------------------------------------
| Facilities
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth', 'namespace' => 'Hdmaster\Core\Controllers'], function () {
    // Login (discipline select)
    Route::get('facilities/select/login', ['as' => 'facilities.login', 'uses' => 'FacilitiesController@login']);
    Route::post('facilities/select/login', ['as' => 'facilities.login.save', 'uses' => 'FacilitiesController@updateLogin']);
    // Facility Populate
    Route::get('facilities/populate', ['as' => 'facilities.populate', 'uses' => 'FacilitiesController@populate', 'before' => 'nonProduction']);
    // Directions
    Route::get('facilities/{id}/directions', ['as' => 'facilities.directions', 'uses' => 'FacilitiesController@directions']);
    // Archived
    Route::get('facilities/{id}/archived', ['as' => 'facilities.archived', 'uses' => 'FacilitiesController@archived']);
    Route::post('facilities/{id}/archived/update', ['as' => 'facilities.archived.update', 'uses' => 'FacilitiesController@archivedUpdate']);
    // Activate
    Route::get('facilities/{id}/activate', ['as' => 'facilities.activate', 'uses' => 'FacilitiesController@activate']);
    // Search
    Route::post('facilities/search', ['as' => 'facilities.search', 'uses' => 'FacilitiesController@search']);
    Route::get('facilities/search/remove/{index}', ['as' => 'facilities.search.delete', 'uses' => 'FacilitiesController@searchDelete']);
    Route::get('facilities/search/clear', ['as' => 'facilities.search.clear', 'uses' => 'FacilitiesController@searchClear']);
    // RESTful Facility routes
    Route::resource('facilities', 'FacilitiesController');
    // Login as
    Route::get('facilities/{id}/loginas', ['as' => 'facilities.loginas', 'uses' => 'FacilitiesController@loginas']);
    // Add Person
    Route::get('facilities/{id}/person/add', ['as' => 'facilities.person.add', 'uses' => 'FacilitiesController@addPerson']);
    Route::get('facilities/{id}/discipline/{disciplineId}/{personType}/add', ['uses' => 'FacilitiesController@addPerson']);
    Route::post('facilities/{id}/person/add', ['as' => 'facilities.person.store', 'uses' => 'FacilitiesController@addPerson']);
    // Find Potential People (not already working for facility under requested discipline)
    // used on facilities.person.add
    Route::get('facilities/{id}/discipline/{disciplineId}/{personType}/get', ['as' => 'facilities.people.get', 'uses' => 'FacilitiesController@getPeople']);
    // Fetch all instructors under a particular discipline and training program
    // used on students.add_training view
    Route::get('facilities/{id}/discipline/{disciplineId}/training/{trainingId?}/instructors', ['as' => 'facilities.instructors', 'uses' => 'FacilitiesController@instructors']);
    Route::get('facilities/{id}/discipline/{disciplineId}/training/instructors', ['as' => 'facilities.instructors', 'uses' => 'FacilitiesController@instructors']);
    // Fetch all instructors, even inactive relations, (as json)
    // used reports.select_facility, after facility is selected to generate reports, it pulls list of all instructors under this program/facility
    Route::get('facilities/{id}/instructors/json', ['as' => 'facilities.instructors_json', 'uses' => 'FacilitiesController@instructorsJson']);

    // Add Discipline
    Route::get('facilities/{id}/discipline/add', ['as' => 'facilities.discipline.add', 'uses' => 'FacilitiesController@addDiscipline']);
    Route::post('facilities/{id}/discipline/store', ['as' => 'facilities.discipline.store', 'uses' => 'FacilitiesController@storeDiscipline']);
    // Discipline Students
    Route::get('facilities/{id}/discipline/{disciplineId}/students/archived', ['as' => 'facilities.discipline.students.archived', 'uses' => 'FacilitiesController@archivedStudents']);
    // Deactivate Discipline
    Route::get('facilities/{id}/discipline/{disciplineId}/deactivate', ['as' => 'facilities.discipline.deactivate', 'uses' => 'FacilitiesController@deactivateDiscipline']);

    // Past Events
    Route::get('facilities/{id}/events/past', ['as' => 'facilities.events.past', 'uses' => 'FacilitiesController@getPastEvents']);

    // Affiliated Training Programs
    Route::get('facilities/{id}/discipline/{disciplineId}/affiliate/attach', ['as' => 'facilities.affiliate.attach', 'uses' => 'FacilitiesController@attachAffiliate']);
    Route::post('facilities/discipline/affiliate/store', ['as' => 'facilities.affiliate.store', 'uses' => 'FacilitiesController@storeAffiliate']);
    Route::get('facilities/{id}/discipline/{disciplineId}/affiliate/{affiliateId}/remove', ['as' => 'facilities.affiliate.remove', 'uses' => 'FacilitiesController@removeAffiliate']);
});

/*
|--------------------------------------------------------------------------
| Importer
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth', 'namespace' => 'Hdmaster\Core\Controllers'], function () {
    // Capture
    Route::get('import', ['as' => 'import', 'uses' => 'ImportController@index']);
    Route::any('import/{id}/facilities', ['as' => 'import.facilities', 'uses' => 'ImportController@captureFacilities']);
    Route::any('import/{id}/instructors', ['as' => 'import.instructors', 'uses' => 'ImportController@captureInstructors']);
    // Capture Knowledge
    Route::any('import/{id}/knowledge', ['as' => 'import.knowledge', 'uses' => 'ImportController@captureKnowledge']);
    // Capture Skills
    Route::any('import/{id}/skilltasks', ['as' => 'import.skill.tasks', 'uses' => 'ImportController@captureSkillTasks']);
    Route::any('import/{id}/skillsteps', ['as' => 'import.skill.steps', 'uses' => 'ImportController@captureSkillSteps']);
    Route::any('import/{id}/skillsetups', ['as' => 'import.skill.setups', 'uses' => 'ImportController@captureSkillSetups']);
    // Truncate
    Route::any('truncate/testitems', ['as' => 'truncate.testitems', 'uses' => 'ImportController@truncateKnowledge']);
    Route::any('truncate/trainings', ['as' => 'truncate.trainings', 'uses' => 'ImportController@truncateTrainings']);
    Route::any('truncate/skills', ['as' => 'truncate.skills', 'uses' => 'ImportController@truncateSkills']);
    // Help Pages
    Route::get('import/task/help', ['as' => 'import.task.help', 'uses' => 'ImportController@taskHelp']);
    Route::get('import/setup/help', ['as' => 'import.setup.help', 'uses' => 'ImportController@setupHelp']);
    Route::get('import/step/help', ['as' => 'import.step.help', 'uses' => 'ImportController@stepHelp']);
    Route::get('import/instructor/help', ['as' => 'import.instructor.help', 'uses' => 'ImportController@instructorHelp']);
    Route::get('import/facility/help', ['as' => 'import.facility.help', 'uses' => 'ImportController@facilityHelp']);
});

/*
|--------------------------------------------------------------------------
| Agencies
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth', 'namespace' => 'Hdmaster\Core\Controllers'], function () {
    Route::resource('agencies', 'AgenciesController');
});

/*
|--------------------------------------------------------------------------
| Events
|--------------------------------------------------------------------------
*/
Route::get('events/json', ['as' => 'events.json', 'uses' => 'Hdmaster\Core\Controllers\EventsController@json']);

Route::group(['middleware' => 'auth', 'namespace' => 'Hdmaster\Core\Controllers'], function () {
    // Calendar view of all Events, need to be logged in to view
    // Similar view for not logged in, events.calendar_simple
    Route::get('events/calendar', ['as' => 'events.calendar', 'uses' => 'EventsController@calendar']);
    // Pending events
    Route::get('events/pending', ['as' => 'events.pending', 'uses' => 'EventsController@pending']);
    // Unschedule Student from Test
    Route::get('events/{id}/skill/{exam_id}/student/{student_id}/unschedule', ['as' => 'events.skill.unschedule', 'uses' => 'EventsController@unscheduleSkill']);
    Route::get('events/{id}/knowledge/{exam_id}/student/{student_id}/unschedule', ['as' => 'events.knowledge.unschedule', 'uses' => 'EventsController@unscheduleKnowledge']);
    // Change Seat Limits
    Route::match(['GET', 'POST'], 'events/{id}/knowledge/{exam_id}/change_seats', ['as' => 'events.knowledge.change_seats', 'uses' => 'EventsController@changeKnowledgeSeats']);
    Route::match(['GET', 'POST'], 'events/{id}/skill/{skill_id}/change_seats', ['as' => 'events.skill.change_seats', 'uses' => 'EventsController@changeSkillSeats']);
    // Fill Seats
    Route::match(['GET', 'POST'], 'events/{id}/skill/{skill_id}/fill_seats', ['as' => 'events.skill.fill_seats', 'uses' => 'EventsController@fillSkillSeats']);
    Route::match(['GET', 'POST'], 'events/{id}/knowledge/{exam_id}/fill_seats', ['as' => 'events.knowledge.fill_seats', 'uses' => 'EventsController@fillKnowledgeSeats']);

    Route::match(['GET', 'POST'], 'events/knowledge/search', ['as' => 'events.knowledge.search', 'uses' => 'EventsController@knowledgeSearch']);

    // Manage Proctor
    Route::match(['GET', 'POST'], 'events/select_team', ['as' => 'events.select_team', 'uses' => 'EventsController@selectTeam']);
    Route::match(['GET', 'POST'], 'events/{id}/change_team', ['as' => 'events.change_team', 'uses' => 'EventsController@changeTeam']);

    // Lock/Unlock
    Route::get('events/{id}/lock', ['as' => 'events.lock', 'uses' => 'EventsController@lock']);
    Route::get('events/{id}/unlock', ['as' => 'events.unlock', 'uses' => 'EventsController@unlock']);

    // Event Creation
    Route::post('events/creating', ['as' => 'events.creating', 'uses' => 'EventsController@creating']);
    // Testevent Scheduling (knowledge)
    Route::post('events/potential_schedules', function () {
        return Response::json([
            'students' => Student::whereIn('id', Input::get('students'))->get()
        ]);
    });

    // Delete
    Route::get('events/{id}/delete', ['as' => 'events.delete', 'uses' => 'EventsController@delete']);

    // Testing Process
    Route::get('events/{id}/release_tests', ['as' => 'events.release_tests', 'uses' => 'EventsController@releaseTests']);
    Route::get('events/{id}/end', ['as' => 'events.end', 'uses' => 'EventsController@end']);

    // Pending Events
    Route::get('events/{id}/edit_pending', ['as' => 'events.edit_pending', 'uses' => 'EventsController@editPending']);
    Route::put('events/{id}/update', ['as' => 'events.update_pending', 'uses' => 'EventsController@updatePending']);
    Route::get('pendingevents/{id}/test_team/populate', ['as' => 'events.pending.populate.test_team', 'uses' => 'EventsController@populateTestTeam']);

    // Print
    Route::get('events/{id}/print/1250', ['as' => 'events.1250', 'uses' => 'EventsController@print1250']);
    Route::get('events/{id}/print/admin', ['as' => 'events.admin_report', 'uses' => 'EventsController@printAdminReport']);
    Route::get('events/{id}/print/skill/{student?}', ['as' => 'events.print_skill', 'uses' => 'EventsController@printSkill']);
    Route::get('events/{id}/print/verification', ['as' => 'events.print_verification', 'uses' => 'EventsController@printVerification']);
    Route::get('events/{id}/print/confirmations', ['as' => 'events.print_confirmations', 'uses' => 'EventsController@printConfirmations']);

    // Create event intermediate page when site has other events on this date
    Route::get('events/{id}/site_report', ['as' => 'events.site_report', 'uses' => 'EventsController@siteReport']);

    // Route to just upload files from the event edit page
    Route::put('events/{id}/uploadFiles', ['as' => 'events.upload', 'uses' => 'EventsController@uploadEventFiles']);

    // Override Edit route (apply filter)
    Route::get('events/{id}/edit', ['before' => 'prevent-access-ob-event', 'as' => 'events.edit', 'uses' => 'EventsController@edit']);

    // Change Testform
    Route::get('events/{id}/exams/{examId}/student/{studentId}/testform/change', ['as' => 'events.testform.change', 'uses' => 'EventsController@changeTestform']);
    Route::get('events/{id}/skills/{skillId}/student/{studentId}/skilltest/change', ['as' => 'events.skilltest.change', 'uses' => 'EventsController@changeSkilltest']);
    Route::post('events/testform/update', ['as' => 'events.testform.update', 'uses' => 'EventsController@updateTestform']);
    Route::post('events/skilltest/update', ['as' => 'events.skilltest.update', 'uses' => 'EventsController@updateSkilltest']);
});

Route::group(['namespace' => 'Hdmaster\Core\Controllers'], function () {
    Route::resource('events', 'EventsController');
});

/*
|--------------------------------------------------------------------------
| Written Testbank
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth', 'namespace' => 'Hdmaster\Core\Controllers'], function () {
    // Written Testitems
    Route::get('testitems/enemies/{id?}', ['as' => 'testitems.enemies', 'uses' => 'TestitemsController@enemies']);
    Route::resource('testitems', 'TestitemsController');
    Route::post('testitems/{id}/activate', ['as' => 'testitems.activate', 'uses' => 'TestitemsController@activate']);
    Route::get('testitems/{id}/swap/{testform}', ['as' => 'testitems.swap', 'uses' => 'TestitemsController@swap']);

    // Written Testforms
    Route::resource('testforms', 'TestformsController');
    Route::post('testforms/{id}/activate', ['as' => 'testforms.activate', 'uses' => 'TestformsController@activate']);
    Route::get('testforms/{id}/archive', ['as' => 'testforms.archive', 'uses' => 'TestformsController@archive']);
    Route::get('testforms/{id}/scrambled', ['as' => 'testforms.scrambled', 'uses' => 'TestformsController@scrambled']);

    // Written Testplans
    Route::resource('testplans', 'TestplansController');
    Route::get('testplans/create/{exam}', ['as' => 'testplans.create', 'uses' => 'TestplansController@create']);
    Route::get('testplans/{exam}/generate', ['as' => 'testplans.generate', 'uses' => 'TestplansController@generate']);
    Route::get('testplans/{exam}/generating', ['as' => 'testplans.generating', 'uses' => 'TestplansController@generating']);
});

/*
|--------------------------------------------------------------------------
| Skill Testing Process
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth', 'namespace' => 'Hdmaster\Core\Controllers'], function () {

    Route::get('skills/{attempt_id}/initialize', ['as' => 'skills.initialize', 'uses' => 'SkillsController@initialize']);
    Route::get('skills/in_progress/{current?}', ['as' => 'skills.in_progress', 'uses' => 'SkillsController@inProgress']);
    Route::post('skills/save', ['as' => 'skills.save', 'uses' => 'SkillsController@save']);
    Route::get('skills/{attempt_id}/scoring', ['as' => 'skills.scoring', 'uses' => 'SkillsController@scoring']);
    Route::match(['GET', 'POST'], 'skills/{attempt_id}/end', ['as' => 'skills.end', 'uses' => 'SkillsController@end']);
    // View Detail
    Route::get('skills/{id}/detail', ['as' => 'skills.testing.show', 'uses' => 'SkillsController@show']);
});

/*
|--------------------------------------------------------------------------
| Skill Testbank
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth', 'namespace' => 'Hdmaster\Core\Controllers'], function () {
    // Skill Exams
    Route::resource('skillexams', 'SkillexamsController');

    // Skill Tests
    Route::get('skills/add_task/{id?}', ['as' => 'skills.add_task', 'uses' => 'SkillsController@addTask']);
    Route::get('skills/generate', ['as' => 'skills.generate', 'uses' => 'SkillsController@generate']);
    Route::get('skills/{id}/activate', ['as' => 'skills.activate', 'uses' => 'SkillsController@activate']);
    Route::get('skills/{id}/save_as', ['as' => 'skills.save_as', 'uses' => 'SkillsController@saveAs']);
    Route::get('skills/{id}/tasks/{task_id}/remove', ['as' => 'skills.remove_task', 'uses' => 'SkillsController@removeTask']);

    // Skill Tasks
    Route::get('tasks/review', ['as' => 'tasks.review', 'uses' => 'TasksController@review']);
    Route::get('tasks/enemies/{id?}', ['as' => 'tasks.enemies', 'uses' => 'TasksController@enemies']);
    Route::resource('tasks', 'TasksController');
    Route::get('tasks/{id}/activate', ['as' => 'tasks.activate', 'uses' => 'TasksController@activate']);
    Route::get('tasks/{id}/save_as', ['as' => 'tasks.save_as', 'uses' => 'TasksController@saveAs']);
    Route::get('tasks/{id}/print', ['as' => 'tasks.print', 'uses' => 'TasksController@printTask']);

    // Tasks Search
    Route::post('tasks/search', ['as' => 'tasks.search', 'uses' => 'TasksController@search']);
    Route::get('tasks/search/remove/{index}', ['as' => 'tasks.search.delete', 'uses' => 'TasksController@searchDelete']);
    Route::get('tasks/search/clear', ['as' => 'tasks.search.clear', 'uses' => 'TasksController@searchClear']);

    // Task Setups
    Route::get('setups/{id}/remove',  ['as' => 'setups.remove', 'uses' => 'TasksController@removeSetup']);

    // Task Steps
    Route::get('steps',  ['as' => 'steps.index', 'uses' => 'StepsController@index']);
    Route::get('steps/{id}/edit',  ['as' => 'steps.edit', 'uses' => 'StepsController@edit']);
    Route::put('steps/{id}/update',  ['as' => 'steps.update', 'uses' => 'StepsController@update']);
    Route::get('steps/{id}/remove',  ['as' => 'steps.remove', 'uses' => 'StepsController@remove']);
    Route::get('steps/{id}/unflag',  ['as' => 'steps.unflag', 'uses' => 'StepsController@unflag']);
    Route::get('steps/{id}/preview_paper', ['as' => 'steps.preview.paper', 'uses' => 'StepsController@previewPaper']);
    Route::get('steps/{id}/preview_web', ['as' => 'steps.preview.web', 'uses' => 'StepsController@previewWeb']);

    // Variable step input (add/update/delete)
    Route::get('steps/{step_id}/input/add', ['as' => 'steps.input.add', 'uses' => 'StepsController@addInput']);
    Route::put('steps/{step_id}/input/store', ['as' => 'steps.input.store', 'uses' => 'StepsController@storeInput']);
    Route::get('steps/{step_id}/input/{input_id}/edit', ['as' => 'steps.input.edit', 'uses' => 'StepsController@editInput']);
    Route::put('steps/{step_id}/input/{input_id}/update', ['as' => 'steps.input.update', 'uses' => 'StepsController@updateInput']);
    Route::get('steps/{step_id}/update_outcome', ['as' => 'steps.outcome.update', 'uses' => 'StepsController@updateOutcome']);
    Route::get('steps/{step_id}/input/{input_id}/delete', ['as' => 'steps.input.delete', 'uses' => 'StepsController@deleteInput']);

    // Define resourceful route last to avoid routing conflicts with other /skill/{var}/xyz type routes
    Route::resource('skills', 'SkillsController');
});

/*
|--------------------------------------------------------------------------
| Disciplines, Trainings, Exams, Certifications, ADAs
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth', 'namespace' => 'Hdmaster\Core\Controllers'], function () {

    // Disciplines
    Route::get('discipline/{id}/facilities/training', ['as' => 'discipline.training.facilities', 'uses' => 'DisciplineController@getTrainingFacilities']);
    Route::resource('discipline', 'DisciplineController');

    // Trainings
    Route::resource('trainings', 'TrainingsController');

    // Subjects
    Route::resource('subjects', 'SubjectsController');

    // Exams
    Route::get('exams/{id}/subject/{subjectId}/remove', ['as' => 'exams.subject.remove', 'uses' => 'ExamsController@removeSubject']);
    Route::resource('exams', 'ExamsController');

    // Certifications
    Route::resource('certifications', 'CertificationsController');

    // ADAs
    Route::resource('adas', 'AdasController');
});

/*
|--------------------------------------------------------------------------
| Written Testing Process
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth', 'namespace' => 'Hdmaster\Core\Controllers'], function () {

    // Print a test confirmation page
    Route::get('test/{type}/{attempt}/confirm', ['as' => 'testing.confirm', 'uses' => 'TestingController@confirm']);
    // Resend email notification for test confirmation page
    Route::get('test/{type}/{attempt}/confirm-email', ['as' => 'testing.email', 'uses' => 'TestingController@confirmEmail']);
    // Print a test results page
    Route::get('test/{type}/{attempt}/results', ['as' => 'testing.results_letter', 'uses' => 'TestingController@results']);
    // Taking a Written
    Route::get('testing/{attempt}/detail', ['as' => 'testing.show', 'uses' => 'TestingController@show', 'before' => 'actionableTests|attemptMatchesStudent']);
    Route::get('testing/{question?}', ['as' => 'testing.index', 'uses' => 'TestingController@index']);
    Route::get('testing/{attempt}/start', ['as' => 'testing.start', 'uses' => 'TestingController@start', 'before' => 'attemptMatchesStudent']);
    Route::get('testing/{attempt}/resume', ['as' => 'testing.resume', 'uses' => 'TestingController@resume', 'before' => 'attemptMatchesStudent']);
    Route::post('testing/{attempt}/initialize', ['as' => 'testing.initialize', 'uses' => 'TestingController@initialize', 'before' => 'attemptMatchesStudent']);
    Route::post('testing/save', ['as' => 'testing.save', 'uses' => 'TestingController@save']);
});

/*
|--------------------------------------------------------------------------
| Roles / Permissions Management GUI
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth', 'namespace' => 'Hdmaster\Core\Controllers'], function () {
    Route::get('permissions', ['as' => 'permissions', 'uses' => 'PermissionsController@index']);
    Route::get('role/{role}/edit', ['as' => 'permissions.edit_role', 'uses' => 'PermissionsController@editRole']);
    Route::post('role/{role}/update', ['as' => 'permissions.update_role', 'uses' => 'PermissionsController@updateRole']);
});

/*
|--------------------------------------------------------------------------
| Public Portal
|--------------------------------------------------------------------------
*/
Route::group(['namespace' => 'Hdmaster\Core\Controllers'], function () {
    Route::get('/search', ['as' => 'public.search', 'uses' => 'PublicController@search']);
});

/*
|--------------------------------------------------------------------------
| Reporting
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth', 'namespace' => 'Hdmaster\Core\Controllers'], function () {
    Route::get('reports', ['as' => 'reports.index', 'uses' => 'ReportsController@index']);
    Route::post('reports/generate', ['as' => 'reports.generate', 'uses' => 'ReportsController@generate']);

    // Find facility/instructor by license
    Route::get('reports/discipline/{disciplineId}/{license?}', ['as' => 'reports.find_license', 'uses' => 'ReportsController@findLicense']);

    Route::get('reports/retake_summary/{disciplineId}/{type}/{id}/{license?}/{from?}/{to?}', ['as' => 'reports.retake_summary', 'uses' => 'ReportsController@retakeSummary']);
    Route::get('reports/site_summary/{disciplineId}/{license?}/{from?}/{to?}', ['as' => 'reports.site_summary', 'uses' => 'ReportsController@siteSummary']);
    Route::get('reports/scheduled_exams/{disciplineId}/{type}/{id}/{license}/{from?}/{to?}', ['as' => 'reports.scheduled_exams', 'uses' => 'ReportsController@scheduledExams']);
    Route::get('reports/skills_detail/{disciplineId}/{type}/{id}/{license}/{from?}/{to?}', ['as' => 'reports.skills_detail', 'uses' => 'ReportsController@skillsDetail']);
    Route::get('reports/knowledge_detail/{disciplineId}/{type}/{id}/{license}/{from?}/{to?}', ['as' => 'reports.knowledge_detail', 'uses' => 'ReportsController@knowledgeDetail']);

    Route::get('reports/select_facility/{disciplineId}/{type}/{id}/{license}/{from?}/{to?}', ['as' => 'reports.select_facility', 'uses' => 'ReportsController@selectFacility']);
    Route::match(['GET', 'POST'], 'reports/passfail/{disciplineId}/{type}/{id}/{license}/{from?}/{to?}', ['as' => 'reports.pass_fail', 'uses' => 'ReportsController@passFail']);

});

/*
|--------------------------------------------------------------------------
| Accounting Routes
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth', 'namespace' => 'Hdmaster\Core\Controllers'], function () {
    Route::get('accounting/billrates', ['as' => 'accounting.billrates', 'uses' => 'BillingRatesController@index']);
    Route::get('accounting/billrates/create', ['as' => 'billingrate.create', 'uses' => 'BillingRatesController@create']);
    Route::get('accounting/billrates/{id}/edit', ['as' => 'billingrate.edit', 'uses' => 'BillingRatesController@edit']);
    Route::post('accounting/billrates/store', ['as' => 'billingrate.store', 'uses' => 'BillingRatesController@store']);
    Route::post('accounting/billrates/update', ['as' => 'billingrate.update', 'uses' => 'BillingRatesController@update']);

    Route::get('accounting/invoice', ['as' => 'accounting.invoice', 'uses' => 'BillingController@index']);
    Route::get('accounting/invoice/{start}/{end}/{discipine_id}/csv', ['as' => 'accounting.csv', 'uses' => 'BillingController@invoiceCsv']);
    Route::post('accounting/gettests', ['as' => 'accounting.gettests', 'uses' => 'BillingController@getTests']);
    Route::post('accounting/getbilling', ['as' => 'accounting.getbilling', 'uses' => 'BillingController@getBilling']);
    Route::any('accounting/invoiced', ['as' => 'accounting.invoiced', 'uses' => 'BillingController@markInvoiced']);

    Route::get('accounting/payrates', ['as' => 'accounting.payrates', 'uses' => 'PayablesController@payrates']);
    Route::get('accounting/billing', ['as' => 'accounting.billing', 'uses' => 'PayablesController@billing']);
    Route::get('accounting/billing/manage', ['as' => 'accounting.billing.manage', 'uses' => 'BillingController@manageBilling']);
    Route::get('accounting/billing/update/{id}/{type}/{status}', ['uses' => 'BillingController@updateBilling']);
    Route::get('accounting/payment/update/{id}/{type}/{status}', ['uses' => 'BillingController@updateStudentBilling']);

    Route::get('accounting/payrates/create', ['as' => 'payrates.create', 'uses' => 'PayableRateController@create']);
    Route::post('accounting/payrates/store', ['as' => 'payrates.store', 'uses' => 'PayableRateController@store']);
    Route::get('accounting/payrate/{id}/edit', ['as' => 'payrate.edit', 'uses' => 'PayableRateController@edit']);
    Route::post('accounting/payrate/update', ['as' => 'payrate.update', 'uses' => 'PayableRateController@update']);

    Route::get('accounting/pay/observer/{id}', ['as' => 'pay.observer', 'uses' => 'PayablesController@payObserver']);
    Route::post('accounting/pay/observer/process', ['as' => 'accounting.observer.processpayment', 'uses' => 'PayablesController@processObserverPayment']);
    Route::post('accounting/pay/all', ['as' => 'accounting.payallobservers', 'uses' => 'PayablesController@processAllObservers']);
    Route::post('accounting/pay/all/process', ['as' => 'accounting.observer.processallpayments', 'uses' => 'PayablesController@processAllObserverPayments']);
});

/*
|--------------------------------------------------------------------------
| People
|    - Instructor, Actor, Proctor, Observer, Students
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth', 'namespace' => 'Hdmaster\Core\Controllers'], function () {
    // Activate/Deactivate
    Route::get('{type}/{id}/discipline/{disciplineId}/facility/{facilityId}/{status}', ['as' => 'person.toggle', 'uses' => 'PersonController@toggle']);
    // Restore
    Route::get('{type}/{id}/restore', ['as' => 'person.restore', 'uses' => 'PersonController@restore']);
    // Archive
    Route::get('{type}/{id}/archive', ['as' => 'person.archive', 'uses' => 'PersonController@archive']);
    // Add Facility
    Route::get('{type}/{id}/facility/add', ['as' => 'person.facility.add', 'uses' => 'PersonController@addFacility']);
    Route::get('{type}/{id}/discipline/{disciplineId}/facility/add', ['uses' => 'PersonController@addFacility']);
    Route::post('{type}/{id}/facility/store', ['as' => 'person.facility.store', 'uses' => 'PersonController@storeFacility']);

    // Get all Facilities that would be available for new activation for a Person
    // Facilities already connected active or deactive are not included in returned json
    Route::get('{type}/{id}/discipline/{disciplineId}/facility/available', ['uses' => 'PersonController@availableFacilities']);
});

/*
|--------------------------------------------------------------------------
| Scantron Form
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth', 'namespace' => 'Hdmaster\Core\Controllers'], function () {
    Route::get('/scantron/adjust', ['as' => 'scantron.adjust', 'uses' => 'ScantronController@adjust']);
    Route::get('/scantron/example/{v?}/{h?}', ['as' => 'scantron.example', 'uses' => 'ScantronController@example']);
    Route::post('/scantron/save-offsets', ['as' => 'scantron.save_offsets', 'uses' => 'ScantronController@saveOffsets']);
    // print a single scanform for a student
    Route::get('students/{id}/print-scanform/{event}', ['as' => 'scantron.print_single', 'uses' => 'ScantronController@printSingle']);
    // print all scanforms for a given event
    Route::get('events/{id}/print-scanforms', ['as' => 'scantron.print_multiple', 'uses' => 'ScantronController@printMultiple']);
});

/*
|--------------------------------------------------------------------------
| Utilities
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth', 'namespace' => 'Hdmaster\Core\Controllers'], function () {
    Route::get('/utilities/users/merge', ['as' => 'utilities.users.merge', 'uses' => 'UtilitiesController@mergeUsers']);
    Route::post('/utilities/users/merge/do', ['as' => 'utilities.users.merge.do', 'uses' => 'UtilitiesController@doMergeUsers']);

    Route::match(['GET', 'POST'], '/utilities/test/history', ['as' => 'utilities.test.history', 'uses' => 'UtilitiesController@testHistory']);
});

/*
|--------------------------------------------------------------------------
| Paper Test Scoring / Review
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth', 'namespace' => 'Hdmaster\Core\Controllers'], function () {
    // list of pending scores
    Route::get('scores/pending', ['as' => 'scores.pending', 'uses' => 'ScoresController@pending']);
    Route::get('scores/review/{first}/{second?}', ['as' => 'scores.review', 'uses' => 'ScoresController@review']);
    Route::post('scores/update', ['as' => 'scores.update', 'uses' => 'ScoresController@update']);
    Route::get('scores/revision/{revision}', ['as' => 'scores.revision', 'uses' => 'ScoresController@revision']);
});

/*
|--------------------------------------------------------------------------
| API
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'api', 'namespace' => 'Hdmaster\Core\Controllers'], function () {
    Route::any('scan', ['as' => 'api.scan', 'uses' => 'ApiController@index']);
});

// CSRF PROTECT
Route::when('*', 'csrf-except', ['post', 'put', 'delete']);

/*
|--------------------------------------------------------------------------
| ZIP LOOKUP
|--------------------------------------------------------------------------
*/
Route::any('/zip/{id}/lookup', function ($id) {
    $z = new PragmaRX\ZipCode\ZipCode;
    $z->setCountry('US');

    $res = $z->find($id)->toArray();

    $lookup = ['city' => '', 'state' => ''];

    if (is_array($res) && $res['success']) {
        foreach ($res['addresses'] as $addr) {
            $lookup['city']  = $addr['place'];
            $lookup['state'] = $addr['state_id'];

            break;
        }
    }

    return json_encode($lookup);
});
