<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ClubSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        SeederIds::$club = Str::uuid()->toString();

        DB::table('clubs')->insert([
            'id'            => SeederIds::$club,
            'name'          => 'FK Zlín Mládež',
            'slug'          => 'fk-zlin-mladez',
            'primary_sport' => 'football',
            'address'       => 'Stadion Letná, Zlín',
            'logo_url'      => null,
            'color'         => '#1B6B4A',
            'bank_account'  => 'CZ6508000000001234567890',
            'settings'      => json_encode([
                'assistant_can_create_training' => true,
                'default_locale' => 'cs',
            ]),
            'billing_plan'  => 'pro',
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        // Club memberships
        DB::table('club_memberships')->insert([
            [
                'id'        => Str::uuid()->toString(),
                'user_id'   => SeederIds::$michal,
                'club_id'   => SeederIds::$club,
                'role'      => 'owner',
                'status'    => 'active',
                'joined_at' => $now,
            ],
            [
                'id'        => Str::uuid()->toString(),
                'user_id'   => SeederIds::$jan,
                'club_id'   => SeederIds::$club,
                'role'      => 'admin',
                'status'    => 'active',
                'joined_at' => $now,
            ],
            [
                'id'        => Str::uuid()->toString(),
                'user_id'   => SeederIds::$pavel,
                'club_id'   => SeederIds::$club,
                'role'      => 'member',
                'status'    => 'active',
                'joined_at' => $now,
            ],
            [
                'id'        => Str::uuid()->toString(),
                'user_id'   => SeederIds::$eva,
                'club_id'   => SeederIds::$club,
                'role'      => 'member',
                'status'    => 'active',
                'joined_at' => $now,
            ],
            [
                'id'        => Str::uuid()->toString(),
                'user_id'   => SeederIds::$martin,
                'club_id'   => SeederIds::$club,
                'role'      => 'member',
                'status'    => 'active',
                'joined_at' => $now,
            ],
            [
                'id'        => Str::uuid()->toString(),
                'user_id'   => SeederIds::$adam,
                'club_id'   => SeederIds::$club,
                'role'      => 'member',
                'status'    => 'active',
                'joined_at' => $now,
            ],
        ]);

        $this->command->info('ClubSeeder: Klub a 5 členství vytvořeno.');
    }
}
