<?php

namespace App\Support;

use App\Models\Schedule;
use Carbon\CarbonInterface;

/**
 * Hari dan jam sekolah. Nama hari mengikuti enum `schedules.day_of_week`.
 */
class SchoolDay
{
    private const DAYS = [
        CarbonInterface::SUNDAY => 'Minggu',
        CarbonInterface::MONDAY => 'Senin',
        CarbonInterface::TUESDAY => 'Selasa',
        CarbonInterface::WEDNESDAY => 'Rabu',
        CarbonInterface::THURSDAY => 'Kamis',
        CarbonInterface::FRIDAY => 'Jumat',
        CarbonInterface::SATURDAY => 'Sabtu',
    ];

    /** "Senin", "Selasa", ... untuk tanggal yang diberikan. */
    public static function nameOf(CarbonInterface $date): string
    {
        return self::DAYS[$date->dayOfWeek];
    }

    /** Apakah jadwal ini berlangsung pada hari tanggal tersebut. */
    public static function isScheduledOn(Schedule $schedule, CarbonInterface $date): bool
    {
        return $schedule->day_of_week === self::nameOf($date);
    }

    /** Apakah waktu tersebut berada di dalam jam pelajaran jadwal ini. */
    public static function isDuringLesson(Schedule $schedule, CarbonInterface $at): bool
    {
        if (! self::isScheduledOn($schedule, $at)) {
            return false;
        }

        $time = $at->format('H:i:s');

        return $time >= self::normalize($schedule->start_time)
            && $time <= self::normalize($schedule->end_time);
    }

    /** "07:30" atau "07:30:00" menjadi "07:30:00" agar bisa dibandingkan. */
    private static function normalize(string $time): string
    {
        return strlen($time) === 5 ? "{$time}:00" : $time;
    }
}
