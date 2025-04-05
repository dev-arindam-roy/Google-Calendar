<?php

namespace App\Http\Controllers\Google;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_FreeBusyRequest;
use Google\Service\Calendar;
use Spatie\GoogleCalendar\Event;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class CalendarBookingEvent extends Controller
{
    public function __construct()
    {
        
    }

    public function index(Request $request)
    {
        $dataBag = [];

        // $client = new Google_Client();
        // $client->setAuthConfig(storage_path('app/google-calendar/service-account-credentials.json'));
        // $client->addScope(Google_Service_Calendar::CALENDAR_READONLY);

        // $service = new Google_Service_Calendar($client);

        // $calendarId = env('GOOGLE_CALENDAR_ID');

        // $now = Carbon::now()->toRfc3339String();
        // $future = Carbon::now()->addDays(90)->toRfc3339String();

        // $optParams = [
        //     'timeMin' => $now,
        //     'timeMax' => $future,
        //     'orderBy' => 'startTime',
        //     'singleEvents' => true,
        //     'maxResults' => 100,
        // ];

        // $results = $service->events->listEvents($calendarId, $optParams);
        // $events = $results->getItems();

        // $formatted = [];

        // foreach ($events as $event) {
        //     $start = $event->start->getDateTime() ?: $event->start->getDate();
        //     $end = $event->end->getDateTime() ?: $event->end->getDate();

        //     $startCarbon = Carbon::parse($start);
        //     $now = Carbon::now();

        //     $status = $startCarbon->lt($now) ? 'past' : 'upcoming';

        //     $formatted[] = [
        //         'summary'     => $event->getSummary(),
        //         'description' => $event->getDescription(),
        //         'start'       => $start,
        //         'end'         => $end,
        //         'status'      => $status,
        //     ];
        // }




    /*** FUTURE & TODAY */
        // Set timezone (use app timezone or your preferred one)
$timezone = config('app.timezone'); // or 'Asia/Kolkata'

$client = new Google_Client();
$client->setAuthConfig(storage_path('app/google-calendar/service-account-credentials.json'));
$client->addScope(Google_Service_Calendar::CALENDAR_READONLY);

$service = new Google_Service_Calendar($client);
$calendarId = env('GOOGLE_CALENDAR_ID');

// Start from beginning of today (00:00) in your timezone
$todayStart = Carbon::now($timezone)->startOfDay()->toRfc3339String();
$future = Carbon::now($timezone)->addDays(90)->toRfc3339String();

$optParams = [
    'timeMin' => $todayStart,
    'timeMax' => $future,
    'orderBy' => 'startTime',
    'singleEvents' => true,
    'maxResults' => 100,
];

$results = $service->events->listEvents($calendarId, $optParams);
$events = $results->getItems();

$formatted = [];

foreach ($events as $event) {
    $start = $event->start->getDateTime() ?: $event->start->getDate();
    $end = $event->end->getDateTime() ?: $event->end->getDate();

    $startCarbon = Carbon::parse($start, $timezone);
    $now = Carbon::now($timezone);

    $status = $startCarbon->lt($now) ? 'past' : 'upcoming';

    if ($status === 'upcoming') {
        $formatted[] = [
            'summary'     => $event->getSummary(),
            'description' => $event->getDescription(),
            'start'       => $start,
            'end'         => $end,
            'status'      => $status,
        ];
    }
}

/**** ALL - PAST & FUTURE */

// $timezone = config('app.timezone'); // or 'Asia/Kolkata'

// $client = new Google_Client();
// $client->setAuthConfig(storage_path('app/google-calendar/service-account-credentials.json'));
// $client->addScope(Google_Service_Calendar::CALENDAR_READONLY);

// $service = new Google_Service_Calendar($client);
// $calendarId = env('GOOGLE_CALENDAR_ID');

// // Show events from 90 days ago to 90 days ahead
// $past = Carbon::now($timezone)->subDays(90)->toRfc3339String();
// $future = Carbon::now($timezone)->addDays(90)->toRfc3339String();

// $optParams = [
//     'timeMin' => $past,
//     'timeMax' => $future,
//     'orderBy' => 'startTime',
//     'singleEvents' => true,
//     'maxResults' => 200,
// ];

// $results = $service->events->listEvents($calendarId, $optParams);
// $events = $results->getItems();

// $formatted = [];

// foreach ($events as $event) {
//     $start = $event->start->getDateTime() ?: $event->start->getDate();
//     $end = $event->end->getDateTime() ?: $event->end->getDate();

//     $startCarbon = Carbon::parse($start, $timezone);
//     $now = Carbon::now($timezone);

//     $status = $startCarbon->lt($now) ? 'past' : 'upcoming';

//     $formatted[] = [
//         'summary'     => $event->getSummary(),
//         'description' => $event->getDescription(),
//         'start'       => $start,
//         'end'         => $end,
//         'status'      => $status,
//     ];
// }





        $dataBag['all_booking'] = json_encode($formatted);
       // dd($dataBag);
        return view('google-calendar.event-booking-service.index2', $dataBag);
        
        
        $events = Event::get();

        dd($events);
        $event = new Event;
        $event->name = 'A new event';
        $event->description = 'Event description';
        $event->startDateTime = Carbon::now();
        $event->endDateTime = Carbon::now()->addHour();
        // $event->addAttendee([
        //     'email' => 'john@example.com',
        //     'name' => 'John Doe',
        //     'comment' => 'Lorum ipsum',
        //     'responseStatus' => 'needsAction',
        // ]);
        // $event->addAttendee(['email' => 'anotherEmail@gmail.com']);
        //$event->addMeetLink(); // optionally add a google meet link to the event

        dd($event->save());
    }

    public function createEventService(Request $request)
    {
        // $requestData = $request->all();

        // $name = $requestData['name'];
        // $email = $requestData['email'];
        // $description = $requestData['description'] ?? null;
        // $date = $requestData['date'] ?? Carbon::now()->format('m-d-Y');
        // $date = Carbon::createFromFormat('m-d-Y', $date)->format('Y-m-d'); // Convert to Y-m-d if needed
        // $time = $requestData['time'] ?? Carbon::now()->addHours(4)->format('h:i A - h:i A');
        // $timezone = "Asia/Kolkata";

        // // Split the time range
        // $timeArr = explode('-', $time);
        // $startTime = trim($timeArr[0]); // e.g., "06:00 AM"
        // $endTime = trim(end($timeArr)); // e.g., "06:30 AM"

        // // Combine date and time strings
        // $startDateTimeStr = $date . ' ' . $startTime; // e.g., "2025-04-10 06:00 AM"
        // $endDateTimeStr = $date . ' ' . $endTime;     // e.g., "2025-04-10 06:30 AM"

        // $startDateTimeStr = Carbon::createFromFormat('m-d-Y', $date)->format('Y-m-d'); // Convert to Y-m-d if needed

        // // Convert to Carbon instances
        // $startDateTime = Carbon::createFromFormat('Y-m-d h:i A', $startDateTimeStr, $timezone);
        // $endDateTime = Carbon::createFromFormat('Y-m-d h:i A', $endDateTimeStr, $timezone);

        $requestData = $request->all();

        $name = $requestData['name'];
        $email = $requestData['email'];
        $description = $requestData['description'] ?? null;
        $date = $requestData['date'] ?? Carbon::now()->format('m-d-Y');
        $timezone = 'Asia/Kolkata';

        // Convert mm-dd-yyyy to Y-m-d
        $dateFormatted = Carbon::createFromFormat('m-d-Y', $date)->format('Y-m-d');

        $time = $requestData['time'] ?? Carbon::now()->addHours(4)->format('h:i A - h:i A');

        // Split time range like "06:00 AM - 06:30 AM"
        $timeArr = explode('-', $time);
        $startTime = trim($timeArr[0]);
        $endTime = trim(end($timeArr));

        // Combine date + time
        $startDateTimeStr = $dateFormatted . ' ' . $startTime;
        $endDateTimeStr = $dateFormatted . ' ' . $endTime;

        // Convert to Carbon instances
        $startDateTime = Carbon::createFromFormat('Y-m-d h:i A', $startDateTimeStr, $timezone);
        $endDateTime = Carbon::createFromFormat('Y-m-d h:i A', $endDateTimeStr, $timezone);


        $client = new Google_Client();
        $client->setAuthConfig(storage_path('app/google-calendar/service-account-credentials.json'));
        $client->addScope(Google_Service_Calendar::CALENDAR);

        $service = new Google_Service_Calendar($client);

        $meetingLink = env('GOOGLE_MEET_LINK');

        $event = new Google_Service_Calendar_Event([
            'summary'     => "Meeting with {$name}",
            'description' => "Booked by: {$name} ({$email})\nDescription: {$description}\nThanks!",
            'location'    => $meetingLink,
            'start'       => [
                'dateTime' => $startDateTime->toRfc3339String(),
                'timeZone' => config('app.timezone'),
            ],
            'end'         => [
                'dateTime' => $endDateTime->toRfc3339String(),
                'timeZone' => config('app.timezone'),
            ],
            'colorId' => '6',
            'conferenceData' => [
                'createRequest' => [
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                    'requestId' => uniqid(),
                ]
            ],
        ]);

        $calendarId = env('GOOGLE_CALENDAR_ID');
        $service->events->insert($calendarId, $event);

        return response()->json(['isSuccess' => true]);
    }
}
