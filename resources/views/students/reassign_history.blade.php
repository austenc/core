@extends('core::layouts.default')

@section('content')
{!! Form::open(['route' => ['students.history.reassign', $student->id]]) !!}

    <div class="col-md-9">
        <div class="row">
            <div class="col-xs-8">
                <h1>Reassign History <small>{{ $student->fullname }}</small></h1>
            </div>
            <div class="col-xs-4 back-link">
                <a href="{{ route('students.edit', $student->id) }}" class="btn btn-link pull-right">{!! Icon::arrow_left() !!} Back to {{ Lang::choice('core::terms.student', 1) }}</a>
            </div>
        </div>

        <div class="well">
            <div class="form-group">
                {!! Form::label('ssn', 'Connect to SSN') !!} @include('core::partials.required')
                {!! Form::text('ssn', '') !!}
                <span class="text-danger">{{ $errors->first('ssn') }}</span>    
            </div>

            <div class="form-group">
                {!! Form::label('rev_ssn', 'Reverse SSN') !!} @include('core::partials.required')
                {!! Form::text('rev_ssn', '') !!}
                <span class="text-danger">{{ $errors->first('rev_ssn') }}</span>
            </div>
        </div>

        <p class="lead">Select Trainings</p>
        <div class="well">
            @if($student->allStudentTrainings->isEmpty())
                No Trainings
            @else
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th></th>
                        <th>Training</th>
                        <th>{{ Lang::choice('core::terms.facility_training', 1) }}</th>
                        <th>{{ Lang::choice('core::terms.instructor', 1) }}</th>
                        <th>Started</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Trainings --}}
                    @foreach($student->allStudentTrainings as $tr)
                        <tr data-clickable-row>
                            <td>{!! Form::checkbox('move_training_ids[]', $tr->id) !!}</td>
                            <td>{{ $tr->training->name }}</td>
                            <td>{{ $tr->facility->name }}</td>
                            <td>{{ $tr->instructor->fullname }}</td>
                            <td class="monospace">{{ $tr->started }}</td>
                            <td>
                                 @if($tr->status == 'passed')
                                    <span class="label label-success">
                                @elseif($tr->status == 'failed')
                                    <span class="label label-danger">
                                @else
                                    <span class="label label-warning">
                                @endif
                                    {{ ucfirst($tr->status) }}
                                </span>
                            </td>
                            <td></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>

        <p class="lead">Select Tests</p>
        <div class="well">
            @if($student->attempts->isEmpty() && $student->skillAttempts->isEmpty())
                No Test History
            @else
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th></th>
                        <th>Exam</th>
                        <th class="hidden-xs">{{ Lang::choice('core::terms.facility_testing', 1) }}</th>
                        <th class="hidden-xs">{{ Lang::choice('core::terms.observer', 1) }}</th>
                        <th class="hidden-xs">Form</th>
                        <th>Test Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Test History --}}
                    @foreach($testHistory as $att)
                        <tr data-clickable-row>
                            <td>
                                @if($att->getMorphClass() == 'Testattempt')
                                    {!! Form::checkbox('move_knowledge_ids[]', $att->id) !!}
                                @else
                                    {!! Form::checkbox('move_skill_ids[]', $att->id) !!}
                                @endif
                            </td>
                            <td>
                                {{ $att->exam ? $att->exam->name : $att->skillexam->name }}<br>
                                <small>{{ $att->getMorphClass() == 'Testattempt' ? 'Knowledge' : 'Skill' }}</small>
                            </td>
                            
                            <td class="hidden-xs">{{ $att->facility->name }}</td>
                            <td class="hidden-xs">{{ $att->testevent->observer->fullname }}</td>
                            <td class="hidden-xs">#{{ $att->testform_id ? $att->testform_id : $att->skilltest_id }}</td>

                            <td class="monospace">{{ $att->endDate }}</td>
                            <td>
                                @if($att->status == 'passed')
                                    <span class="label label-success">
                                @elseif($att->status == 'failed')
                                    <span class="label label-danger">
                                @else
                                    <span class="label label-warning">
                                @endif
                                    {{ ucfirst($att->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    <div class="col-md-3 sidebar">
        <div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core::affixOffset') }}" data-clampedwidth=".sidebar">
            <button type="success" class="btn btn-warning" id="reassign" data-confirm="Reassign History?<br><br>Are you sure?">
                {!! Icon::transfer() !!} Reassign
            </button>
        </div>
    </div>
{!! Form::close() !!}
@stop