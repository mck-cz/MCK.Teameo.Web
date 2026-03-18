<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Season
        SeederIds::$season = Str::uuid()->toString();

        DB::table('seasons')->insert([
            'id'         => SeederIds::$season,
            'club_id'    => SeederIds::$club,
            'name'       => '2025/2026',
            'start_date' => '2025-09-01',
            'end_date'   => '2026-06-30',
        ]);

        // Teams
        SeederIds::$teamU9  = Str::uuid()->toString();
        SeederIds::$teamU12 = Str::uuid()->toString();

        DB::table('teams')->insert([
            [
                'id'           => SeederIds::$teamU9,
                'club_id'      => SeederIds::$club,
                'season_id'    => SeederIds::$season,
                'name'         => 'U9 Přípravka',
                'sport'        => 'football',
                'age_category' => 'U9',
                'color'        => null,
                'is_active'    => true,
                'is_archived'  => false,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'id'           => SeederIds::$teamU12,
                'club_id'      => SeederIds::$club,
                'season_id'    => SeederIds::$season,
                'name'         => 'U12 Žáci',
                'sport'        => 'football',
                'age_category' => 'U12',
                'color'        => null,
                'is_active'    => true,
                'is_archived'  => false,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ]);

        // Team memberships — U9
        SeederIds::$tmJanU9   = Str::uuid()->toString();
        SeederIds::$tmPavelU9 = Str::uuid()->toString();
        SeederIds::$tmTomasU9 = Str::uuid()->toString();
        SeederIds::$tmEmaU9   = Str::uuid()->toString();

        DB::table('team_memberships')->insert([
            [
                'id'        => SeederIds::$tmJanU9,
                'team_id'   => SeederIds::$teamU9,
                'user_id'   => SeederIds::$jan,
                'role'      => 'head_coach',
                'status'    => 'active',
                'position'  => null,
                'joined_at' => $now,
            ],
            [
                'id'        => SeederIds::$tmPavelU9,
                'team_id'   => SeederIds::$teamU9,
                'user_id'   => SeederIds::$pavel,
                'role'      => 'assistant_coach',
                'status'    => 'active',
                'position'  => null,
                'joined_at' => $now,
            ],
            [
                'id'        => SeederIds::$tmTomasU9,
                'team_id'   => SeederIds::$teamU9,
                'user_id'   => SeederIds::$tomas,
                'role'      => 'athlete',
                'status'    => 'active',
                'position'  => null,
                'joined_at' => $now,
            ],
            [
                'id'        => SeederIds::$tmEmaU9,
                'team_id'   => SeederIds::$teamU9,
                'user_id'   => SeederIds::$ema,
                'role'      => 'athlete',
                'status'    => 'active',
                'position'  => null,
                'joined_at' => $now,
            ],
        ]);

        // Team memberships — U12
        SeederIds::$tmJanU12   = Str::uuid()->toString();
        SeederIds::$tmJakubU12 = Str::uuid()->toString();
        SeederIds::$tmAdamU12  = Str::uuid()->toString();

        DB::table('team_memberships')->insert([
            [
                'id'        => SeederIds::$tmJanU12,
                'team_id'   => SeederIds::$teamU12,
                'user_id'   => SeederIds::$jan,
                'role'      => 'head_coach',
                'status'    => 'active',
                'position'  => null,
                'joined_at' => $now,
            ],
            [
                'id'        => SeederIds::$tmJakubU12,
                'team_id'   => SeederIds::$teamU12,
                'user_id'   => SeederIds::$jakub,
                'role'      => 'athlete',
                'status'    => 'active',
                'position'  => null,
                'joined_at' => $now,
            ],
            [
                'id'        => SeederIds::$tmAdamU12,
                'team_id'   => SeederIds::$teamU12,
                'user_id'   => SeederIds::$adam,
                'role'      => 'athlete',
                'status'    => 'active',
                'position'  => null,
                'joined_at' => $now,
            ],
        ]);

        $this->command->info('TeamSeeder: 1 sezóna, 2 týmy a 7 členství vytvořeno.');
    }
}
