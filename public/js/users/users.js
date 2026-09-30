$(function() {
    

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Users => Index
    var classification =  $('#classification').val();
    getArea(classification);

    $('#classification').on('change', function() {
        var classification =  $(this).val();
        getArea(classification);
        
    });

    $('#upload_classification').on('change', function() {
        var classification =  $(this).val();
        if(classification == "School Head"){
            var link = '/school_supervisors/upload';
        }
        
        $('#upload_form').attr('action', link);
        
    });


    // Reset, destroy and assign-roles all act on the rows DataTables renders,
    // and the table is initialised by another script, so the handlers are
    // delegated from the document. Binding to `#dt_users tbody` at ready time
    // matched nothing (the markup has no body yet), which is what left the
    // modals on their placeholder text.
    $(document).on('click', '#dt_users tbody .btn-reset', function () {

        var username = $(this).data('reset_username');
        var id = $(this).data('reset_id');

        $('#reset_username').html(username);
        $('#reset_id').val(id);
    });

    $(document).on('click', '#dt_users tbody .btn-destroy', function () {

        var username = $(this).data('destroy_username');
        var id = $(this).data('destroy_id');

        $('#destroy_username').html(username+"'s");
        $('#destroy_id').val(id);
    });

    var rolesSelect = $('#roles_select');
    var rolesModal = $('#roles_modal');
    var rolesUsername = $('#roles_username');
    var rolesCurrentList = $('#roles_current_list');
    var rolesForm = $('#roles_form');
    var rolesOpenerBound = false;

    // Guarded on purpose: when the plugin is unavailable the select stays a
    // native multi-select, which the code below drives without select2, and the
    // role loading below keeps working.
    function hasSelect2() {
        return rolesSelect.length > 0 && typeof $.fn.select2 === 'function';
    }

    function initRolesSelect2() {
        if (!hasSelect2() || rolesSelect.hasClass('select2-hidden-accessible')) {
            return;
        }

        rolesSelect.select2({
            // The dropdown hangs off the body rather than the modal: a modal is
            // `overflow: hidden`, so a dropdown parented to it gets clipped by
            // the dialog, and select2's own z-index already sits above the
            // Bootstrap backdrop.
            dropdownParent: $(document.body),
            placeholder: 'Select roles',
            theme: 'bootstrap4',
            width: '100%'
        });
    }

    // The row buttons are rendered by UserManagementDataTable with
    // `data-roles_id` / `data-roles_username`.
    function readRolesTarget(trigger) {
        var button = $(trigger);

        return {
            id: button.attr('data-roles_id') || button.data('roles_id'),
            username: button.attr('data-roles_username') || button.data('roles_username')
        };
    }

    function triggerRolesChange() {
        if (hasSelect2() && rolesSelect.hasClass('select2-hidden-accessible')) {
            rolesSelect.val(null).trigger('change.select2');
        } else {
            rolesSelect.val(null).trigger('change');
        }
    }

    function showRolesPlaceholder() {
        rolesCurrentList.html('<span class="text-muted">Loading…</span>');
        rolesSelect.empty().append('<option disabled>Loading…</option>');
        triggerRolesChange();
    }

    function renderAssignedRoles(current) {
        var assigned = $.isArray(current) ? current : [];

        if (assigned.length) {
            var badges = '';
            $.each(assigned, function (i, role) {
                badges += '<span class="badge badge-primary mr-1 mb-1">' + role + '</span>';
            });
            rolesCurrentList.html(badges);
        } else {
            rolesCurrentList.html('<span class="badge badge-danger">No role assigned</span>');
        }

        return assigned;
    }

    function setRoleOptions(roles, current) {
        var assigned = $.isArray(current) ? current : [];

        rolesSelect.empty();

        $.each($.isArray(roles) ? roles : [], function (i, role) {
            var option = new Option(role, role, true, $.inArray(role, assigned) !== -1);

            rolesSelect.append(option);
        });

        if (hasSelect2() && rolesSelect.hasClass('select2-hidden-accessible')) {
            rolesSelect.val(assigned).trigger('change.select2');
        } else {
            rolesSelect.val(assigned).trigger('change');
        }
    }

    function loadRolesIntoModal(target) {
        showRolesPlaceholder();

        if (!target.id) {
            rolesCurrentList.html('<span class="badge badge-danger">Unable to load roles</span>');

            return;
        }

        rolesForm.attr('action', '/users/' + target.id + '/roles');

        $.ajax({
            url: '/users/' + target.id + '/roles',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                var assigned = renderAssignedRoles(data && data.assigned);

                setRoleOptions(data && data.roles, assigned);
            },
            error: function () {
                rolesCurrentList.html('<span class="badge badge-danger">Unable to load roles</span>');
            }
        });
    }

    // Both entry points below - the delegated click and Bootstrap's
    // `show.bs.modal` - fire for the same click, so the load is guarded by a
    // flag that is only cleared once the dialog has closed. That keeps the
    // roles request to exactly one per opening, whatever order the two
    // handlers run in.
    var rolesLoadStarted = false;

    function openRolesModal(target) {
        if (!target || !target.id || rolesLoadStarted) {
            return;
        }

        rolesLoadStarted = true;
        rolesUsername.text(target.username || '');
        loadRolesIntoModal(target);
    }

    function bindRolesOpener() {
        if (rolesOpenerBound) {
            return;
        }

        rolesOpenerBound = true;

        // The button carries `data-toggle="modal"`, so Bootstrap's data api
        // opens the dialog itself; this handler only has to find the account on
        // the row, and the delegation is what makes it survive the rows
        // DataTables renders after this script has run.
        $(document).on('click', '#dt_users tbody .btn-roles', function () {
            openRolesModal(readRolesTarget(this));
        });
    }

    initRolesSelect2();
    bindRolesOpener();

    if (rolesModal.length) {
        rolesModal.on('show.bs.modal', function (event) {
            var trigger = event && event.relatedTarget ? event.relatedTarget : null;

            openRolesModal(trigger ? readRolesTarget(trigger) : null);
        });

        rolesModal.on('hidden.bs.modal', function () {
            rolesLoadStarted = false;
            rolesUsername.text('');
            rolesCurrentList.html('<span class="text-muted">Loading…</span>');
            rolesSelect.empty();
            triggerRolesChange();
            rolesForm.attr('action', '#');
        });
    }

});




function getArea(classification){
    $.ajax({
        url: '/getArea',
        type: "POST",
        data: {
            "classification" : classification
        },
        success: function(data){
            var request_area = $("#request_area").val();
            var optons = '<option selected="" value="" > -Select Area- </option>';
            $.each(data, function(i, item) {
                if(request_area == data[i].name){
                    optons += '<option value="'+data[i].name+'" selected>'+data[i].name+'</option>';
                }else{
                    optons += '<option value="'+data[i].name+'">'+data[i].name+'</option>';
                }
                
            });
            $('#select_area').html(optons);    
            
        }, //end of success getMunicipalitiesByDistrict
    });
}

