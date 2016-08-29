var lastHoldField = "";
var lastLockField = "";

function openHoldDetails(field){
	$('#holdTable i.fa-chevron-up').hide();
	$('#holdTable i.fa-chevron-down').show();

	$('#holdTable tr.holdDetails').hide();
	$(field).closest('tr').next('tr').show();
	$(field).hide();
	$(field).next('i').show();

	lastHoldField = field;
}
function openLockDetails(field){
	$('#lockTable i.fa-chevron-up').hide();
	$('#lockTable i.fa-chevron-down').show();

	$('#lockTable tr.lockDetails').hide();
	$(field).closest('tr').next('tr').show();
	$(field).hide();
	$(field).next('i').show();
	
	lastLockField = field;
}
function hideThis(field, lastField){
	$(field).hide();
	if(lastField == "hold"){
		$(lastHoldField).closest('tr').next('tr').hide();
		$(lastHoldField).show();
	} else {
		$(lastLockField).closest('tr').next('tr').hide();
		$(lastLockField).show();
	}
}
