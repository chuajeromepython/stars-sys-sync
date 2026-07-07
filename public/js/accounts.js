$(function() {
	$('.btn-submit').addClass('disabled');

	function updatePasswordSubmitState() {
		var password = $('#password').val();
		var passwordConfirm = $('#password_confirmation').val();
		if (password && passwordConfirm && password === passwordConfirm) {
			$('.btn-submit').removeClass('disabled');
			return;
		}

		$('.btn-submit').addClass('disabled');
	}

	$('#password, #password_confirmation').on('input', function() {
		updatePasswordSubmitState();
	});

	$('#password_confirmation').focusout(function() {
		if ($('#password').val() !== $('#password_confirmation').val()) {
			toastr.error("Password didn't match!");
		}
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
	       		$('#password_confirmation').attr('type', 'text');
       		$(this).data('val', 1);
       		$(this).html('<i class="fa fa-eye-slash"></i>')
       	}else{
	       		$('#password_confirmation').attr('type', 'password');
       		$(this).data('val', 0);
       		$(this).removeClass('btn-primary');
       		$(this).html('<i class="fa fa-eye"></i>')
       	}
    });

	$('#refresh-qr').click(function() {
		var button = $(this);
		button.prop('disabled', true);

		$.get(button.data('url'))
			.done(function(response) {
				if (response.qr_svg) {
					$('#qr-code-wrapper').html(response.qr_svg);
					toastr.success('QR code refreshed.');
				}
			})
			.fail(function() {
				toastr.error('Unable to refresh QR code.');
			})
			.always(function() {
				button.prop('disabled', false);
			});
	});

});