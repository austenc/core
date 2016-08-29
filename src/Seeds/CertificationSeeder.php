<?php namespace Hdmaster\Core\Seeds;

use Illuminate\Database\Seeder;
use \Certification;
use \Exam;
use \Skillexam;
use \Discipline;

class CertificationSeeder extends Seeder
{

    public function run()
    {
        \Eloquent::unguard();

        // dont seed if table already has records
        if (\DB::table('certifications')->exists()) {
            return;
        }

        // create certifications
        Certification::create([
            'discipline_id' => Discipline::all()->random(1)->id,
            'name'        => 'Cert A',
            'abbrev'        => 'CTA'
        ]);
        Certification::create([
            'discipline_id' => Discipline::all()->random(1)->id,
            'name'          => 'Cert B',
            'abbrev'        => 'CTB'
        ]);
        Certification::create([
            'discipline_id' => Discipline::all()->random(1)->id,
            'name'          => 'Cert C',
            'abbrev'        => 'CTC'
        ]);

        // required exams/skills
        foreach (Certification::all() as $cert) {
            $currDisc = Discipline::with('exams', 'skills')->find($cert->discipline_id);

            // required knowledge exams
            if (! $currDisc->exams->isEmpty()) {
                $reqExam = $currDisc->exams->random(1);
                $cert->required_exams()->sync([$reqExam->id]);
            }

            // required skill exams
            if (! $currDisc->skills->isEmpty()) {
                $reqSkills = $currDisc->skills->random(1);
                $cert->required_skills()->sync([$reqSkills->id]);
            }
        }

        $this->command->info('Certifications -- 3 seeded!');
    }
}
