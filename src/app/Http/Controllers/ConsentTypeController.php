<?php

namespace App\Http\Controllers;

use App\Models\ClubMembership;
use App\Models\Consent;
use App\Models\ConsentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsentTypeController extends Controller
{
    private function authorizeClubAdmin(): void
    {
        $clubId = session('current_club_id');
        $exists = ClubMembership::where('club_id', $clubId)
            ->where('user_id', Auth::id())
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->exists();
        abort_unless($exists, 403);
    }

    public function index()
    {
        $clubId = session('current_club_id');

        $consentTypes = ConsentType::where('club_id', $clubId)
            ->orderBy('sort_order')
            ->get();

        // User's own consents
        $userConsents = Consent::where('user_id', Auth::id())
            ->whereHas('consentType', fn ($q) => $q->where('club_id', $clubId))
            ->get()
            ->keyBy('consent_type_id');

        return view('consents.index', compact('consentTypes', 'userConsents'));
    }

    public function store(Request $request)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_required' => 'boolean',
        ]);

        $maxOrder = ConsentType::where('club_id', $clubId)->max('sort_order') ?? 0;

        ConsentType::create([
            ...$validated,
            'club_id' => $clubId,
            'is_required' => $validated['is_required'] ?? false,
            'sort_order' => $maxOrder + 1,
        ]);

        return back()->with('success', __('messages.consents.type_created'));
    }

    public function destroy(ConsentType $consentType)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($consentType->club_id === $clubId, 404);

        $consentType->consents()->delete();
        $consentType->delete();

        return back()->with('success', __('messages.consents.type_deleted'));
    }

    public function grant(Request $request, ConsentType $consentType)
    {
        $clubId = session('current_club_id');
        abort_unless($consentType->club_id === $clubId, 404);

        $userId = Auth::id();

        Consent::updateOrCreate(
            ['consent_type_id' => $consentType->id, 'user_id' => $userId],
            [
                'granted' => true,
                'granted_by' => $userId,
                'granted_at' => now(),
                'revoked_at' => null,
            ]
        );

        return back()->with('success', __('messages.consents.granted'));
    }

    public function revoke(ConsentType $consentType)
    {
        $clubId = session('current_club_id');
        abort_unless($consentType->club_id === $clubId, 404);

        $consent = Consent::where('consent_type_id', $consentType->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($consent) {
            $consent->update([
                'granted' => false,
                'revoked_at' => now(),
            ]);
        }

        return back()->with('success', __('messages.consents.revoked'));
    }
}
