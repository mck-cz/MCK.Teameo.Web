<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ---------------------------------------------------------------
        // Recurrence rules
        // ---------------------------------------------------------------
        $rrUtery   = Str::uuid()->toString();
        $rrCtvrtek = Str::uuid()->toString();

        DB::table('recurrence_rules')->insert([
            [
                'id'                       => $rrUtery,
                'club_id'                  => SeederIds::$club,
                'team_id'                  => SeederIds::$teamU9,
                'name'                     => 'Úterní trénink hala',
                'event_type'               => 'training',
                'frequency'                => 'weekly',
                'interval'                 => 1,
                'day_of_week'              => 1,
                'week_parity'              => null,
                'nth_weekday'              => null,
                'time_start'               => '17:00:00',
                'time_end'                 => '18:30:00',
                'venue_id'                 => SeederIds::$venueHala,
                'surface_type'             => null,
                'instructions_template_id' => null,
                'equipment_template_id'    => null,
                'auto_create_days_ahead'   => 14,
                'auto_rsvp'                => true,
                'valid_from'               => '2025-09-01',
                'valid_until'              => null,
                'is_active'                => true,
                'created_by'               => SeederIds::$jan,
                'created_at'               => $now,
                'updated_at'               => $now,
            ],
            [
                'id'                       => $rrCtvrtek,
                'club_id'                  => SeederIds::$club,
                'team_id'                  => SeederIds::$teamU9,
                'name'                     => 'Čtvrteční trénink venku',
                'event_type'               => 'training',
                'frequency'                => 'weekly',
                'interval'                 => 1,
                'day_of_week'              => 3,
                'week_parity'              => null,
                'nth_weekday'              => null,
                'time_start'               => '16:30:00',
                'time_end'                 => '18:00:00',
                'venue_id'                 => SeederIds::$venueHlavni,
                'surface_type'             => null,
                'instructions_template_id' => null,
                'equipment_template_id'    => null,
                'auto_create_days_ahead'   => 14,
                'auto_rsvp'                => true,
                'valid_from'               => '2025-09-01',
                'valid_until'              => null,
                'is_active'                => true,
                'created_by'               => SeederIds::$jan,
                'created_at'               => $now,
                'updated_at'               => $now,
            ],
        ]);

        // ---------------------------------------------------------------
        // Individual events
        // ---------------------------------------------------------------
        $tomorrow  = Carbon::tomorrow();
        $dayAfter  = Carbon::tomorrow()->addDay();
        $inFiveDays = Carbon::today()->addDays(5);

        // Event 1: Trénink U9 — zítra
        $eventTrU9 = Str::uuid()->toString();
        DB::table('events')->insert([
            'id'                   => $eventTrU9,
            'club_id'              => SeederIds::$club,
            'team_id'              => SeederIds::$teamU9,
            'venue_id'             => SeederIds::$venueHlavni,
            'location'             => null,
            'created_by'           => SeederIds::$jan,
            'event_type'           => 'training',
            'title'                => 'Trénink U9',
            'surface_type'         => null,
            'starts_at'            => $tomorrow->copy()->setTime(17, 0),
            'ends_at'              => $tomorrow->copy()->setTime(18, 30),
            'recurrence_rule_id'   => null,
            'rsvp_deadline'        => null,
            'nomination_deadline'  => null,
            'min_capacity'         => null,
            'max_capacity'         => null,
            'instructions'         => null,
            'notes'                => null,
            'status'               => 'scheduled',
            'cancel_reason'        => null,
            'rescheduled_to'       => null,
            'cancelled_by'         => null,
            'cancelled_at'         => null,
            'created_at'           => $now,
            'updated_at'           => $now,
        ]);

        // Event 2: Zápas U9 vs SK Kroměříž — za 5 dní
        $eventMatchU9 = Str::uuid()->toString();
        DB::table('events')->insert([
            'id'                   => $eventMatchU9,
            'club_id'              => SeederIds::$club,
            'team_id'              => SeederIds::$teamU9,
            'venue_id'             => SeederIds::$venueHlavni,
            'location'             => null,
            'created_by'           => SeederIds::$jan,
            'event_type'           => 'match',
            'title'                => 'U9 vs SK Kroměříž',
            'surface_type'         => null,
            'starts_at'            => $inFiveDays->copy()->setTime(10, 0),
            'ends_at'              => $inFiveDays->copy()->setTime(12, 0),
            'recurrence_rule_id'   => null,
            'rsvp_deadline'        => null,
            'nomination_deadline'  => $inFiveDays->copy()->subDays(3)->setTime(18, 0),
            'min_capacity'         => null,
            'max_capacity'         => null,
            'instructions'         => null,
            'notes'                => null,
            'status'               => 'scheduled',
            'cancel_reason'        => null,
            'rescheduled_to'       => null,
            'cancelled_by'         => null,
            'cancelled_at'         => null,
            'created_at'           => $now,
            'updated_at'           => $now,
        ]);

        // Event 3: Trénink U12 — pozítří
        $eventTrU12 = Str::uuid()->toString();
        DB::table('events')->insert([
            'id'                   => $eventTrU12,
            'club_id'              => SeederIds::$club,
            'team_id'              => SeederIds::$teamU12,
            'venue_id'             => SeederIds::$venueHala,
            'location'             => null,
            'created_by'           => SeederIds::$jan,
            'event_type'           => 'training',
            'title'                => 'Trénink U12',
            'surface_type'         => null,
            'starts_at'            => $dayAfter->copy()->setTime(16, 0),
            'ends_at'              => $dayAfter->copy()->setTime(17, 30),
            'recurrence_rule_id'   => null,
            'rsvp_deadline'        => null,
            'nomination_deadline'  => null,
            'min_capacity'         => null,
            'max_capacity'         => null,
            'instructions'         => null,
            'notes'                => null,
            'status'               => 'scheduled',
            'cancel_reason'        => null,
            'rescheduled_to'       => null,
            'cancelled_by'         => null,
            'cancelled_at'         => null,
            'created_at'           => $now,
            'updated_at'           => $now,
        ]);

        // ---------------------------------------------------------------
        // Attendances — Trénink U9 (athletes: Tomáš, Ema)
        // ---------------------------------------------------------------
        DB::table('attendances')->insert([
            [
                'id'                 => Str::uuid()->toString(),
                'event_id'           => $eventTrU9,
                'team_membership_id' => SeederIds::$tmTomasU9,
                'rsvp_status'        => 'confirmed',
                'rsvp_note'          => null,
                'responded_by'       => SeederIds::$eva,
                'responded_at'       => $now,
                'actual_status'      => null,
                'checked_by'         => null,
                'checked_at'         => null,
            ],
            [
                'id'                 => Str::uuid()->toString(),
                'event_id'           => $eventTrU9,
                'team_membership_id' => SeederIds::$tmEmaU9,
                'rsvp_status'        => 'pending',
                'rsvp_note'          => null,
                'responded_by'       => null,
                'responded_at'       => null,
                'actual_status'      => null,
                'checked_by'         => null,
                'checked_at'         => null,
            ],
        ]);

        // Attendances — Zápas U9 (athletes: Tomáš confirmed, Ema declined)
        DB::table('attendances')->insert([
            [
                'id'                 => Str::uuid()->toString(),
                'event_id'           => $eventMatchU9,
                'team_membership_id' => SeederIds::$tmTomasU9,
                'rsvp_status'        => 'confirmed',
                'rsvp_note'          => null,
                'responded_by'       => SeederIds::$eva,
                'responded_at'       => $now,
                'actual_status'      => null,
                'checked_by'         => null,
                'checked_at'         => null,
            ],
            [
                'id'                 => Str::uuid()->toString(),
                'event_id'           => $eventMatchU9,
                'team_membership_id' => SeederIds::$tmEmaU9,
                'rsvp_status'        => 'declined',
                'rsvp_note'          => 'Rodinná oslava',
                'responded_by'       => SeederIds::$martin,
                'responded_at'       => $now,
                'actual_status'      => null,
                'checked_by'         => null,
                'checked_at'         => null,
            ],
        ]);

        // Attendances — Trénink U12 (athletes: Jakub, Adam)
        DB::table('attendances')->insert([
            [
                'id'                 => Str::uuid()->toString(),
                'event_id'           => $eventTrU12,
                'team_membership_id' => SeederIds::$tmJakubU12,
                'rsvp_status'        => 'confirmed',
                'rsvp_note'          => null,
                'responded_by'       => SeederIds::$martin,
                'responded_at'       => $now,
                'actual_status'      => null,
                'checked_by'         => null,
                'checked_at'         => null,
            ],
            [
                'id'                 => Str::uuid()->toString(),
                'event_id'           => $eventTrU12,
                'team_membership_id' => SeederIds::$tmAdamU12,
                'rsvp_status'        => 'pending',
                'rsvp_note'          => null,
                'responded_by'       => null,
                'responded_at'       => null,
                'actual_status'      => null,
                'checked_by'         => null,
                'checked_at'         => null,
            ],
        ]);

        // ---------------------------------------------------------------
        // Nominations — Zápas U9
        // ---------------------------------------------------------------
        DB::table('nominations')->insert([
            [
                'id'                 => Str::uuid()->toString(),
                'event_id'           => $eventMatchU9,
                'team_membership_id' => SeederIds::$tmTomasU9,
                'source_team_id'     => SeederIds::$teamU9,
                'status'             => 'nominated',
                'priority'           => 1,
                'nominated_by'       => SeederIds::$jan,
                'responded_by'       => null,
                'responded_at'       => null,
            ],
            [
                'id'                 => Str::uuid()->toString(),
                'event_id'           => $eventMatchU9,
                'team_membership_id' => SeederIds::$tmEmaU9,
                'source_team_id'     => SeederIds::$teamU9,
                'status'             => 'nominated',
                'priority'           => 1,
                'nominated_by'       => SeederIds::$jan,
                'responded_by'       => null,
                'responded_at'       => null,
            ],
        ]);

        $this->command->info('EventSeeder: 2 pravidla opakování, 3 události, 6 docházek a 2 nominace vytvořeny.');
    }
}
