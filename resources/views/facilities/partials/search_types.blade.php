<ul class="dropdown-menu" role="menu">
	<li class="active"><a href="#">Name</a></li>
	<li><a href="#">Email</a></li>
	<li><a href="#">City</a></li>
	<li class="divider"></li>
	<li><a href="#">State License</a></li>
	@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
		<li><a href="#">Testmaster License</a></li>
	@endif
</ul>