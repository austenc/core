<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FillPermissionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Permission::all()->isEmpty()) {
            /**
             * Define some permissions
             */
            // Admin / Staff login permissions
            $loginAs = Permission::create([
                'name'         => 'login_as',
                'display_name' => 'Login As Account'
            ])->id;


            // Users
            $usersManage = Permission::create([
                'name'         => 'users.manage',
                'display_name' => 'Manage All Users'
            ])->id;


            // People
            $personRestore = Permission::create([
                'name'         => 'person.restore',
                'display_name' => 'Restore Person'
            ])->id;
            $personToggle = Permission::create([
                'name'         => 'person.toggle',
                'display_name' => 'Activate/Deactivate Facility/Person Relation'
            ])->id;
            $personArchive = Permission::create([
                'name'         => 'person.archive',
                'display_name' => 'Archive Person'
            ])->id;
            $personManageFacilities = Permission::create([
                'name'         => 'person.manage_facilities',
                'display_name' => 'Manage Facilities for a Person'
            ])->id;
            $personManageRoles = Permission::create([
                'name'         => 'person.manage_roles',
                'display_name' => 'Manage Roles for a Person'
            ])->id;

            // Agency
            $agencyUpdatePassword = Permission::create([
                'name'         => 'agency.update_password',
                'display_name' => 'Update Agency Password'
            ])->id;
        

            // Students
            $studentManage = Permission::create([
                'name'         => 'students.manage',
                'display_name' => 'Manage Student'
            ])->id;
            $studentCreate = Permission::create([
                'name'         => 'students.create',
                'display_name' => 'Create Student'
            ])->id;
            $studentEdit = Permission::create([
                'name'         => 'students.edit',
                'display_name' => 'Edit Student'
            ])->id;
            $studentViewEvents = Permission::create([
                'name'         => 'students.view_events',
                'display_name' => 'View Student Events'
            ])->id;
            $studentViewExams = Permission::create([
                'name'         => 'students.view_exams',
                'display_name' => 'View Student Exams'
            ])->id;
            $studentViewTrainings = Permission::create([
                'name'         => 'students.view_trainings',
                'display_name' => 'View Student Trainings'
            ])->id;
            $studentUpdatePassword = Permission::create([
                'name'         => 'students.update_password',
                'display_name' => 'Update Student Password'
            ])->id;
            $studentManageTraining = Permission::create([
                'name'         => 'students.manage_trainings',
                'display_name' => 'Manage Student Trainings'
            ])->id;
            $studentViewTestHistory = Permission::create([
                'name'         => 'students.view_test_history',
                'display_name' => 'View Test History'
            ])->id;
            $studentViewFullTestHistory = Permission::create([
                'name'         => 'students.view_full_test_history',
                'display_name' => 'View Test History'
            ])->id;
            $studentViewTrainingHistory = Permission::create([
                'name'         => 'students.view_training_history',
                'display_name' => 'View Training History'
            ])->id;
            $studentSchedule = Permission::create([
                'name'         => 'students.schedule',
                'display_name' => 'Schedule Student'
            ])->id;
            $studentUnschedule = Permission::create([
                'name'         => 'students.unschedule',
                'display_name' => 'Unschedule Student'
            ])->id;
            $studentViewCerts = Permission::create([
                'name'            => 'students.view_certs',
                'display_name'    => 'View Student Certification History'
            ])->id;
            $studentMerge = Permission::create([
                'name'         => 'students.merge',
                'display_name' => 'Restore and Merge two Student records'
            ])->id;
            $studentModifyAttempt = Permission::create([
                'name'         => 'students.attempt.modify',        //'students.toggle.test.hold',
                'display_name' => 'Hold/Archive Student Test Attempt'
            ])->id;

            // Instructors
            $instructorManage = Permission::create([
                'name'         => 'instructors.manage',
                'display_name' => 'Manage Instructors'
            ])->id;
            $instructorCreate = Permission::create([
                'name'         => 'instructors.create',
                'display_name' => 'Create Instructors'
            ])->id;
            $instructorRemapAndDelete = Permission::create([
                'name'         => 'instructors.remap',          
                'display_name' => 'Remap & Delete Instructor'   
            ])->id;
            $instructorViewTeachingTraining = Permission::create([
                'name'         => 'instructors.view_teaching_training',
                'display_name' => 'View Teaching Trainings'
            ])->id;
            $instructorUpdatePassword = Permission::create([
                'name'         => 'instructors.update_password',
                'display_name' => 'Update Instructor Password'
            ])->id;
            $instructorAddDiscipline = Permission::create([
                'name'         => 'instructors.add.discipline',
                'display_name' => 'Add Instructor Discipline'
            ])->id;
            $instructorRemoveDiscipline = Permission::create([
                'name'         => 'instructors.remove.discipline',
                'display_name' => 'Deactivate Instructor Discipline'
            ])->id;
            $instructorManageTrainings = Permission::create([
                'name'         => 'instructors.manage_trainings',
                'display_name' => 'Manage Instructor Trainings'
            ])->id;


            // Proctors
            $proctorManage = Permission::create([
                'name'         => 'proctors.manage',
                'display_name' => 'Manage Proctor'
            ])->id;
            $proctorUpdatePassword = Permission::create([
                'name'         => 'proctors.update_password',
                'display_name' => 'Update Proctor Password'
            ])->id;


            // Actors
            $actorManage = Permission::create([
                'name'         => 'actors.manage',
                'display_name' => 'Manage Actor'
            ])->id;
            $actorUpdatePassword = Permission::create([
                'name'         => 'actors.update_password',
                'display_name' => 'Update Actor Password'
            ])->id;


            // Observers
            $observerManage = Permission::create([
                'name'         => 'observers.manage',
                'display_name' => 'Manage Observer'
            ])->id;
            $observerUpdatePassword = Permission::create([
                'name'         => 'observers.update_password',
                'display_name' => 'Update Observer Password'
            ])->id;


            // Facilities
            $facilityManage = Permission::create([
                'name'         => 'facilities.manage',
                'display_name' => 'Manage Facilities'
            ])->id;
            $facilityCreate = Permission::create([
                'name'         => 'facilities.create',
                'display_name' => 'Create Facility'
            ])->id;
            $facilityViewPeople = Permission::create([
                'name'         => 'facilities.view_people',
                'display_name' => 'View Facility People'
            ])->id;
            $facilityViewEvents = Permission::create([
                'name'         => 'facilities.view_events',
                'display_name' => 'View Facilty Events'
            ])->id;
            $facilityUpdatePassword = Permission::create([
                'name'         => 'facilities.update_password',
                'display_name' => 'Update Facility Password'
            ])->id;
            $facilityLoginAsOwnInstructor = Permission::create([
                'name'         => 'facilities.login_as_own_instructor',
                'display_name' => 'Login As Instructor'
            ])->id;
            $facilityActivate = Permission::create([
                'name'         => 'facilities.activate',
                'display_name' => 'Activate Facility'
            ])->id;
            $facilityArchive = Permission::create([
                'name'         => 'facilities.archive',
                'display_name' => 'Archive Facility'
            ])->id;
            $facilityManagePeople = Permission::create([
                'name'         => 'facilities.manage_people',
                'display_name' => 'Manage Facility People'
            ])->id;
            $facilityManageAffiliated = Permission::create([
                'name'         => 'facilities.manage_affiliated',
                'display_name' => 'Manage Facility Affiliated Programs'
            ])->id;
            $facilityTogglePeople = Permission::create([
                'name'         => 'facilities.person.toggle',
                'display_name' => 'Toggle Single Facility Person On/Off'
            ])->id;
            $facilityAddPerson = Permission::create([
                'name'         => 'facilities.person.add',
                'display_name' => 'Add New Person'
            ])->id;
            $facilityRemoveDiscipline = Permission::create([
                'name'         => 'facilities.remove.discipline',
                'display_name' => 'Deactivate Facility Discipline'
            ])->id;

            // Events
            $eventManage = Permission::create([
                'name'         => 'events.manage',
                'display_name' => 'Manage Events'
            ])->id;
            $eventEdit = Permission::create([
                'name'         => 'events.edit',
                'display_name' => 'Edit Events'
            ])->id;
            $eventCreate = Permission::create([
                'name'         => 'events.create',
                'display_name' => 'Create Events'
            ])->id;
            $eventReleaseTests = Permission::create([
                'name'           => 'events.release_tests',
                'display_name' => 'Release Tests'
            ])->id;
            $eventChangeTeam = Permission::create([
                'name'           => 'events.change_team',
                'display_name' => 'Change Event Test Team'
            ])->id;
            $eventChangeSeats = Permission::create([
                'name'           => 'events.change_seats',
                'display_name' => 'Change Event Seat Limits'
            ])->id;
            $eventChangeDateTime = Permission::create([
                'name'           => 'events.change_datetime',
                'display_name' => 'Change Event DateTime'
            ])->id;
            $eventRemoveStudent = Permission::create([
                'name'           => 'events.remove_student',
                'display_name' => 'Remove Student from Event'
            ])->id;
            $eventManageTestform = Permission::create([
                'name'           => 'events.manage_testforms',
                'display_name' => 'Manage Event Testforms'
            ])->id;
            $eventLock = Permission::create([
                'name'           => 'events.lock',
                'display_name' => 'Lock Event'
            ])->id;
            $eventEnd = Permission::create([
                'name'           => 'events.end',
                'display_name' => 'End Event'
            ])->id;
            $eventPrintPacket = Permission::create([
                'name'         => 'events.print_packet',
                'display_name' => 'Print Test Packets'
            ])->id;
            $eventPrintSkill  = Permission::create([
                'name'         => 'events.print_skill',
                'display_name' => 'Print Event Skills'
            ])->id;
            $eventPrint1250 = Permission::create([
                'name'         => 'events.print_1250',
                'display_name' => 'Print Event 1250 Report'
            ])->id;
            $eventDelete = Permission::create([
                'name'         => 'events.delete',
                'display_name' => 'Delete Event'
            ])->id;
            


            // skills
            $skillsManage = Permission::create([
                'name'           => 'skills.manage',
                'display_name' => 'Manage Skills'
            ])->id;
            $skillsBegin = Permission::create([    // proctor recording student skill test progress
                'name'           => 'skills.begin',
                'display_name' => 'Begin Skill Test'
            ])->id;
            $skillsScore = Permission::create([    // scoring a skill test (double check)
                'name'           => 'skills.scoring',
                'display_name' => 'Score Skill Test'
            ])->id;

            // tasks
            $tasksManage = Permission::create([
                'name'            => 'tasks.manage',
                'display_name'    => 'Manage Tasks'
            ])->id;

            // import
            $import = Permission::create([
                'name'            => 'import',
                'display_name'    => 'Import Abilities'
            ])->id;
            $truncate = Permission::create([
                'name'            => 'truncate',
                'display_name'    => 'Truncate Abilities'
            ])->id;



            /**
             * Assign permissions to the roles
             */

            // ADMIN
            $admin = Role::where('name', 'Admin')->first();
            $admin->perms()->sync([
                // users
                $usersManage,
                // people
                $personRestore,
                $personToggle,
                $personArchive,
                $personManageFacilities,
                $personManageRoles,
                // students
                $studentManage,
                $studentCreate,
                $studentEdit,
                $studentViewEvents,
                $studentViewExams,
                $studentViewTrainings,
                $studentUpdatePassword,
                $studentManageTraining,
                $studentViewTestHistory,
                $studentViewFullTestHistory,
                $studentViewTrainingHistory,
                $studentSchedule,
                $studentUnschedule,
                $studentViewCerts,
                $studentMerge,
                $studentModifyAttempt,
                // agency
                $agencyUpdatePassword,
                // proctors
                $proctorManage,
                $proctorUpdatePassword,
                // actors
                $actorManage,
                $actorUpdatePassword,
                // observers
                $observerManage,
                $observerUpdatePassword,
                // instructors
                $instructorManage,
                $instructorCreate,
                $instructorRemapAndDelete,
                $instructorViewTeachingTraining,
                $instructorUpdatePassword,
                $instructorAddDiscipline,
                $instructorRemoveDiscipline,
                $instructorManageTrainings,
                // facility
                $facilityManage,
                $facilityCreate,
                $facilityViewPeople,
                $facilityViewEvents,
                $facilityUpdatePassword,
                $facilityActivate,
                $facilityArchive,
                $facilityTogglePeople,
                $facilityManagePeople,
                $facilityAddPerson,
                $facilityManageAffiliated,
                $facilityRemoveDiscipline,
                // events
                $eventManage,
                $eventCreate,
                $eventEdit,
                $eventChangeTeam,
                $eventChangeSeats,
                $eventChangeDateTime,
                $eventRemoveStudent,
                $eventManageTestform,
                $eventReleaseTests,
                $eventLock,
                $eventEnd,
                $eventPrintPacket,
                $eventPrintSkill,
                $eventPrint1250,
                $eventDelete,
                // skills
                $skillsManage,
                $skillsBegin,
                $skillsScore,
                // tasks
                $tasksManage,
                // import
                $import,
                $truncate,
                // other
                $loginAs
            ]);

            // STAFF
            $admin = Role::where('name', 'Staff')->first();
            $admin->perms()->sync([
                // students
                $studentManage,
                $studentCreate,
                $studentEdit,
                $studentViewEvents,
                $studentViewExams,
                $studentViewTrainings,
                $studentUpdatePassword,
                $studentManageTraining,
                $studentViewTestHistory,
                $studentViewFullTestHistory,
                $studentViewTrainingHistory,
                $studentSchedule,
                $studentUnschedule,
                $studentViewCerts,
                $studentMerge,
                $studentModifyAttempt,
                // people
                $personRestore,
                $personToggle,
                $personArchive,
                $personManageFacilities,
                $personManageRoles,
                // agency
                $agencyUpdatePassword,
                // proctors
                $proctorManage,
                $proctorUpdatePassword,
                // actors
                $actorManage,
                $actorUpdatePassword,
                // observers
                $observerManage,
                $observerUpdatePassword,
                // facility
                $facilityManage,
                $facilityCreate,
                $facilityViewPeople,
                $facilityViewEvents,
                $facilityUpdatePassword,
                $facilityActivate,
                $facilityArchive,
                $facilityTogglePeople,
                $facilityManagePeople,
                $facilityAddPerson,
                $facilityManageAffiliated,
                $facilityRemoveDiscipline,
                // instructors
                $instructorManage,
                $instructorCreate,
                $instructorViewTeachingTraining,
                $instructorUpdatePassword,
                $instructorAddDiscipline,
                $instructorRemoveDiscipline,
                $instructorManageTrainings,
                // events
                $eventManage,
                $eventCreate,
                $eventEdit,
                $eventChangeTeam,
                $eventChangeSeats,
                $eventChangeDateTime,
                $eventRemoveStudent,
                $eventManageTestform,
                $eventReleaseTests,
                $eventLock,
                $eventEnd,
                $eventPrintPacket,
                $eventPrintSkill,
                $eventPrint1250,
                $eventDelete,
                // skills
                $skillsBegin,
                $skillsScore,
                // other
                $loginAs
            ]);

            // AGENCY
            $i = Role::where('name', 'Agency')->first();
            $i->perms()->sync([
                // students
                $studentManage,
                $studentEdit,
                $studentViewEvents,
                $studentViewExams,
                $studentViewTrainings,
                $studentUpdatePassword,
                $studentViewTestHistory,
                $studentViewFullTestHistory,
                $studentViewTrainingHistory,
                $studentViewCerts,
                $studentMerge,
                // facility
                $facilityManage,
                $facilityViewPeople,
                $facilityViewEvents,
                // instructors
                $instructorManage,
                $instructorViewTeachingTraining,
                // events
                $eventManage
            ]);

            // STUDENT
            $i = Role::where('name', 'Student')->first();
            $i->perms()->sync([
                // students
                $studentSchedule,
                $studentViewTestHistory,
                $studentViewTrainingHistory
            ]);

            // INSTRUCTOR
            $i = Role::where('name', 'Instructor')->first();
            $i->perms()->sync([
                // students
                $studentManage,
                $studentCreate,
                $studentEdit,
                $studentManageTraining,
                $studentSchedule,
                $studentViewExams,
                $studentMerge
            ]);

            // PROCTOR
            $p = Role::where('name', 'Proctor')->first();
            $p->perms()->sync([
                // events
                $eventManage,
                $eventEdit,
                // $eventEnd,
                // $eventReleaseTests,
                // skill test recording
                $skillsBegin
            ]);

            // ACTOR
            $a = Role::where('name', 'Actor')->first();
            $a->perms()->sync([
                // events
                $eventManage,
                $eventEdit
            ]);

            // OBSERVER
            $p = Role::where('name', 'Observer')->first();
            $p->perms()->sync([
                // events
                $eventManage,
                $eventEdit,
                $eventEnd,
                $eventReleaseTests,
                $eventPrint1250,
                $eventPrintSkill,
                $eventChangeTeam,
                $eventLock,
                // skill test recording
                $skillsBegin,
                // ability to change student pwd via popup on events.edit
                $studentUpdatePassword
            ]);

            // FACILITY
            $f = Role::where('name', 'Facility')->first();
            $f->perms()->sync([
                // students
                $studentManage,
                $studentEdit,
                $studentViewTrainings,
                $studentSchedule,
                $studentViewExams,
                // events
                $eventCreate,
                $eventManage,
                $eventEdit,
                $eventChangeTeam,
                $eventChangeSeats,
                $eventChangeDateTime,
                $eventRemoveStudent,
                // other
                $facilityLoginAsOwnInstructor
            ]);
        } // end 'if' check for if permissions already there
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // get rid of any permissions
        DB::table('permissions')->truncate();

        // get rid of any role / permission associations
        DB::table('permission_role')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
