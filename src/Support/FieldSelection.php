<?php

namespace Astek\SearchableFields\Support;

/**
 * Pure helpers for the selected-field list. No framework, no I/O, so the merge
 * rules that decide what an admin's submit actually changes can be unit tested.
 */
final class FieldSelection
{
    /**
     * Drop anything that is not a usable attribute code and remove duplicates,
     * keeping the first occurrence and its order.
     *
     * @param  array<int|string, mixed>  $codes
     * @return array<int, string>
     */
    public static function normalize(array $codes): array
    {
        $clean = [];

        foreach ($codes as $code) {
            if (! is_string($code)) {
                continue;
            }

            $code = trim($code);

            if ($code === '' || in_array($code, $clean, true)) {
                continue;
            }

            $clean[] = $code;
        }

        return $clean;
    }

    /**
     * Merge one submitted page of checkboxes into the stored selection.
     *
     * The picker is paginated, so a plain "store what was ticked" would silently
     * drop every field selected on another page. Only codes that were rendered on
     * the submitted page ($visible) may be removed, and a ticked code that was not
     * on that page is ignored rather than trusted.
     *
     * @param  array<int|string, mixed>  $stored
     * @param  array<int|string, mixed>  $visible
     * @param  array<int|string, mixed>  $checked
     * @return array<int, string>
     */
    public static function merge(array $stored, array $visible, array $checked): array
    {
        $stored = self::normalize($stored);
        $visible = self::normalize($visible);
        $checked = self::normalize($checked);

        $kept = array_diff($stored, $visible);

        $added = array_intersect($checked, $visible);

        return self::normalize(array_merge($kept, $added));
    }
}
