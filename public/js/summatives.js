$(function() {

	var screen_width = $(window).width();
	var screen_height = $(window).height();

	$('#dt_csv').dataTable({
	    "pageLength": 5,
	    'scrollX': (screen_height > screen_width) ? true : false
	});

});