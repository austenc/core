@extends('core::layouts.default')

@section('content')
    <div class="panel panel-{{{ $n->type }}}">
        <div class="panel-heading">
            <span class="lead">{{ $n->subject }}</span> 
            <small class=" pull-right">
                <i class="fa fa-clock-o"></i> {{ $n->timeForHumans }}
            </small>
        </div>
        <div class="panel-body">
            <p>{{ $n->body }}</p>                    
        </div>
    </div>
    <a href="{{ route('notifications') }}" class="btn btn-sm btn-primary">{!! Icon::arrow_left() !!} Back to All Messages</a>
    <a href="{{ route('notifications.delete', $n->id) }}" class="btn btn-sm btn-danger" data-confirm="Are you sure you want to delete this notification?">{!! Icon::trash() !!} Send to Trash</a>
    <a href="{{ route('notification.unread', $n->id) }}" class="btn btn-default btn-sm">{!! Icon::flag() !!} Mark as Unread</a>    
@stop