@extends('core::layouts.default')

@section('content')
{!! Form::open(['route' => 'students.change_owner', 'method' => 'post']) !!}

    <div class="col-md-9">
        <div class="row">
            <div class="col-xs-8">
                <h1>Change Owner of Record(s)</h1>
            </div>
        </div>


        <div class="well">
            <div class="form-group">
                {!! Form::label('new_owner', 'Select New Owner') !!}
                {!! Form::select('new_owner', $instructors) !!}
            </div>
        </div>

        <p class="lead">Selected Students</p>
        <div class="well">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>{!! Icon::ok() !!}</th>
                        <th>Last</th>
                        <th>First</th>
                        <th>Updated At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $s)
                        <tr>
                            <td>{!! Form::checkbox('student_ids[]', $s->id, in_array($s->id, $studentIds)) !!}</td>
                            <td>{{ $s->last }}</td>
                            <td>{{ $s->first }}</td>
                            <td>{{ $s->updated_at }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-md-3 sidebar">
        <div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
            <button type="success" class="btn btn-success" data-confirm="Are you sure? Changing ownership of record(s) is not reversible!">
                {!! Icon::tower() !!} Change Owner
            </button>
        </div>
    </div>

    @if(isset($single))
        {!! Form::hidden('single', true) !!}
    @endif
{!! Form::close() !!}
@stop