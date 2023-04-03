document.addEventListener('turbo:load', loadHospitalTypeData)

function loadHospitalTypeData () {
}

listenSubmit('#addHospitalTypeForm', function (e) {
    e.preventDefault()
    let loadingButton = jQuery(this).find('#hospitalTypeSaveBtn')
    loadingButton.button('loading')
    $.ajax({
        url: $('.hospitalTypeCreateUrl').val(),
        type: 'POST',
        data: $(this).serialize(),
        success: function (result) {
            if (result.success) {
                displaySuccessMessage(result.message);
                $('#createHospitalTypeModal').modal('hide');
                window.livewire.emit('refresh')
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
        complete: function () {
            loadingButton.button('reset');
        },
    });
})

listenClick('.editHospitalTypeBtn', function (event) {
    let hospitalTypeId = $(event.currentTarget).attr('data-id');
    $.ajax({
        url: route('super.admin.hospitals.type.edit', hospitalTypeId),
        type: 'GET',
        success: function (result) {
            if (result.success) {
                $('#editHospitalTypeId').val(result.data.id);
                $('#editHospitalTypeName').val(result.data.name);
                $('#editHospitalTypeModal').modal('show');
            }
        },
        error: function (result) {
            manageAjaxErrors(result);
        },
    });
});

listenSubmit('#editHospitalTypeForm', function (event){
    event.preventDefault()
    let loadingButton = jQuery(this).find('#editHospitalTypeSaveBtn')
    loadingButton.button('loading')
    let id = $('#editHospitalTypeId').val()
    $.ajax({
        url: route('super.admin.hospitals.type.update', id),
        type: 'POST',
        data: $(this).serialize(),
        success: function (result) {
            if (result.success) {
                displaySuccessMessage(result.message);
                $('#editHospitalTypeModal').modal('hide');
                window.livewire.emit('refresh')
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
        complete: function () {
            loadingButton.button('reset');
        },
    });
});

listenClick('.deleteHospitalTypeBtn', function (event) {
    let id = $(event.currentTarget).attr('data-id')
    // deleteItem($('#hospitalTypeEditUrl').val() + '/' + id, '',
    //     'Hospital Type')
    deleteItem(route('super.admin.hospitals.type.delete', id), '',
        'Hospital Type')
})

listenHiddenBsModal('#createHospitalTypeModal', function () {
    resetModalForm('#addHospitalTypeForm')
})

listenHiddenBsModal('#editHospitalTypeModal', function () {
    resetModalForm('#editHospitalTypeForm')
})
