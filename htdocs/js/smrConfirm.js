/**
 *
 * Confirmation
 * Author: Astax
 * 
 */
 
$(".bond").confirm({
    text: "Are you sure you want to bond?",
    confirm: function() {
        $('#finance').submit();
		alert(yes);
    },
    cancel: function() {
        // do something
    },
    confirmButton: "Yes I am",
    cancelButton: "No"
});