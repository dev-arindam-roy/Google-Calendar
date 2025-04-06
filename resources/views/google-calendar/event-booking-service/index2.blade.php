@extends('layouts.app')

@section('page_meta_title', 'Google Calendar Event Booking Service')

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
                        <h2>Schedule an appointment for Bug / Code Fix</h2>
                    </div>
                    <div class="card-body">
                        <div id="responseArea"></div>
                        <form name="book_event_frm" id="bookEventFrm" action="{{ route('gcal.create-event-service') }}" method="POST">
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
        <div class="row mt-5">
            <div class="col-md-8 offset-md-2">
                <ul class="nav nav-tabs" id="scheduleTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming-tab-pane" type="button" role="tab" aria-controls="upcoming-tab-pane" aria-selected="true">Upcoming Meetings ({{ count($upcoming_booking) }})</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="past-tab" data-bs-toggle="tab" data-bs-target="#past-tab-pane" type="button" role="tab" aria-controls="past-tab-pane" aria-selected="false">Past Scheduled ({{ count($past_booking) }})</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-tab-pane" type="button" role="tab" aria-controls="all-tab-pane" aria-selected="false">ALL ({{ count($all_booking) }})</button>
                    </li>
                </ul>
                <div class="tab-content" id="scheduleTabContent">
                    <div class="tab-pane fade show active" id="upcoming-tab-pane" role="tabpanel" aria-labelledby="upcoming-tab" tabindex="0">
                        <table class="table table-sm table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Meeting</th>
                                    <th>Date Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($upcoming_booking))
                                    @foreach($upcoming_booking as $k => $v)
                                        @php
                                            $start = Carbon\Carbon::parse($v['start']);
                                            $formattedStartDateTime = $start->format('m-d-Y h:i:s a');

                                            $end = Carbon\Carbon::parse($v['end']);
                                            $formattedEndDateTime = $end->format('m-d-Y h:i:s a');

                                            $diffInMinutes = $end->diffInMinutes($start);
                                        @endphp
                                    <tr>
                                        <td>{{ $k + 1 }}</td>
                                        <td>
                                            {{ $v['summary'] }}
                                            <br/>
                                            <a href="{{ $v['meeting_link'] }}" target="_blank">Meeting LInk</a>
                                            <br/>
                                            <a href="{{ $v['calendar_html_link'] }}" target="_blank">Calendar LInk</a>
                                        </td>
                                        <td>
                                            Start: {{ $formattedStartDateTime }}
                                            <br/>
                                            End: {{ $formattedEndDateTime }}
                                            <br/>
                                            Duration: {{ $diffInMinutes }} minutes
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3">No Records Found!</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="past-tab-pane" role="tabpanel" aria-labelledby="past-tab" tabindex="0">
                        <table class="table table-sm table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Meeting</th>
                                    <th>Date Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($past_booking))
                                    @foreach($past_booking as $k => $v)
                                        @php
                                            $start = Carbon\Carbon::parse($v['start']);
                                            $formattedStartDateTime = $start->format('m-d-Y h:i:s a');

                                            $end = Carbon\Carbon::parse($v['end']);
                                            $formattedEndDateTime = $end->format('m-d-Y h:i:s a');

                                            $diffInMinutes = $end->diffInMinutes($start);
                                        @endphp
                                    <tr>
                                        <td>{{ $k + 1 }}</td>
                                        <td>{{ $v['summary'] }}</td>
                                        <td>
                                            Start: {{ $formattedStartDateTime }}
                                            <br/>
                                            End: {{ $formattedEndDateTime }}
                                            <br/>
                                            Duration: {{ $diffInMinutes }} minutes
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3">No Records Found!</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="all-tab-pane" role="tabpanel" aria-labelledby="all-tab" tabindex="0">
                        <table class="table table-sm table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Meeting</th>
                                    <th>Date Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($all_booking))
                                    @foreach($all_booking as $k => $v)
                                        @php
                                            $start = Carbon\Carbon::parse($v['start']);
                                            $formattedStartDateTime = $start->format('m-d-Y h:i:s a');

                                            $end = Carbon\Carbon::parse($v['end']);
                                            $formattedEndDateTime = $end->format('m-d-Y h:i:s a');

                                            $diffInMinutes = $end->diffInMinutes($start);
                                        @endphp
                                    <tr>
                                        <td>{{ $k + 1 }}</td>
                                        <td>{{ $v['summary'] }}</td>
                                        <td>
                                            Start: {{ $formattedStartDateTime }}
                                            <br/>
                                            End: {{ $formattedEndDateTime }}
                                            <br/>
                                            Duration: {{ $diffInMinutes }} minutes
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3">No Records Found!</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
    <p id="allBooking" class="d-none">{{ json_encode($all_booking) }}</p>
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
        
        // $("#meetingTime").timepicker({
        //     timeFormat: "h:mm p",
        //     interval: 30,
        //     minTime: "06",
        //     maxTime: "23:55pm",
        //     defaultTime: "06",
        //     startTime: "01:00",
        //     dynamic: true,
        //     dropdown: true,
        //     scrollbar: false
        // });
        //$("#meetingTime").val("");

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

        

        // Get booking data from HTML
        let bookingRaw = $("#allBooking").text();
        let allBookings = [];

        try {
            allBookings = JSON.parse(bookingRaw);
        } catch (e) {
            console.error("Invalid booking JSON:", e);
        }

        // Time formatting
        function formatAMPM(date) {
            let hours = date.getHours();
            let minutes = date.getMinutes();
            const ampm = hours >= 12 ? "PM" : "AM";
            hours = hours % 12 || 12;
            minutes = minutes < 10 ? "0" + minutes : minutes;
            return hours + ":" + minutes + " " + ampm;
        }

        // Check if slot is booked
        function isSlotBooked(slotStart, slotEnd, bookings) {
            return bookings.some(booked => {
                const bookedStart = new Date(booked.start);
                const bookedEnd = new Date(booked.end);
                return slotStart < bookedEnd && slotEnd > bookedStart;
            });
        }

        // Create dropdown
        const $dropdown = $("<div class='time-dropdown list-group'></div>").css({
            position: "absolute",
            zIndex: 1000,
            display: "none",
            width: $meetingTime.outerWidth()
        });
        $("body").append($dropdown);

        // Show and populate dropdown
        function showTimeDropdown(selectedDate) {
            const dropdownOffset = $meetingTime.offset();
            $dropdown.css({
                top: dropdownOffset.top + $meetingTime.outerHeight(),
                left: dropdownOffset.left,
                display: "block"
            });

            $dropdown.empty();

            const bookingsForDate = allBookings.filter(event => {
                return event.start.startsWith(selectedDate);
            });

            const start = new Date(`${selectedDate}T06:00:00`);
            const end = new Date(`${selectedDate}T23:55:00`);

            while (start < end) {
                const slotStart = new Date(start);
                const slotEnd = new Date(start);
                slotEnd.setMinutes(slotEnd.getMinutes() + bookingInterval);

                const isBooked = isSlotBooked(slotStart, slotEnd, bookingsForDate);
                const label = `${formatAMPM(slotStart)} - ${formatAMPM(slotEnd)}`;
                const disabledClass = isBooked ? "disabled text-muted" : "";

                $dropdown.append(
                    `<a href="#" class="list-group-item list-group-item-action ${disabledClass}">${label}</a>`
                );

                start.setMinutes(start.getMinutes() + bookingInterval);
            }
        }

        // Open dropdown on focus
        $meetingTime.on("focus", function () {
            const selectedDate = $meetingDate.val();
            if (!selectedDate) {
                alert("Please select a date first.");
                return;
            }

            let formatted = selectedDate;

            if (_usingDateFormat === 'mm-dd-yy') {
                // Convert "mm-dd-yy" to "yyyy-mm-dd"
                const parts = selectedDate.split("-");
                formatted = `${parts[2]}-${parts[0]}-${parts[1]}`;
            }

            if (_usingDateFormat === 'mm/dd/yy') {
                // Convert "mm/dd/yy" to "yyyy-mm-dd"
                const parts = selectedDate.split("/");
                formatted = `${parts[2]}-${parts[0]}-${parts[1]}`;
            }

            if (_usingDateFormat === 'yy/mm/dd') {
                // Convert "yy/mm/dd" to "yyyy-mm-dd"
                const parts = selectedDate.split("/");
                formatted = `${parts[0]}-${parts[1]}-${parts[1]}`;
            }

            if (_usingDateFormat === 'yy-mm-dd') {
                formatted = selectedDate;
            }

            showTimeDropdown(formatted);
            
        });

        // Handle time slot click
        $dropdown.on("click", ".list-group-item:not(.disabled)", function (e) {
            e.preventDefault();
            $meetingTime.val($(this).text()).valid(); // 🛠️ Trigger validation here
            $dropdown.hide();
        });

        // Hide dropdown on outside click
        $(document).on("click", function (e) {
            if (!$(e.target).closest("#meetingTime, .time-dropdown").length) {
                $dropdown.hide();
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