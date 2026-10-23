$(function() {

    var screen_width = $(window).width();
    var screen_height = $(window).height();

    var schoolOptions = $('#student-school option').clone();
    var teacherOptions = $('#student-teacher option').clone();
    var table = $('#dt_students').DataTable({
        serverSide: true,
        processing: true,
        deferRender: true,
        ajax: {
            url: '/students/data',
            data: function (data) {
                data.academic_year_id = $('#student-academic-year').val();
                data.district_id = $('#student-district').val();
                data.school_id = $('#student-school').val();
                data.teacher_id = $('#student-teacher').val();
            }
        },
        columns: [
            { data: 'lrn' },
            { data: 'name' },
            { data: 'gender' },
            { data: 'action', orderable: false, searchable: false }
        ],
        'scrollX': (screen_height > screen_width) ? true : false
    });

    $('#student-district').on('change', function () {
        var districtId = $(this).val();
        $('#student-school').empty().append(schoolOptions.filter(function () {
            return !this.value || !districtId || $(this).data('district') == districtId;
        }).clone()).trigger('change');
    });

    $('#student-school').on('change', function () {
        var schoolId = $(this).val();
        $('#student-teacher').empty().append(teacherOptions.filter(function () {
            return !this.value || !schoolId || $(this).data('school') == schoolId;
        }).clone());
        table.ajax.reload();
    });

    $('#student-academic-year, #student-teacher').on('change', function () {
        table.ajax.reload();
    });
});
