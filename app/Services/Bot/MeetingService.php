<?php
namespace App\Services\Bot;

use App\Models\Meeting;
use Carbon\Carbon;

class MeetingService
{
    public function getTodayMeetings()
    {
        return Meeting::whereDate('meeting_date', Carbon::today())
            ->orderBy('meeting_date')
            ->get();
    }

    public function getTodayMeetingsText(): string
    {
        $meetings = $this->getTodayMeetings();

        if ($meetings->isEmpty()) {
            return "No meetings scheduled for today.";
        }

        $lines = ["📅 *Today's Meetings:*\n"];

        foreach ($meetings as $meeting) {
            $time = Carbon::parse($meeting->meeting_date)->format('h:i A');
            $lines[] = "• *{$meeting->title}*";
            $lines[] = "  🕐 Time: {$time}";
            $lines[] = "  🔗 Link: {$meeting->meeting_url}\n";
        }

        return implode("\n", $lines);
    }
}
