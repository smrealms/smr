function processCourse(sectorID) {
	var plotCourseForm = document.getElementById('plotCourseForm');
	plotCourseForm.to.value = sectorID;
	plotCourseForm.submit();
}

function processRemove(sectorID) {
	var manageDestination = document.getElementById('manageDestination');
	manageDestination.sectorId.value = sectorID;
	manageDestination.type.value = 'delete';
	manageDestination.submit();
}

$(function() {
	$('.draggableObject').draggable({ containment: 'parent' });
	$('#droppableObject').droppable({
		drop: function(event, ui) {
			var manageDestination = document.getElementById('manageDestination'),
				sectorID = ui.draggable.data('sector-id'),
				pos = ui.draggable.position();
			manageDestination.sectorId.value = sectorID;
			manageDestination.offsetTop.value = pos.top;
			manageDestination.offsetLeft.value = pos.left;
			manageDestination.type.value = 'move';
			manageDestination.submit();
		}
	});
});
