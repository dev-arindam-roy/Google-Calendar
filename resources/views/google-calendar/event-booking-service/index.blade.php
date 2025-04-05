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
    border-radius: 4px;
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

        // Create a custom dropdown using a datalist-like popup
        const $dropdown = $("<div class='time-dropdown list-group'></div>").css({
            position: "absolute",
            zIndex: 1000,
            display: "none",
            width: $input.outerWidth()
        });

        $("body").append($dropdown);

        function formatAMPM(date) {
            let hours = date.getHours();
            let minutes = date.getMinutes();
            const ampm = hours >= 12 ? "PM" : "AM";
            hours = hours % 12 || 12;
            minutes = minutes < 10 ? "0" + minutes : minutes;
            return hours + ":" + minutes + " " + ampm;
        }

        const start = new Date();
        start.setHours(6, 0, 0); // 6:00 AM

        const end = new Date();
        end.setHours(23, 55, 0); // 11:55 PM

        const timeSlots = [];

        while (start < end) {
            const slotStart = new Date(start);
            const slotEnd = new Date(start);
            slotEnd.setMinutes(slotStart.getMinutes() + 30);
            //slotEnd.setMinutes(slotStart.getMinutes() + 60);
            const range = `${formatAMPM(slotStart)} - ${formatAMPM(slotEnd)}`;
            timeSlots.push(range);
            start.setMinutes(start.getMinutes() + 30);
            //start.setMinutes(start.getMinutes() + 60);
        }

        // Populate dropdown
        timeSlots.forEach(slot => {
            $dropdown.append(`<a href="#" class="list-group-item list-group-item-action">${slot}</a>`);
        });

        // Show dropdown on focus
        $input.on("focus", function () {
            const offset = $input.offset();
            $dropdown.css({
                top: offset.top + $input.outerHeight(),
                left: offset.left,
                display: "block"
            });
        });

        // Hide dropdown when clicking outside
        $(document).on("click", function (e) {
            if (!$(e.target).closest("#meetingTime, .time-dropdown").length) {
                $dropdown.hide();
            }
        });

        // Handle selection
        $dropdown.on("click", ".list-group-item", function (e) {
            e.preventDefault();
            $input.val($(this).text());
            $dropdown.hide();
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