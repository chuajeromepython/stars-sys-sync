$(function() {
    
    bsCustomFileInput.init();
	$('.select2bs4').select2({
      	theme: 'bootstrap4'
    })
	$(".form").submit(function (e) {
        $(".btn-submit").attr("disabled", true);
        $(".btn-submit").html("<i class='fa fa-save mr-2'></i> Saving...");
    });
    $(".form-destroy").submit(function (e) {
        $(".btn-submit-destroy").attr("disabled", true);
        $(".btn-submit-destroy").html("<i class='fa fa-save mr-2'></i> Deleting...");
    });
    $('.btn-cancel').click(function() {
            location.reload();
    });  

});