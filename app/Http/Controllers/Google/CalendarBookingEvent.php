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
use Exception;

class CalendarBookingEvent extends Controller
{

    public function __construct()
    {

    }

    public function index(Request $request)
    {
        $dataBag = [];

        $allSchedules = $this->retriveCalendarSchedules();

        // Separate arrays based on 'status'
        $pastSchedules = array_filter($allSchedules, fn($event) => $event['status'] === 'past');
        $upcomingSchedules = array_filter($allSchedules, fn($event) => $event['status'] === 'upcoming');

        // Optional: reindex arrays
        $pastSchedules = array_values($pastSchedules);
        $upcomingSchedules = array_values($upcomingSchedules);

        $dataBag['all_booking'] = $allSchedules;
        $dataBag['past_booking'] = $pastSchedules;
        $dataBag['upcoming_booking'] = $upcomingSchedules;
        
        //dd($dataBag);
        return view('google-calendar.event-booking-service.index2', $dataBag);
        
    }

    public function retriveCalendarSchedules($scheduleStatus = null)
    {
        $timezone = config('app.timezone');

        $client = new Google_Client();
        $client->setAuthConfig(storage_path('app/google-calendar/service-account-credentials.json'));
        $client->addScope(Google_Service_Calendar::CALENDAR_READONLY);

        $service = new Google_Service_Calendar($client);
        $calendarId = env('GOOGLE_CALENDAR_ID');

        $now = Carbon::now($timezone);

        if (!empty($scheduleStatus)) {
            $scheduleStatus = strtolower($scheduleStatus);
        }

        // Define time ranges
        if ($scheduleStatus === 'past') {
            $timeMin = $now->copy()->subDays(90)->startOfDay()->toRfc3339String();
            $timeMax = $now->toRfc3339String();
        } elseif ($scheduleStatus === 'upcoming') {
            $timeMin = $now->toRfc3339String();
            $timeMax = $now->copy()->addDays(90)->toRfc3339String();
        } else {
            // Get both past and upcoming from today at 00:00 to 90 days ahead
            $timeMin = $now->copy()->subDays(90)->startOfDay()->toRfc3339String();
            $timeMax = $now->copy()->addDays(90)->toRfc3339String();
        }

        $optParams = [
            'timeMin' => $timeMin,
            'timeMax' => $timeMax,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'maxResults' => 2500,
        ];

        $results = $service->events->listEvents($calendarId, $optParams);
        $events = $results->getItems();

        $formatted = [];

        foreach ($events as $event) {
            //\Log::info(json_encode($event));
            $start = $event->start->getDateTime() ?: $event->start->getDate();
            $end = $event->end->getDateTime() ?: $event->end->getDate();
            $calendarHtmlLink = $event->htmlLink ?? null;
            if (!empty($calendarHtmlLink)) {
                $calendarHtmlLink = $calendarHtmlLink . '&authuser=' . urlencode(env('GOOGLE_CALENDAR_ID'));
            }
            $meetingId = $event->id ?? null;
            $meetingLink = $event->location ?? null;
            $meetingStatus = $event->status ?? null;
            $startTimeZone = $event->start->timeZone ?? null;
            $endTimeZone = $event->end->timeZone ?? null;

            $startCarbon = Carbon::parse($start, $timezone);
            $status = $startCarbon->lt($now) ? 'past' : 'upcoming';

            // Filter based on specific request if needed
            if ($scheduleStatus === 'past' && $status !== 'past') {
                continue;
            }
            if ($scheduleStatus === 'upcoming' && $status !== 'upcoming') {
                continue;
            }

            $formatted[] = [
                'summary'     => $event->getSummary(),
                'description' => $event->getDescription(),
                'start'       => $start,
                'end'         => $end,
                'status'      => $status,
                'meeting_link'=> $meetingLink,
                'meeting_id'  => $meetingId,
                'meeting_status' => $meetingStatus,
                'start_timezone' => $startTimeZone,
                'end_timezone' => $endTimeZone,
                'calendar_html_link' => $calendarHtmlLink
            ];
        }

        return $formatted;
    }

    public function createEventService(Request $request)
    {
        $requestData = $request->all();

        $name = $requestData['name'];
        $email = $requestData['email'];
        $description = $requestData['description'] ?? null;
        $date = $requestData['date'] ?? Carbon::now()->format('m-d-Y');
        $time = $requestData['time'] ?? Carbon::now()->addHours(4)->format('h:i A - h:i A');
        $usingDateFormat = $requestData['using_date_format'] ?? 'Y-m-d';
        $timezone = 'Asia/Kolkata';

        try {
            if ($usingDateFormat === 'mm-dd-yy') {
                $dateFormatted = Carbon::createFromFormat('m-d-Y', $date)->format('Y-m-d');
            } else {
                $dateFormatted = Carbon::createFromFormat('Y-m-d', $date)->format('Y-m-d');
            }
        } catch (Exception $e) {
            return response()->json([
                'isSuccess' => false,
                'message' => 'Invalid date format',
                'error' => $e->getMessage()
            ], 400);
        }

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
            'colorId' => self::googleCalendarColorMark(),
            'visibility' => 'public', //'default' or 'public' or 'private'
            'status' => 'confirmed',
            'guestsCanModify' => false,
            'guestsCanInviteOthers' => false,
            'guestsCanSeeOtherGuests' => false,
            'reminders'   => [
                'useDefault' => false,
                'overrides' => [
                    ['method' => 'email', 'minutes' => 30],
                    ['method' => 'popup', 'minutes' => 10],
                ],
            ],
            'extendedProperties' => [
                'shared' => [
                    'booking_id' => '12345',
                    'user_id'    => '7890',
                    'source'     => 'web-form',
                ]
            ],
            'source' => [
                'title' => 'Booking Form',
                'url'   => 'https://yourapp.com/booking/12345'
            ],
            'transparency' => 'opaque',     // block time , 'transparent' or 'opaque'
            'privateCopy'  => false,        // let others see full details
            'conferenceData' => [
                'createRequest' => [
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                    'requestId' => uniqid(),
                ]
            ],
        ]);

        $calendarId = env('GOOGLE_CALENDAR_ID');
        $eventServiceResponse = $service->events->insert($calendarId, $event);

        return response()->json(['isSuccess' => true, 'message' => 'Your meeting has been scheduled successfully', 'data' => $eventServiceResponse]);
    }

    public static function googleCalendarColorMark()
    {
        $googleCalendarColors = [
            ['id' => 1,  'name' => 'Lavender',   'hex' => '#a4bdfc'],
            ['id' => 2,  'name' => 'Sage',       'hex' => '#7ae7bf'],
            ['id' => 3,  'name' => 'Grape',      'hex' => '#dbadff'],
            ['id' => 4,  'name' => 'Flamingo',   'hex' => '#ff887c'],
            ['id' => 5,  'name' => 'Banana',     'hex' => '#fbd75b'],
            ['id' => 6,  'name' => 'Tangerine',  'hex' => '#ffb878'],
            ['id' => 7,  'name' => 'Peacock',    'hex' => '#46d6db'],
            ['id' => 8,  'name' => 'Graphite',   'hex' => '#e1e1e1'],
            ['id' => 9,  'name' => 'Blueberry',  'hex' => '#5484ed'],
            ['id' => 10, 'name' => 'Basil',      'hex' => '#51b749'],
            ['id' => 11, 'name' => 'Tomato',     'hex' => '#dc2127'],
        ];
        
        $color = $googleCalendarColors[array_rand($googleCalendarColors)];
        return $color['id'] ?? 6;
    }
}

