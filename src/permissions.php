<?php

/*
|--------------------------------------------------------------------------
| 'Login As' Filters
|--------------------------------------------------------------------------
|
| Ensures only admins can use the 'login as' feature.
|
*/
Entrust::routeNeedsRole('students/*/loginas', ['Admin', 'Staff', 'Agency'], null, false);
Entrust::routeNeedsRole('facilities/*/loginas', ['Admin', 'Staff', 'Agency'], null, false);

/*
|--------------------------------------------------------------------------
| Admin Portal
|--------------------------------------------------------------------------
|
| Ensures only admins can use these features.
|
*/

// ADMIN ONLY
Entrust::routeNeedsRole('admins*', 'Admin');
Entrust::routeNeedsRole('staff*', 'Admin');
Entrust::routeNeedsRole('exams*', 'Admin');
Entrust::routeNeedsRole('skillexams*', 'Admin');
Entrust::routeNeedsRole('testforms*', 'Admin');
Entrust::routeNeedsRole('testplans*', 'Admin');
Entrust::routeNeedsRole('testitems*', 'Admin');
Entrust::routeNeedsRole('certifications*', 'Admin');
Entrust::routeNeedsRole('subjects*', 'Admin');
Entrust::routeNeedsRole('trainings*', 'Admin');
Entrust::routeNeedsRole('permissions*', 'Admin');
Entrust::routeNeedsRole('user/create*', 'Admin');
Entrust::routeNeedsRole('role/*/edit', 'Admin');
Entrust::routeNeedsRole('discipline/create', 'Admin');
Entrust::routeNeedsRole('discipline/*/edit', 'Admin');
Entrust::routeNeedsRole('utilities/merge/*', 'Admin');
// ADMIN/STAFF ONLY
Entrust::routeNeedsRole('logs*', ['Admin', 'Staff'], null, false);
Entrust::routeNeedsRole('agencies*', ['Admin', 'Staff'], null, false);
Entrust::routeNeedsRole('users', ['Admin', 'Staff'], null, false);
Entrust::routeNeedsRole('users/*/add_role', ['Admin', 'Staff'], null, false);
Entrust::routeNeedsRole('scores*', ['Admin', 'Staff'], null, false);
Entrust::routeNeedsRole('scantron*', ['Admin', 'Staff'], null, false);
Entrust::routeNeedsRole('adas*', ['Admin', 'Staff'], null, false);
// ADMIN/STAFF/AGENCY ONLY
Entrust::routeNeedsRole('generate/password', ['Admin', 'Staff', 'Agency'], null, false);
// ADMIN/STAFF/AGENCY/INSTRUCTOR ONLY
Entrust::routeNeedsRole('reports/*', ['Admin', 'Staff', 'Agency', 'Instructor', 'Facility'], null, false);
Entrust::routeNeedsRole('discipline/*/facilities/training', ['Admin', 'Staff', 'Agency', 'Instructor'], null, false);



/*
|--------------------------------------------------------------------------
| Facilities
|--------------------------------------------------------------------------
|
| Anything to do with facilities
|
*/
Entrust::routeNeedsRole('facilities', ['Admin', 'Staff', 'Agency', 'Facility'], null, false);
Entrust::routeNeedsRole('facilities/create', ['Admin', 'Staff'], null, false);
Entrust::routeNeedsRole('facilities/*/edit', ['Admin', 'Staff', 'Agency', 'Facility'], null, false);
Entrust::routeNeedsRole('facilities/*/instructors/*', ['Admin', 'Staff', 'Agency', 'Facility'], null, false);
Entrust::routeNeedsRole('facilities/*/directions', ['Admin', 'Staff', 'Agency', 'Facility', 'Student'], null, false);
Entrust::routeNeedsRole('facilities/*/archived', ['Admin', 'Staff', 'Agency'], null, false);
Entrust::routeNeedsRole('facilities/*/archived/update', ['Admin', 'Staff'], null, false);
Entrust::routeNeedsPermission('facilities/*/activate', 'facilities.activate');
Entrust::routeNeedsPermission('facilities/*/*/*/toggle', 'facilities.person.toggle');
Entrust::routeNeedsPermission('facilities/*/person/add', 'facilities.person.add');
Entrust::routeNeedsPermission('facilities/*/discipline/*/*/add', 'facilities.person.add');
Entrust::routeNeedsPermission('facilities/*/discipline/*/affiliate/attach', 'facilities.manage_affiliated');
Entrust::routeNeedsPermission('facilities/*/discipline/*/affiliate/*/remove', 'facilities.manage_affiliated');
Entrust::routeNeedsRole('facilities/*/discipline/*/students/archived', ['Admin', 'Staff', 'Agency'], null, false);

/*
|--------------------------------------------------------------------------
| Students
|--------------------------------------------------------------------------
|
| Anything to do with students
|
*/
Entrust::routeNeedsPermission('students', 'students.manage');
Entrust::routeNeedsPermission('students/*/edit', 'students.edit');
Entrust::routeNeedsPermission('students/create', 'students.create');
Entrust::routeNeedsPermission('students/add_training/*', 'students.manage_trainings');
Entrust::routeNeedsPermission('students/*/training/*/edit', 'students.manage_trainings');
Entrust::routeNeedsPermission('students/*/tests', 'students.view_test_history');
Entrust::routeNeedsPermission('students/*/training', 'students.view_training_history');
Entrust::routeNeedsPermission('students/*/training_detail/*', 'students.view_training_history');
Entrust::routeNeedsPermission('students/*/schedule/*', 'students.schedule');
Entrust::routeNeedsPermission('students/*/unschedule/*', 'students.unschedule');
Entrust::routeNeedsPermission('students/*/print-scanform/*', 'events.print_packet');
Entrust::routeNeedsPermission('students/*/duplicate', 'students.merge');
Entrust::routeNeedsPermission('students/*/activate', 'students.merge');
Entrust::routeNeedsPermission('students/*/hold/*/toggle', 'students.test.hold');
Entrust::routeNeedsPermission('students/*/attempt/*/toggle/*/*', 'students.attempt.modify');
Entrust::routeNeedsRole('students/*/history/reassign', ['Admin'], null, false);
Entrust::routeNeedsRole('students/*/training/*/archive', ['Admin', 'Staff'], null, false);
Entrust::routeNeedsRole('students/*/status/*', ['Admin', 'Staff'], null, false);
Entrust::routeNeedsRole('students/training/*/restore', ['Admin', 'Staff'], null, false);

/*
|--------------------------------------------------------------------------
| Instructors
|--------------------------------------------------------------------------
|
| Anything to do with instructors
|
*/
Entrust::routeNeedsRole('instructors', ['Admin', 'Staff', 'Agency'], null, false);
Entrust::routeNeedsRole('instructors/*/loginas', ['Admin', 'Staff', 'Agency'], null, false);
Entrust::routeNeedsRole('instructors/*/edit', ['Admin', 'Staff', 'Agency'], null, false);
Entrust::routeNeedsRole('instructors/create', ['Admin', 'Staff'], null, false);
Entrust::routeNeedsRole('instructors/*/archived', ['Admin', 'Staff'], null, false);
Entrust::routeNeedsRole('instructors/*/archived/update', ['Admin', 'Staff'], null, false);
Entrust::routeNeedsPermission('instructors/*/discipline/add', 'instructors.add.discipline');
Entrust::routeNeedsPermission('instructors/*/discipline/*/remove', 'instructors.remove.discipline');
Entrust::routeNeedsPermission('instructors/*/remap', 'instructors.remap');

/*
|--------------------------------------------------------------------------
| People
|--------------------------------------------------------------------------
|
| Anything to do with observers/proctors/actors/instructors (but not students)
|
*/
Entrust::routeNeedsPermission('*/*/restore', 'person.restore');
Entrust::routeNeedsPermission('*/*/archive', 'person.archive');
Entrust::routeNeedsPermission('*/*/discipline/*/facility/*/activate', 'person.toggle');
Entrust::routeNeedsPermission('*/*/discipline/*/facility/*/deactivate', 'person.toggle');

/*
|--------------------------------------------------------------------------
| Observers
|--------------------------------------------------------------------------
|
| Anything to do with observers (staff/admin only)
|
*/
Entrust::routeNeedsRole('observers*', ['Admin', 'Staff'], null, false);

/*
|--------------------------------------------------------------------------
| Actors
|--------------------------------------------------------------------------
|
| Anything to do with actors (staff/admin only)
|
*/
Entrust::routeNeedsRole('actors*', ['Admin', 'Staff'], null, false);

/*
|--------------------------------------------------------------------------
| Proctors
|--------------------------------------------------------------------------
|
| Anything to do with proctors (staff/admin only)
|
*/
Entrust::routeNeedsRole('proctors*', ['Admin', 'Staff'], null, false);

/*
|--------------------------------------------------------------------------
| Events
|--------------------------------------------------------------------------
|
| Anything to do with events
|
*/
Entrust::routeNeedsRole('events/pending*', ['Admin', 'Staff'], null, false);
Entrust::routeNeedsRole('events/*pending*', ['Admin', 'Staff'], null, false);

Entrust::routeNeedsPermission('events', 'events.manage');
// create
Entrust::routeNeedsPermission('events/create', 'events.create');
Entrust::routeNeedsPermission('events/creating', 'events.create');
Entrust::routeNeedsPermission('events/select_team', 'events.create');
// edit
Entrust::routeNeedsPermission('events/*/edit', 'events.edit');
// end event
Entrust::routeNeedsPermission('events/*/end', 'events.end');
// change test team
Entrust::routeNeedsPermission('events/*/change_team', 'events.change_team');
// lock/unlock
Entrust::routeNeedsPermission('events/*/lock', 'events.lock');
Entrust::routeNeedsPermission('events/*/unlock', 'events.lock');
// unschedule
Entrust::routeNeedsPermission('events/*/knowledge/*/student/*/unschedule', 'students.unschedule');
Entrust::routeNeedsPermission('events/*/skill/*/student/*/unschedule', 'students.unschedule');
// change seat limits
Entrust::routeNeedsPermission('events/*/knowledge/*/change_seats', 'events.change_seats');
Entrust::routeNeedsPermission('events/*/skill/*/change_seats', 'events.change_seats');
// change testforms/skilltests
Entrust::routeNeedsPermission('events/*/exams/*/student/*/*/change', 'events.manage_testforms');
Entrust::routeNeedsPermission('events/testforms/update', 'events.manage_testforms');
Entrust::routeNeedsPermission('events/skilltests/update', 'events.manage_testforms');
// scheduling
Entrust::routeNeedsPermission('events/*/knowledge/*/fill_seats', 'students.schedule');
Entrust::routeNeedsPermission('events/*/skill/*/fill_seats', 'students.schedule');
// release tests
Entrust::routeNeedsPermission('events/release_tests/*', 'events.release_tests');
// printing stuff
Entrust::routeNeedsPermission('scantron/*', 'events.print_packet');
Entrust::routeNeedsPermission('events/*/print-scanform*', 'events.print_packet');
Entrust::routeNeedsPermission('events/*/print/1250', 'events.print_1250');
// delete event
Entrust::routeNeedsPermission('events/*/delete', 'events.delete');

/*
|--------------------------------------------------------------------------
| Skill Testing
|--------------------------------------------------------------------------
|
| Anything to do with skills (skill testing)
|
*/
Entrust::routeNeedsRole('test/*/*/results', ['Admin', 'Staff', 'Student'], null, false);
Entrust::routeNeedsPermission('skills', 'skills.manage');
Entrust::routeNeedsPermission('skills/create', 'skills.manage');
Entrust::routeNeedsPermission('skills/*/edit', 'skills.manage');
Entrust::routeNeedsPermission('skills/generate', 'skills.manage');
Entrust::routeNeedsPermission('skills/addTask', 'skills.manage');
Entrust::routeNeedsPermission('skills/*/activate', 'skills.manage');
Entrust::routeNeedsPermission('skills/*/archive', 'skills.manage');
Entrust::routeNeedsPermission('skills/*/saveAs', 'skills.manage');
Entrust::routeNeedsPermission('skills/*/tasks*', 'skills.manage');
// Administer (record) skill test
Entrust::routeNeedsPermission('skills/*/begin', 'skills.begin');
Entrust::routeNeedsPermission('skills/inProgress*', 'skills.begin');
Entrust::routeNeedsPermission('skills/save', 'skills.begin');
// Skill Tasks
Entrust::routeNeedsPermission('tasks*', 'tasks.manage');
// Task Steps
Entrust::routeNeedsPermission('steps*', 'tasks.manage');
// Task Setups
Entrust::routeNeedsPermission('setups*', 'tasks.manage');

/*
|--------------------------------------------------------------------------
| Import
|--------------------------------------------------------------------------
|
| Anything to do with Importer
|
*/
Entrust::routeNeedsPermission('import*', 'import');
Entrust::routeNeedsPermission('truncate*', 'truncate');
