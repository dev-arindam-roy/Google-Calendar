<?php

namespace App\Http\Controllers\Google;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        $client = $this->getGoogleClient();
        return redirect()->away($client->createAuthUrl());
    }

    public function handleGoogleCallback(Request $request)
    {
        $client = $this->getGoogleClient();
        $accessToken = $client->fetchAccessTokenWithAuthCode($request->code);

        session(['google_token' => $accessToken]);

        return redirect()->route('calendar.create');
    }

    private function getGoogleClient()
    {
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $client->addScope(Google_Service_Calendar::CALENDAR);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        if (session()->has('google_token')) {
            $client->setAccessToken(session('google_token'));
        }

        return $client;
    }

    public function createCalendarEvent(Request $request)
    {
        $client = $this->getGoogleClient();

        if ($client->isAccessTokenExpired()) {
            return redirect()->route('google.auth');
        }

        $service = new Google_Service_Calendar($client);

        $event = new Google_Service_Calendar_Event([
            'summary' => 'Meeting with ' . $request->name,
            'description' => "Booked by: {$request->name} ({$request->email})",
            'start' => [
                'dateTime' => now()->addHour()->toRfc3339String(),
                'timeZone' => config('app.timezone'),
            ],
            'end' => [
                'dateTime' => now()->addHours(2)->toRfc3339String(),
                'timeZone' => config('app.timezone'),
            ],
            'attendees' => [
                ['email' => $request->email],
            ],
            'conferenceData' => [
                'createRequest' => [
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                    'requestId' => uniqid(),
                ],
            ],
        ]);

        $calendarId = 'primary'; // Or custom calendar ID
        $event = $service->events->insert($calendarId, $event, ['conferenceDataVersion' => 1]);

        return response()->json([
            'meet_link' => $event->getHangoutLink(),
            'event_link' => $event->getHtmlLink(),
        ]);
    }
}
