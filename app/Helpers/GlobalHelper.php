<?php

namespace App\Http\Helpers;

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
