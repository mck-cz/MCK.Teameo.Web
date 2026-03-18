<?php

namespace App\Http\Controllers;

use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Team;
use App\Models\TeamMembership;
use App\Models\TeamPost;
use App\Models\TeamPostComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamPostController extends Controller
{
    public function index(Team $team)
    {
        $clubId = session('current_club_id');
        abort_unless($team->club_id === $clubId, 404);

        $posts = TeamPost::where('team_id', $team->id)
            ->with(['user', 'teamPostComments.user', 'pollOptions.pollVotes'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('teams.wall', compact('team', 'posts'));
    }

    public function store(Request $request, Team $team)
    {
        $clubId = session('current_club_id');
        abort_unless($team->club_id === $clubId, 404);

        $this->authorizeTeamMember($team);

        $validated = $request->validate([
            'body' => 'required|string|max:2000',
            'post_type' => 'required|in:message,poll',
            'poll_options' => 'required_if:post_type,poll|array|min:2|max:10',
            'poll_options.*' => 'required|string|max:255',
        ]);

        $post = TeamPost::create([
            'team_id' => $team->id,
            'user_id' => Auth::id(),
            'body' => $validated['body'],
            'post_type' => $validated['post_type'],
        ]);

        if ($validated['post_type'] === 'poll' && !empty($validated['poll_options'])) {
            foreach ($validated['poll_options'] as $i => $label) {
                if (trim($label) !== '') {
                    PollOption::create([
                        'post_id' => $post->id,
                        'label' => trim($label),
                        'sort_order' => $i,
                    ]);
                }
            }
        }

        return back()->with('success', __('messages.wall.posted'));
    }

    public function storeComment(Request $request, TeamPost $teamPost)
    {
        $clubId = session('current_club_id');
        $team = $teamPost->team;
        abort_unless($team->club_id === $clubId, 404);

        $this->authorizeTeamMember($team);

        $validated = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        TeamPostComment::create([
            'post_id' => $teamPost->id,
            'user_id' => Auth::id(),
            'body' => $validated['body'],
        ]);

        return back()->with('success', __('messages.wall.comment_posted'));
    }

    public function vote(Request $request, PollOption $pollOption)
    {
        $teamPost = $pollOption->teamPost;
        $clubId = session('current_club_id');
        abort_unless($teamPost->team->club_id === $clubId, 404);

        $userId = Auth::id();

        // Remove existing votes on this poll
        $optionIds = PollOption::where('post_id', $teamPost->id)->pluck('id');
        PollVote::whereIn('option_id', $optionIds)
            ->where('user_id', $userId)
            ->delete();

        // Add new vote
        PollVote::create([
            'option_id' => $pollOption->id,
            'user_id' => $userId,
        ]);

        return back()->with('success', __('messages.wall.voted'));
    }

    public function destroy(TeamPost $teamPost)
    {
        abort_unless($teamPost->user_id === Auth::id(), 403);

        $teamPost->teamPostComments()->delete();
        $optionIds = $teamPost->pollOptions()->pluck('id');
        PollVote::whereIn('option_id', $optionIds)->delete();
        $teamPost->pollOptions()->delete();
        $teamPost->delete();

        return back()->with('success', __('messages.wall.deleted'));
    }

    private function authorizeTeamMember(Team $team): void
    {
        $exists = TeamMembership::where('team_id', $team->id)
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->exists();

        abort_unless($exists, 403);
    }
}
