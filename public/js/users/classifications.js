$(function() {
	$('#btn_edit_classification').click(function(){
		var user_id = $(this).data('user_id');
		var classification = $(this).data('classification');
		$('#edit_user_id').val(user_id);
		$('#current_classification').val(classification);
	});
});