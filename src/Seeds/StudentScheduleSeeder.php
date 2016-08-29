<?php namespace Hdmaster\Core\Seeds;

use Faker\Factory as Faker;
use \Student;
use Illuminate\Database\Seeder;

class StudentScheduleSeeder extends Seeder
{

    public function run()
    {
        $faker = Faker::create();

        $knowScheduled = 0;
        $skillScheduled = 0;

        // get all students
        $students = Student::with([
            'passedExams',
            'passedSkills',
            'scheduledAttempts',
            'scheduledSkills'
        ])->get();

        foreach ($students as $student) {
            // passed exams
            $passedExamIds  = $student->passedExams->lists('id')->all();
            $passedSkillIds = $student->passedSkills->lists('id')->all();
            // scheduled exams
            $schedExamIds   = $student->scheduledAttempts->lists('exam_id')->all();
            $schedSkillIds  = $student->scheduledSkills->lists('skillexam_id')->all();

            // each eligible knowledge exam
            foreach ($student->eligibleExams as $exam) {
                // fraction of eligible exams should just stay as ready
                // do not schedule for this exam
                if ($faker->boolean(25)) {
                    continue;
                }

                // corequirements that have not yet been passed
                $remCorequiredSkills = array_diff($exam->corequired_skills->lists('id')->all(), $passedSkillIds);

                // find events containing the necessary exams/skills
                $eventPool = $student->findEvent($exam->discipline, [$exam->id], $remCorequiredSkills);

                if ($eventPool->isEmpty()) {
                    // couldnt find any events containing all needed exams.. try next exam
                    continue;
                }

                // choose one random event
                $event = $eventPool->random();

                // schedule student into original knowledge exam
                $schedSuccess = $event->scheduleKnowledgeStudent($student->id, $exam->id);

                if (! $schedSuccess) {
                    // failed to schedule student into event for whatever reason
                    continue;
                }

                // schedule student into any corequired skill exams
                foreach ($remCorequiredSkills as $coreqSkillId) {
                    if (! $event->scheduleSkillStudent($student->id, $coreqSkillId)) {
                        // do we also need to unschedule the above knowledge exam in this case?
                        // student couldnt be schedule into this corequired exam
                        // continue to checking next exam, all or nothing
                        continue 2;
                    }

                    $skillScheduled++;
                }

                $knowScheduled++;
            }
            

            // each eligible skill exam
            foreach ($student->eligibleSkills as $skill) {
                // fraction of eligible skills should just stay as ready
                // do not schedule for this skillexam
                if ($faker->boolean(25)) {
                    continue;
                }

                // corequirements that have not yet been passed
                $remCorequiredKnowledge = array_diff($skill->corequired_exams->lists('id')->all(), $passedExamIds);

                // find events containing the necessary exams/skills
                $eventPool = $student->findEvent($skill->discipline, $remCorequiredKnowledge, [$skill->id]);
                
                // couldnt find any events containing all needed exams.. 
                if ($eventPool->isEmpty()) {
                    continue;
                }

                // choose one random event
                $event = $eventPool->random();

                // schedule student into original skill exam
                $schedSuccess = $event->scheduleSkillStudent($student->id, $skill->id);

                // failed to schedule student into event for whatever reason
                if (! $schedSuccess) {
                    continue;
                }

                // schedule student into any corequired knowledge exams
                foreach ($remCorequiredKnowledge as $coreqExamId) {
                    if (! $event->scheduleKnowledgeStudent($student->id, $coreqExamId)) {
                        // do we also need to unschedule the above knowledge exam in this case?
                        // student couldnt be schedule into this corequired exam
                        // continue to checking next exam, all or nothing
                        continue 2;
                    }

                    $knowScheduled++;
                }

                $skillScheduled++;
            }
        }

        $this->command->info('Scheduled Knowledge -- '.$knowScheduled.' seeded!');
        $this->command->info('Scheduled Skills -- '.$skillScheduled.' seeded!');
    }
}
