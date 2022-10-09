"use strict";

// Install drag & drop handlers once the page has finished loading
window.onload = function() {
	setupDragDrop();
};

/**
 * Allows locations to be dragged and dropped to different sectors
 * with an AJAX update.
 *
 * This needs to be called every time the elements in the sector map
 * update, to install new handlers on the updated elements.
 */
function setupDragDrop() {

	// Make the Location images draggable elements
	$(".drag_loc").draggable({
		addClasses: false,
		revert: "invalid",
		cursor: "move",
	});

	// The draggable elements can be dropped into any sector
	$(".lm_sector").droppable({
		addClasses: false,
		accept: ".drag_loc",
		drop: function(event, ui) {
			var href = ui.draggable.data("href");
			var data = {
				TargetSectorID: $(this).find(".lmsector").text(),
				OrigSectorID: ui.draggable.data("sector"),
				LocationTypeID: ui.draggable.data("loc"),
			};
			ajaxLink(href, setupDragDrop, data);
		},
	});

}
