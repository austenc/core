@if($form->status == 'active')
    <div class="alert alert-success clearfix">
        {!! Icon::play() !!} <strong>{{ ucfirst($form->status) }}</strong> -- Currently used in Testing <a href="{{ route('testforms.archive', $form->id) }}" class="btn btn-sm btn-warning pull-right" data-confirm="This testform will no longer be used, this is final. Are you sure?"><i class="glyphicon glyphicon-warning-sign"></i> Archive Testform</a>
    </div>
@elseif($form->status == 'archived')
	<div class="alert alert-danger">
		{!! Icon::warning_sign() !!} <strong>{{ ucfirst($form->status) }}</strong> -- No longer used in testing process.
	</div>
@else
    <div class="alert alert-warning">
        {!! Icon::flag() !!} <strong>{{ ucfirst($form->status) }}</strong> -- Not used in Testing process. Activate to begin use.
    </div>
@endif