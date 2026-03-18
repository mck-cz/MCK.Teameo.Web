<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $password = Hash::make('změňteHeslo123!');

        // Generate UUIDs
        SeederIds::$michal = Str::uuid()->toString();
        SeederIds::$jan    = Str::uuid()->toString();
        SeederIds::$pavel  = Str::uuid()->toString();
        SeederIds::$eva    = Str::uuid()->toString();
        SeederIds::$martin = Str::uuid()->toString();
        SeederIds::$tomas  = Str::uuid()->toString();
        SeederIds::$ema    = Str::uuid()->toString();
        SeederIds::$jakub  = Str::uuid()->toString();
        SeederIds::$adam   = Str::uuid()->toString();

        DB::table('users')->insert([
            // Super admin
            [
                'id'                => SeederIds::$michal,
                'first_name'        => 'Michal',
                'last_name'         => 'Kašpařík',
                'nickname'          => null,
                'email'             => 'admin@teameo.cz',
                'phone'             => null,
                'password'          => $password,
                'avatar_path'       => null,
                'address'           => null,
                'birth_date'        => null,
                'is_minor'          => false,
                'can_self_manage'   => false,
                'locale'            => 'cs',
                'notification_preferences' => null,
                'email_verified_at' => $now,
                'remember_token'    => null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            // Trenér 1
            [
                'id'                => SeederIds::$jan,
                'first_name'        => 'Jan',
                'last_name'         => 'Novák',
                'nickname'          => null,
                'email'             => 'trener@teameo.test',
                'phone'             => null,
                'password'          => $password,
                'avatar_path'       => null,
                'address'           => null,
                'birth_date'        => null,
                'is_minor'          => false,
                'can_self_manage'   => false,
                'locale'            => 'cs',
                'notification_preferences' => null,
                'email_verified_at' => $now,
                'remember_token'    => null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            // Trenér 2
            [
                'id'                => SeederIds::$pavel,
                'first_name'        => 'Pavel',
                'last_name'         => 'Dvořák',
                'nickname'          => null,
                'email'             => 'trener2@teameo.test',
                'phone'             => null,
                'password'          => $password,
                'avatar_path'       => null,
                'address'           => null,
                'birth_date'        => null,
                'is_minor'          => false,
                'can_self_manage'   => false,
                'locale'            => 'cs',
                'notification_preferences' => null,
                'email_verified_at' => $now,
                'remember_token'    => null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            // Rodič 1
            [
                'id'                => SeederIds::$eva,
                'first_name'        => 'Eva',
                'last_name'         => 'Svobodová',
                'nickname'          => null,
                'email'             => 'rodic@teameo.test',
                'phone'             => null,
                'password'          => $password,
                'avatar_path'       => null,
                'address'           => null,
                'birth_date'        => null,
                'is_minor'          => false,
                'can_self_manage'   => false,
                'locale'            => 'cs',
                'notification_preferences' => null,
                'email_verified_at' => $now,
                'remember_token'    => null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            // Rodič 2
            [
                'id'                => SeederIds::$martin,
                'first_name'        => 'Martin',
                'last_name'         => 'Procházka',
                'nickname'          => null,
                'email'             => 'rodic2@teameo.test',
                'phone'             => null,
                'password'          => $password,
                'avatar_path'       => null,
                'address'           => null,
                'birth_date'        => null,
                'is_minor'          => false,
                'can_self_manage'   => false,
                'locale'            => 'cs',
                'notification_preferences' => null,
                'email_verified_at' => $now,
                'remember_token'    => null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            // Dítě 1
            [
                'id'                => SeederIds::$tomas,
                'first_name'        => 'Tomáš',
                'last_name'         => 'Svoboda',
                'nickname'          => null,
                'email'             => null,
                'phone'             => null,
                'password'          => null,
                'avatar_path'       => null,
                'address'           => null,
                'birth_date'        => '2017-05-15',
                'is_minor'          => true,
                'can_self_manage'   => false,
                'locale'            => 'cs',
                'notification_preferences' => null,
                'email_verified_at' => null,
                'remember_token'    => null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            // Dítě 2
            [
                'id'                => SeederIds::$ema,
                'first_name'        => 'Ema',
                'last_name'         => 'Procházková',
                'nickname'          => null,
                'email'             => null,
                'phone'             => null,
                'password'          => null,
                'avatar_path'       => null,
                'address'           => null,
                'birth_date'        => '2018-02-20',
                'is_minor'          => true,
                'can_self_manage'   => false,
                'locale'            => 'cs',
                'notification_preferences' => null,
                'email_verified_at' => null,
                'remember_token'    => null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            // Dítě 3
            [
                'id'                => SeederIds::$jakub,
                'first_name'        => 'Jakub',
                'last_name'         => 'Procházka',
                'nickname'          => null,
                'email'             => null,
                'phone'             => null,
                'password'          => null,
                'avatar_path'       => null,
                'address'           => null,
                'birth_date'        => '2016-08-10',
                'is_minor'          => true,
                'can_self_manage'   => false,
                'locale'            => 'cs',
                'notification_preferences' => null,
                'email_verified_at' => null,
                'remember_token'    => null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            // Teenager
            [
                'id'                => SeederIds::$adam,
                'first_name'        => 'Adam',
                'last_name'         => 'Novotný',
                'nickname'          => null,
                'email'             => 'adam@teameo.test',
                'phone'             => null,
                'password'          => $password,
                'avatar_path'       => null,
                'address'           => null,
                'birth_date'        => '2010-11-03',
                'is_minor'          => true,
                'can_self_manage'   => true,
                'locale'            => 'cs',
                'notification_preferences' => null,
                'email_verified_at' => $now,
                'remember_token'    => null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
        ]);

        // Guardian relationships
        DB::table('user_guardians')->insert([
            [
                'id'          => Str::uuid()->toString(),
                'guardian_id' => SeederIds::$eva,
                'child_id'    => SeederIds::$tomas,
                'relationship' => 'mother',
                'is_primary'  => true,
                'created_at'  => $now,
            ],
            [
                'id'          => Str::uuid()->toString(),
                'guardian_id' => SeederIds::$martin,
                'child_id'    => SeederIds::$ema,
                'relationship' => 'father',
                'is_primary'  => true,
                'created_at'  => $now,
            ],
            [
                'id'          => Str::uuid()->toString(),
                'guardian_id' => SeederIds::$martin,
                'child_id'    => SeederIds::$jakub,
                'relationship' => 'father',
                'is_primary'  => true,
                'created_at'  => $now,
            ],
        ]);

        $this->command->info('UserSeeder: 9 uživatelů a 3 vazby rodič-dítě vytvořeno.');
    }
}
