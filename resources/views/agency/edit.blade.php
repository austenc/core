@extends('core::layouts.default')

@section('content')
    {!! Form::model($agency, ['route' => ['agencies.update', $agency->id], 'method' => 'PUT']) !!}
    <div class="col-md-9">
        <div class="row">
            <div class="col-xs-8">
                <h1>Edit Agency <small>{{ $agency->full_name }}</small></h1>
            </div>
            {!! HTML::backlink('agencies.index') !!}
        </div>

        {{-- Identification --}}
        @include('core::agency.partials.identification')

        {{-- Contact --}}
        @include('core::agency.partials.contact')

        {{-- Address --}}
        @include('core::partials.address')

        {{-- Login Info --}}
        @include('core::partials.login_info', ['record' => $agency, 'name' => 'agency'])
    </div>

    {{-- Sidebar --}}
    <div class="col-md-3 sidebar">
        @include('core::agency.sidebars.edit')
    </div>
    {!! Form::close() !!}
@stop