<li class="treeview {{!! HTML::activeClass(['accounting']) !!}}">
	<a href="#">
        <i class="glyphicon glyphicon-usd"></i>
        <span>Accounting</span>
        <i class="fa fa-angle-left pull-right"></i>
    </a>
	<ul class="treeview-menu">
		{!! HTML::nav('accounting/billrates', 'Billing Rates') !!}
		{!! HTML::nav('accounting/invoice', 'Billing Report') !!}
		{!! HTML::nav('accounting/billing/manage', 'Manage Billing') !!}
		{!! HTML::nav('accounting/payrates', 'Payable Rates') !!}
		{!! HTML::nav('accounting/billing', 'Payables') !!}
	</ul>
</li>