<?php

use App\Models\Option;
use Illuminate\Support\Facades\Schema;

if (! function_exists('global_config')) {
    function global_config($name, $default)
    {
        try {
            if (Schema::hasTable('options')) {
                return Option::where([
                    'name' => $name,
                    'status' => Option::STATUS_ACTIVE,
                ])->value('value');
            }

            return $default;
        } catch (\Error $error) {
            return $default;
        }
    }
}

if (! function_exists('decimal_number_format')) {
    function decimal_number_format(int|float|string|null $num, int $decimals = 2, ?string $decimal_separator = ',', ?string $thousands_separator = '.'): string
    {
        if (is_null($num)) {
            return '';
        }

        return rtrim(rtrim(number_format((float) $num, $decimals, $decimal_separator, $thousands_separator), '0'), $decimal_separator);
    }
}
