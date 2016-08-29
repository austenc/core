@extends('core::layouts.default')

@section('content')
    {!! Form::open(['route' => ['instructors.remap', $instructor->id]]) !!}
    <div class="col-md-9">
        <div class="row">
            <div class="col-xs-8">
                <h1>
                    Remap &amp; Delete <small>{{ $instructor->fullname }}</small>
                </h1>
            </div>
            <div class="col-xs-4 back-link">
                <a href="{{ route('instructors.edit', $instructor->id) }}" class="btn btn-link pull-right">{!! Icon::arrow_left() !!} Back to {{ Lang::choice('core::terms.instructor', 1) }}</a>
            </div>
        </div>

        <div class="alert alert-warning">
            <strong>{!! Icon::exclamation_sign() !!} Remap</strong> {{ Lang::choice('core::terms.student', 1) }} Trainings, Record Ownership, and {{ Lang::choice('core::terms.facility_training', 2) }} will all be transferred.
        </div>

        <div class="alert alert-danger">
            <strong>{!! Icon::exclamation_sign() !!} Delete</strong> Records will be permanently deleted after successful remap. This cannot be undone!
        </div>

        <h3>Delete {{ Lang::choice('core::terms.instructor', 1) }}</h3>
        <div class="well">
            <div class="form-group">
                {!! Form::label('name', 'Name') !!}
                {!! Form::text('name', $instructor->fullname, ['disabled']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('license', 'License') !!}
                {!! Form::text('license', $instructor->license, ['disabled']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('birthdate', 'Birthdate') !!}
                {!! Form::text('birthdate', $instructor->birthdate, ['disabled']) !!}
            </div>
        </div>


        <h3>Remap To {{ Lang::choice('core::terms.instructor', 1) }}</h3>
        <div class="well">
            @if($matches->isEmpty())
                No Matches
            @else
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>#</th>
                            <th>Name</th>
                            <th>License</th>
                            <th class="hidden-xs">Address</th>
                            <th hidden-xs>Birthdate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($matches as $m)
                        <tr>
                            <td>{!! Form::radio('remap_to', $m->id) !!}</td>
                            <td><span class="lead text-muted">{{ $m->id }}</span></td>
                            <td><a href="{{ route('instructors.edit', $m->id) }}" target="_blank">{{ $m->fullname }}</a></td>
                            <td class="monospace">{{ $m->license }}</td>

                            <td class="hidden-xs">
                                {{ $m->address }}<br>
                                <small>{{ $m->city }}, {{ $m->state }} {{ $m->zip }}</small>
                            </td>

                            <td class="monospace hidden-xs">{{ $m->birthdate }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="col-md-3 sidebar">
        <div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
            <button type="submit" name="remap" class="btn btn-danger" data-confirm="Remap and Delete ({{ Lang::choice('core::terms.instructor', 1) }}) {{ $instructor->fullname }} #{{ $instructor->id }}?<br><br>This will remap <strong>all</strong> related: {{ Lang::choice('core::terms.student', 1) }} Trainings, Record Ownership, and {{ Lang::choice('core::terms.facility_training', 2) }}. Afterwards this record will be <strong>permanently deleted</strong>!<br><br>Are you sure?">
                {!! Icon::scissors().' Remap & Delete' !!}
            </button>
        </div>
    </div>

    {!! Form::close() !!}
@stop