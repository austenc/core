<?php namespace Hdmaster\Core\Seeds;

use \Student;
use \Certification;
use Illuminate\Database\Seeder;

class StudentCertificationSeeder extends Seeder
{
    public function run()
    {
        \Eloquent::unguard();

        // remove any existing students
        \DB::table('student_certification')->delete();
        $this->command->info('Student Certifications cleared!');

        $seeded = 0;

        // get all students
        $students = Student::with('passedExams', 'passedSkills')->get();
        $certs = Certification::with('required_exams', 'required_skills')->get();

        foreach ($students as $student) {
            // passed records
            $passedSkillIds = $student->passedSkills->lists('id')->all();
            $passedExamIds  = $student->passedExams->lists('id')->all();

            foreach ($certs as $cert) {
                // curr required
                $reqSkillIds = $cert->required_skills->lists('id')->all();
                $reqExamIds  = $cert->required_exams->lists('id')->all();

                $remReqSkillIds = array_diff($reqSkillIds, $passedSkillIds);
                $remReqExamIds  = array_diff($reqExamIds, $passedExamIds);

                // no remaining skill or exam requirements? all passed?
                if (empty($remReqExamIds) && empty($remReqSkillIds)) {
                    $certified = date('Y-m-d');
                    $expires   = date('Y-m-t', strtotime("+2 years"));

                    // insert student certification
                    $student->certifications()->attach($cert->id, [
                        'certified_at'    => $certified,
                        'expires_at'    => $expires
                    ]);

                    $seeded++;
                }
            }
        }

        $this->command->info('Student Certifications -- '.$seeded.' seeded!');
    }
}
