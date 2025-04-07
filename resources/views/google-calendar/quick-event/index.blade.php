@extends('layouts.app')

@section('page_meta_title', 'Google Calendar Quick Event Booking Service')

@push('page_css_linkss')
<!-- jQuery UI (Datepicker) -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<!-- jQuery UI (Timepicker) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datepicker/0.6.5/datepicker.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
@endpush
@push('page_css_styles')
<style>
  .time-dropdown {
  max-height: 300px;
  overflow-y: auto;
  border: 1px solid #ccc;
  background: white;
}

.time-dropdown .disabled {
  pointer-events: none;
  opacity: 0.5;
}
</style>
@endpush

@section('page_content')
    <div class="container">
        <div class="row mt-5">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h2>Schedule Quick appointment</h2>
                    </div>
                    <div class="card-body">
                        <div id="responseArea"></div>
                        <form name="book_event_frm" id="bookEventFrm" action="{{ route('gcal.create-quick-event') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="name" id="bookName" placeholder="Your Name" required>
                                        <label for="bookName">Name:</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-floating mb-3">
                                        <input type="email" class="form-control" name="email" id="bookEmail" placeholder="Your Email" required>
                                        <label for="bookEmail">Email:</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-floating mb-3">
                                        <textarea class="form-control" id="bookDescription" name="description" placeholder="Description..." required></textarea>
                                        <label for="bookDescription">Description:</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="date" id="meetingDate" placeholder="Choose a date">
                                        <label for="meetingDate">Choose a date</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="time" id="meetingTime" placeholder="Choose a time" disabled>
                                        <label for="meetingTime">Choose a time</label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-primary" id="createBookEventBtn">Schedule Meeting</button>
                        <button type="button" class="btn btn-danger" id="resetBookEventBtn">Reset</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page_script_links')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- jQuery UI (Datepicker) -->
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<!-- jQuery Timepicker (by Jon Thornton) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/additional-methods.min.js"></script>

@endpush

@push('page_scripts')
<script>
    $(document).ready(function () {

        const bookingInterval = 30;
        const $meetingTime = $("#meetingTime");
        const $meetingDate = $("#meetingDate");
        const _uiDateFormat = "mm-dd-yy"; // "yy-mm-dd" or "mm-dd-yy"
        let _usingDateFormat = null;

        $("#meetingDate").datepicker({
            dateFormat: _uiDateFormat,
            minDate: 0,
            showAnim: "fadeIn",
            onSelect: function(dateText, inst) {
                $(this).valid(); // Always re-validate date
                // Reset time field on date change
                $meetingTime.val('').attr('disabled', true).valid(); // Clear, disable, and validate
                if ($(this).valid()) {
                    _usingDateFormat = inst.settings?.dateFormat || $("#meetingDate").datepicker("option", "dateFormat");
                    $meetingTime.removeAttr('disabled');
                }
            }
        });
        
        $("#meetingTime").timepicker({
            timeFormat: "h:mm p",
            interval: 30,
            minTime: "06",
            maxTime: "23:55pm",
            defaultTime: "06",
            startTime: "01:00",
            dynamic: true,
            dropdown: true,
            scrollbar: true
        });
        
        $("#meetingTime").val("");
        
        $('#meetingTime').on('change', function () {
    alert('xxxxxx');
});

        let formValidate = $("#bookEventFrm").validate({
            errorClass: 'onex-error',
            errorElement: 'div',
            rules: {
                name: {
                    required: true,
                    minlength: 3
                },
                email: {
                    required: true,
                    email: true,
                    maxlength: 60
                },
                description: {
                    required: true,
                    minlength: 6,
                    maxlength: 120
                },
                date: {
                    required: true,
                    pattern: /^(?:\d{2}-\d{2}-\d{4}|\d{4}-\d{2}-\d{2})$/ // mm-dd-yyyy OR yyyy-mm-dd
                },
                time: {
                    required: {
                        depends: function (element) {
                            return $('input[name="date"]').val().trim() !== '';
                        }
                    }
                },
            },
            messages: {
                name: {
                    required: 'Please enter your name',
                    minlength: 'Minimum 3 chars required'
                },
                email: {
                    required: 'Please enter your email',
                    email: 'Please enter valid email',
                    maxlength: 'Maximum 60 chars allowed'
                },
                description: {
                    required: 'Please write meeting agenda',
                    minlength: 'Minimum 6 chars required',
                    maxlength: 'Maximum 120 chars allowed'
                },
                date: {
                    required: 'Please select a date'
                },
                time: {
                    required: 'Please select a time slot'
                },
            },
            errorPlacement: function (error, element) {
                error.insertAfter(element);
            }
        });

        const createBookEventBtn = document.getElementById('createBookEventBtn');
        const resetBookEventBtn = document.getElementById('resetBookEventBtn');

        createBookEventBtn.addEventListener('click', function () {
            if (formValidate.form()) {
                let $form = $('#bookEventFrm');
                let form = $form[0];
                let formData = new FormData(form);
                formData.append('using_date_format', _usingDateFormat);

                $.ajax({
                    url: $form.attr('action'),
                    type: $form.attr('method'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    cache: false,
                    beforeSend: function () {
                        $('#createBookEventBtn').attr('disabled', true);
                        $('#createBookEventBtn').html(`<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> <span role="status">Scheduling...</span>`);
                    },
                    success: function (response) {
                        if (response && response.isSuccess) {
                            $('#createBookEventBtn').removeAttr('disabled');
                            $('#createBookEventBtn').html('Schedule Meeting');

                            const start = new Date(response?.data?.start?.dateTime);
                            const end = new Date(response?.data?.end?.dateTime);

                            const diffMs = end - start; // Difference in milliseconds
                            const diffMins = Math.round(diffMs / 1000 / 60); // Convert to minutes

                            const successMessage = response?.message || 'Your meeting has been scheduled successfully';

                            $('#responseArea').html(`
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    <h4 class="alert-heading">Thanks! ${$('input[name="name"]').val()}</h4>
                                    <p> ${successMessage}</p>
                                    <hr>
                                    <p class="mb-0">
                                        <strong>Meeting Link:</strong> ${response?.data?.location}
                                        <br/>
                                        <strong>Start:</strong> ${response?.data?.start?.dateTime}
                                        <br/>
                                        <strong>End:</strong> ${response?.data?.end?.dateTime} 
                                        <br/>
                                        <strong>Duration:</strong> ${diffMins} minutes
                                        <br/>
                                        <strong>Timezone:</strong> ${response?.data?.start?.timeZone}
                                    </p>
                                </div>
                            `);

                            $('input[name="name"]').val('');
                            $('input[name="email"]').val('');
                            $('textarea[name="description"]').val('');
                            $('input[name="date"]').val('');
                            $('input[name="time"]').val('').attr('disabled', true);

                            form.reset();

                        }
                    },
                    error: function (xhr, status, error) {
                        $('#createBookEventBtn').removeAttr('disabled');
                        $('#createBookEventBtn').html('Schedule Meeting');
                        if (!xhr?.responseJSON?.isSuccess) {
                            $('#responseArea').html(`
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    <h4 class="alert-heading">Oops!! Error occur</h4>
                                    <hr/>
                                    <p>${xhr?.responseJSON?.message}<br/>${xhr?.responseJSON?.error}</p>
                                </div>`
                            );
                        } else {
                            $('#responseArea').html(`
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    Error: Something went wrong!! Try again
                                </div>
                            `);
                        }
                        console.error('Error:', error, xhr, status);
                    }
                });
            }
        });

        resetBookEventBtn.addEventListener('click', function () {
            $('input[name="name"]').val('');
            $('input[name="email"]').val('');
            $('textarea[name="description"]').val('');
            $('input[name="date"]').val('');
            $('input[name="time"]').val('').attr('disabled', true);
            $('#responseArea').html('');
        });
    });
</script>
@endpush