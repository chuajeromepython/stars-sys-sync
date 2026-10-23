$(function() {

     var screen_width = $(window).width();
    var screen_height = $(window).height();

    var schoolOptions = $('#teacher-school option').clone();
    var table = $('#dt_teachers').DataTable({
        serverSide: true,
        processing: true,
        deferRender: true,
        ajax: {
            url: '/teachers/data',
            data: function (data) {
                data.district_id = $('#teacher-district').val();
                data.school_id = $('#teacher-school').val();
            }
        },
        columns: [
            { data: 'username' },
            { data: 'name' },
            { data: 'action', orderable: false, searchable: false }
        ],
        'scrollX': (screen_height > screen_width) ? true : false
    });

    $('#teacher-district').on('change', function () {
        var districtId = $(this).val();
        $('#teacher-school').empty().append(schoolOptions.filter(function () {
            return !this.value || !districtId || $(this).data('district') == districtId;
        }).clone()).trigger('change');
    });

    $('#teacher-school').on('change', function () {
        table.ajax.reload();
    });

	$('#dt_teachers tbody').on( 'click', '.btn-destroy', function () {
        
        var username = $(this).data('destroy_username');
        var id = $(this).data('destroy_id');

        $('#destroy_username').html(username+"'s");
        $('#destroy_id').val(id);
        
    });

    

});
