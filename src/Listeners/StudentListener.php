<?php namespace Hdmaster\Core\Listeners;

class StudentListener
{

    /**
     * After a Student Training record is created/updated/deleted
     */
    public function finishedTraining($student, $training)
    {
    }

    /**
     * After a student finishes both tests (assuming they're taking both)
     */
    public function finishedTests($student, $attempt, $skill)
    {
        $student->refreshCertifications();
    }

    /**
     * After a Student finishes a knowledge exam
     */
    public function finishedKnowledge($student, $attempt)
    {
        $student->refreshCertifications();
    }

    /**
     * After a Student finishes a skill exam
     */
    public function finishedSkill($student, $attempt)
    {
        $student->refreshCertifications();
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Illuminate\Events\Dispatcher  $events
     * @return array
     */
    public function subscribe($events)
    {
        // Student Training
        $events->listen('student.training_added', StudentListener::class.'@finishedTraining');   // StudentTraining.addWithInput()
        $events->listen('student.training_updated', StudentListener::class.'@finishedTraining'); // StudentTraining.updateWithInput()

        // Student taking both exams
        $events->listen('student.finished_tests', StudentListener::class.'@finishedTests');

        // Student taking just knowledge
        $events->listen('student.finished_knowledge', StudentListener::class.'@finishedKnowledge');

        // Student taking just skill
        $events->listen('student.finished_skill', StudentListener::class.'@finishedSkill');
    }
}
