<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventCommentController extends Controller
{
    public function store(Request $request, Event $event)
    {
        $clubId = session('current_club_id');
        abort_unless($event->club_id === $clubId, 403);

        $validated = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        EventComment::create([
            'event_id' => $event->id,
            'user_id' => Auth::id(),
            'body' => $validated['body'],
        ]);

        return back()->with('success', __('messages.comments.posted'));
    }

    public function destroy(EventComment $eventComment)
    {
        abort_unless($eventComment->user_id === Auth::id(), 403);

        $eventComment->delete();

        return back()->with('success', __('messages.comments.deleted'));
    }
}
