$(function() {

	var screen_width = $(window).width();
	var screen_height = $(window).height();

	$('#dt_csv').dataTable({
	    'language':{
	        'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
	    },
	    "pageLength": 5,
	    'scrollX': (screen_height > screen_width) ? true : false
	});

});