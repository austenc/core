@extends('core::layouts.default')

@section('content')
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Task</th>
                <th>Setup?</th>
                <th>Steps?</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            @foreach($toReview as $k => $r)
                <tr>
                    <td>{{{ $k }}}</td>
                    <td>{{{ $taskNames[$k]->title }}}</td>
                    <td>
                        @if(array_key_exists('setup', $r))
                            <span class="label label-warning">Needs Review</span>
                        @else
                            <span class="label label-success">Okay</span>
                        @endif
                    </td>
                    <td>
                        @if(array_key_exists('steps', $r))
                            <span class="label label-warning">Needs Review</span>
                        @else
                            <span class="label label-success">Okay</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('tasks.edit', $k) }}" class="btn btn-primary">Edit Task</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

@stop