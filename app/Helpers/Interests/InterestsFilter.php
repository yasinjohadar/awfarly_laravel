<?php

namespace App\Helpers\Interests;

use App\Models\Interests\Interest;

class InterestsFilter
{
    /**
     * Whether the client asked to bypass interest filtering.
     * Missing / null / empty → false (apply interests by default).
     */
    public static function wantsAllInterests(array $data): bool
    {
        if (!array_key_exists('isGetAllInterests', $data) || $data['isGetAllInterests'] === null || $data['isGetAllInterests'] === '') {
            return false;
        }

        $value = $data['isGetAllInterests'];

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'on', 'yes'], true);
    }

    /**
     * Expand parent interest ids to include their direct children.
     *
     * @param array $interestIds
     * @return int[]
     */
    public static function expandInterestIds(array $interestIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map(static function ($id) {
            return (int) $id;
        }, $interestIds))));

        if (empty($ids)) {
            return [];
        }

        $children = Interest::whereIn('parent_interest_id', $ids)
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->toArray();

        return array_values(array_unique(array_merge($ids, $children)));
    }

    /**
     * @param mixed $user
     * @return int[]
     */
    public static function preferredInterestIds($user): array
    {
        if (!$user || !method_exists($user, 'interests')) {
            return [];
        }

        return self::expandInterestIds(
            $user->interests()->pluck('interest_id')->toArray()
        );
    }

    /**
     * Apply interest matching (with children) when no explicit interestId and not "get all".
     * Matches at the advertiser level: shows content from advertisers whose own selected
     * interests overlap with the user's selected interests.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     * @param mixed $user
     */
    public static function applyFeedInterestFilter($query, array $data, $user, string $advertiserTable = 'advertisers_users')
    {
        if (!empty($data['interestId'])) {
            $ids = self::expandInterestIds([(int) $data['interestId']]);

            return $query->whereIn("{$advertiserTable}.id", function ($q) use ($ids) {
                $q->select('advertiser_id')->from('advertiser_interests')->whereIn('interest_id', $ids);
            });
        }

        if (self::wantsAllInterests($data)) {
            return $query;
        }

        $ids = self::preferredInterestIds($user);

        if (empty($ids)) {
            return $query;
        }

        return $query->whereIn("{$advertiserTable}.id", function ($q) use ($ids) {
            $q->select('advertiser_id')->from('advertiser_interests')->whereIn('interest_id', $ids);
        });
    }
}
