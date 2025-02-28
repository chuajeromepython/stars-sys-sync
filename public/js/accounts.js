 $(function() {
 	$('.btn-submit').addClass("disabled");
    $(document).ready(function(){
        $('#password_confirm').focusout(function(){
            var password = $('#password').val();
            var password_confirm = $('#password_confirm').val();
            if(password != password_confirm){
                toastr.error("Password didn't match!")
            }else{
            	$('.btn-submit').removeClass("disabled");
            }
        });
    });


    $('.show-password').click(function() {
       	val = $(this).data('val');
       	// alert(val);
       	if(val == 0){
       		$('#password').attr('type', 'text');
       		$(this).data('val', 1);
       		$(this).html('<i class="fa fa-eye-slash"></i>')
       	}else{
       		$('#password').attr('type', 'password');
       		$(this).data('val', 0);
       		$(this).removeClass('btn-primary');
       		$(this).html('<i class="fa fa-eye"></i>')
       	}
    })

    $('.show-password-confirm').click(function() {
       	val = $(this).data('val');
       	if(val == 0){
       		$('#password_confirm').attr('type', 'text');
       		$(this).data('val', 1);
       		$(this).html('<i class="fa fa-eye-slash"></i>')
       	}else{
       		$('#password_confirm').attr('type', 'password');
       		$(this).data('val', 0);
       		$(this).removeClass('btn-primary');
       		$(this).html('<i class="fa fa-eye"></i>')
       	}
    })

});