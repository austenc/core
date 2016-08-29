@extends('core::layouts.default')

@section('content')
<h3 class="text-center">{{ Config::get('core.client.name') }} Training Registration Roster</h3>
<h4 class="text-center text-muted">
   {{ Lang::choice('core::terms.facility_training', 1) }}: {{ $discipline['program']['name'] }} ({{ $discipline['program']['license'] }})
</h4>
<div class="center-block text-center">
    <a href="javascript:window.print();" class="btn btn-primary hidden-print">
        <span class="glyphicon glyphicon-print"></span> Print This Page
    </a>
</div>
<hr>
<table class="table table-striped table-condensed">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Address</th>
            <th>City</th>
            <th>Training Start</th>
            <th>Training Completion</th>
        </tr>
    </thead>
    <tbody>
        @foreach($students as $s)
            <tr>
                <td>{{{ $s->id }}}</td>
                <td>
                    {{{ $s->commaName }}} <br>
                    <small>{{{ $s->user->email }}}</small>
                </td>
                <td>
                    {{{ $s->address }}}
                </td>
                <td>
                    {{{ $s->city }}}, {{{ $s->state }}}
                </td>
                <td>
                    @if($t = $s->trainings->first())
                        @if(! empty($t) && ! empty($t->pivot))
                            @if($t->pivot->started)
                                {{ date('m/d/Y', strtotime($t->pivot->started)) }}
                            @endif
                        @endif
                    @endif
                </td>
                <td>
                    @if($t = $s->trainings->first())
                        @if(! empty($t) && ! empty($t->pivot))
                            @if($t->pivot->ended)
                                {{ date('m/d/Y', strtotime($t->pivot->ended)) }}
                            @endif
                        @endif
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@stop