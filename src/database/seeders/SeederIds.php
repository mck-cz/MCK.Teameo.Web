<?php

namespace Database\Seeders;

/**
 * Shared UUID registry for cross-seeder references.
 * All IDs are populated by the respective seeders.
 */
class SeederIds
{
    // Users
    public static string $michal;
    public static string $jan;
    public static string $pavel;
    public static string $eva;
    public static string $martin;
    public static string $tomas;
    public static string $ema;
    public static string $jakub;
    public static string $adam;

    // Club
    public static string $club;

    // Season
    public static string $season;

    // Teams
    public static string $teamU9;
    public static string $teamU12;

    // Team memberships (needed for attendances/nominations)
    public static string $tmJanU9;
    public static string $tmPavelU9;
    public static string $tmTomasU9;
    public static string $tmEmaU9;
    public static string $tmJanU12;
    public static string $tmJakubU12;
    public static string $tmAdamU12;

    // Venues
    public static string $venueHlavni;
    public static string $venueHala;
    public static string $venueUmelka;
}
