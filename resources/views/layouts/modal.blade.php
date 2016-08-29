<div class="modal fade {{{ $class or '' }}}" id="{{{ $id or 'myModal' }}}">
  <div class="modal-dialog">
    <div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			@yield('header', '<br>')
		</div>
		<div class="modal-body">
			@yield('body')
		</div>
		<div class="modal-footer">
			@yield('footer')
		</div>	    	
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->