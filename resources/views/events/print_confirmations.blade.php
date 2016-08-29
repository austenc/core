@extends('core::layouts.default')

@section('content')

@foreach($students as $s)

	<?php
    $exam = null;

    if ($s->knowledge) {
        $exam = array_get($exams, $s->knowledge->exam_id);
    } elseif ($s->skill) {
        $exam = array_get($skills, $s->skill->skillexam_id);
    }
    ?>

	@include('core::testing.partials.confirm', [
		'exam'  => $exam,
		'f'     => $facility,
		's'     => $s,
		'event' => $event
	])

@endforeach

@stop