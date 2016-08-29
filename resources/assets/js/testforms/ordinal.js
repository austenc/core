
/**
 * Boilerplate Drag/drop for a table with class .dragdrop
 *
 * If the first cell in the row of the body has span.ordinal,
 * it will also update the order numbers automatically
 */

	// enable sortable table
	$(function() {
  		$('.dragdrop').sortable({
			containerSelector: 'table',
			itemPath: '> tbody',
			itemSelector: 'tr',
			placeholder: '<tr class="placeholder"/>',
			onDrop: function ($item, container, _super, event) {
				$item.removeClass("dragged").removeAttr("style")
				$("body").removeClass("dragging")

				updateOrderNumbers();
			}
		});
	});

	// update the 'order' display
	function updateOrderNumbers()
	{
		$('.dragdrop tbody tr').each(function(index){
			$('td:first span.ordinal', this).text(index+1);
		});		
	}