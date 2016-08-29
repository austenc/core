@extends('core::layouts.default')

@section('content')
    <h3>Merge Users</h3>

    <div class="alert alert-info">
        <strong>{!! Icon::info_sign() !!} Matches</strong> found using first ({{ $numCharsMatched }}) characters of First and Last name.
    </div>

    {!! Form::open(['route' => 'utilities.users.merge.do', 'id' => 'merge-form']) !!}
        {{-- For Each Type --}}
        @foreach($allTypes as $type)
            <div class="well">
                @include('core::utilities.partials.merge_users_table', [
                    'items'  => $matched[$type],
                    'lookup' => $lookup[$type],
                    'type'   => $type
                ])
            </div>
        @endforeach
    {!! Form::close() !!}
@stop