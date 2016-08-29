@extends('core::layouts.default')

@section('content')
    {!! Form::open(['route' => 'agencies.store']) !!}
    <div class="col-md-9">
        <div class="row">
            <div class="col-xs-8">
                <h1>Create Agency User</h1>
            </div>
            {!! HTML::backlink('agencies.index') !!}
        </div>

        {{-- Identification --}}
        @include('core::agency.partials.identification')

        {{-- Contact --}}
        @include('core::agency.partials.contact')

        {{-- Address --}}
        @include('core::partials.address')

        {{-- Login Password --}}
        @include('core::partials.new_password')
    </div>

    {{-- Sidebar --}}
    <div class="col-md-3 sidebar">
        @include('core::agency.sidebars.create')
    </div>
    {!! Form::close() !!}
@stop