<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;

class DateAndTime extends Helper
{
	/*
	* Convert TImestamp to human time.
	*/
	public static function TimeElapsed($datetime) {
		return Carbon::parse($datetime)->diffForHumans();
	}

	/*
   * User to timezone
   */
    public static function UserTZ($datetime): string
    {
        return Carbon::make($datetime)->tz(auth()->user()->timezone)->format('Y-m-d h:i A');
    }

	/*
	* Convert Timestamp to age.
	*/
	public static function Age($datetime) {
		return Carbon::parse($datetime)->age;
	}

	/*
	* Difference between two years
	*/
	public static function DiffYears($from, $to = null) {
		$from = Carbon::parse($from);
		$to = ($to !== null ? Carbon::parse($to) : Carbon::now());

		return $from
			->diffAsCarbonInterval($to)
			->totalYears;
	}
}
