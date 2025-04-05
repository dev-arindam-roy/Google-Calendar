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
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h2>Book / Schedule appointment for Bug / Code Fix</h2>
                    </div>
                    <div class="card-body">
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
                                        <input type="text" class="form-control" name="time" id="meetingTime" placeholder="Choose a time">
                                        <label for="meetingTime">Choose a time</label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-primary" id="createBookEventBtn">Schedule Meeting</button>
                        <button type="button" class="btn btn-danger" id="resetBookEventBtn">Cancel / Reset</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <p id="allBooking">{{ $all_booking }}</p>
@endsection

@push('page_script_links')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- jQuery UI (Datepicker) -->
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<!-- jQuery Timepicker (by Jon Thornton) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>

@endpush

@push('page_scripts')
<script>
    $(document).ready(function () {
        $("#meetingDate").datepicker({
            dateFormat: "mm-dd-yy",   // Format: 2025-04-05
            minDate: 0,               // Disable all past dates
            showAnim: "fadeIn",       // Optional animation
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

        const $input = $("#meetingTime");
const $dateInput = $("#meetingDate");

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
    width: $input.outerWidth()
});
$("body").append($dropdown);

// Show and populate dropdown
function showTimeDropdown(selectedDate) {
    const dropdownOffset = $input.offset();
    $dropdown.css({
        top: dropdownOffset.top + $input.outerHeight(),
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
        slotEnd.setMinutes(slotEnd.getMinutes() + 30);

        const isBooked = isSlotBooked(slotStart, slotEnd, bookingsForDate);
        const label = `${formatAMPM(slotStart)} - ${formatAMPM(slotEnd)}`;
        const disabledClass = isBooked ? "disabled text-muted" : "";

        $dropdown.append(
            `<a href="#" class="list-group-item list-group-item-action ${disabledClass}">${label}</a>`
        );

        start.setMinutes(start.getMinutes() + 30);
    }
}

// Open dropdown on focus
$input.on("focus", function () {
    const selectedDate = $dateInput.val(); // e.g., 04-10-2025
    if (!selectedDate) {
        alert("Please select a date first.");
        return;
    }

    // Convert "mm-dd-yy" to "yyyy-mm-dd"
    const parts = selectedDate.split("-");
    const formatted = `${parts[2]}-${parts[0]}-${parts[1]}`;

    showTimeDropdown(formatted);
});

// Handle time slot click
$dropdown.on("click", ".list-group-item:not(.disabled)", function (e) {
    e.preventDefault();
    $input.val($(this).text());
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
            let $form = $('#bookEventFrm');
            let form = $form[0];
            let formData = new FormData(form);

            $.ajax({
                url: $form.attr('action'),
                type: $form.attr('method'),
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                beforeSend: function () {
                    console.log('Sending form...');
                },
                success: function (response) {
                    console.log('Success:', response);
                },
                error: function (xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        });
    });
</script>
@endpush