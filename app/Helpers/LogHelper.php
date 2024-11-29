<?php

namespace App\Helpers;

use App\Models\UserManagement\BusinessUnit;
use App\Models\UserManagement\Location;
use App\Models\UserManagement\ModelHasScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class LogHelper
{
    const modelToLogName = [
        "App\Models\User" => 'Pengguna',
        "App\Models\UserManagement\BusinessUnit" => 'Unit Bisnis',
        "App\Models\UserManagement\Location" => 'Lokasi',
        "App\Models\Option" => 'Konfigurasi',
    ];

    const exclusions = [
        'App\Models\UserManagement\AuthenticationLog',
        'App\Models\UserManagement\ModelHasScope',
    ];

    const eventToAction = [
        'created' => '<span class="badge badge-pill badge-success">CREATE</span>',
        'updated' => '<span class="badge badge-pill badge-primary">UPDATE</span>',
        'deleted' => '<span class="badge badge-pill badge-danger">DELETE</span>',
    ];

    const authLogNameToAction = [
        'login' => '<span class="badge badge-pill badge-success">LOGIN</span>',
        'failed' => '<span class="badge badge-pill badge-danger">FAILED</span>',
    ];

    const columnNameToProperty = [
        'name' => 'Nama',
        'value' => 'Nilai',
        'status' => 'Status',
        'notes' => 'Keterangan',
        'username' => 'Username',
        'password' => 'Password',
        'real_name' => 'Nama Asli',
        'short_name' => 'Nama Pendek',
        'code_name' => 'Kode Nama',
    ];

    public static function getAllModels($mapModelToLogName = true): array
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);
        $models = [];

        foreach ((array) data_get($composer, 'autoload.psr-4') as $namespace => $path) {
            $currentModels = collect(File::allFiles(base_path($path)))
                ->map(function ($item) use ($namespace) {
                    $path = $item->getRelativePathName();

                    return sprintf('%s%s',
                        $namespace,
                        strtr(substr($path, 0, strrpos($path, '.')), '/', '\\'),
                    );
                })
                ->filter(function ($class) {
                    if (! class_exists($class) || in_array($class, static::exclusions)) {
                        return false;
                    }

                    $reflection = new \ReflectionClass($class);

                    return $reflection->isSubclassOf(\Illuminate\Database\Eloquent\Model::class) && ! $reflection->isAbstract();
                });

            if ($mapModelToLogName) {
                $currentModels = $currentModels->map(fn ($model) => static::mapModelToLogName($model));
            }

            $models = array_merge($currentModels->all(), $models);
        }

        return $models;
    }

    public static function mapModelToLogName($model): string
    {
        return static::modelToLogName[$model] ?? $model;
    }

    public static function mapLogNameToModel($logName): ?string
    {
        return array_flip(static::modelToLogName)[$logName] ?? null;
    }

    public static function mapEventToAction($model): string
    {
        return static::eventToAction[$model] ?? $model;
    }

    public static function mapAuthLogNameToAction($model): string
    {
        return static::authLogNameToAction[$model] ?? $model;
    }

    public static function mapColumnNameToProperty($columnName): string
    {
        return isset(static::columnNameToProperty[$columnName]) ? '<strong class="text-info">'.static::columnNameToProperty[$columnName].'</strong>' : $columnName;
    }

    public static function getLocations($user = null): array
    {
        if ($user) {
            return ModelHasScope::join('locations', 'locations.id', 'model_has_scopes.location_id')
                ->where([
                    'model_has_scopes.model_id' => $user->id,
                    'locations.status' => Location::STATUS_ACTIVE,
                ])
                ->orderBy('model_has_scopes.location_id')
                ->pluck('locations.name', 'model_has_scopes.location_id')
                ->all();
        }

        return Location::where('status', Location::STATUS_ACTIVE)->pluck('name', 'id')->all();
    }

    public static function getBusinessUnits($user = null): array
    {
        if ($user) {
            return ModelHasScope::join('business_units', 'business_units.id', 'model_has_scopes.business_unit_id')
                ->where([
                    'model_has_scopes.model_id' => $user->id,
                    'business_units.status' => BusinessUnit::STATUS_ACTIVE,
                ])
                ->orderBy('model_has_scopes.business_unit_id')
                ->pluck('business_units.name', 'model_has_scopes.business_unit_id')
                ->all();
        }

        return BusinessUnit::where('status', BusinessUnit::STATUS_ACTIVE)->pluck('name', 'id')->all();
    }

    public static function getLocationsBusinessUnits($user = null): array
    {
        if ($user) {
            return ModelHasScope::join('locations', 'locations.id', 'model_has_scopes.location_id')
                ->join('business_units', 'business_units.id', 'model_has_scopes.business_unit_id')
                ->where([
                    'model_has_scopes.model_id' => $user->id,
                    'locations.status' => Location::STATUS_ACTIVE,
                    'business_units.status' => BusinessUnit::STATUS_ACTIVE,
                ])
                ->select(DB::raw("CONCAT(locations.name, ' | ', business_units.name) AS 'value'"), DB::raw("CONCAT(model_has_scopes.location_id, '-', model_has_scopes.business_unit_id) AS 'key'"))
                ->pluck('value', 'key')
                ->all();
        }

        $locations = self::getLocations();
        $businessUnits = self::getBusinessUnits();

        $data = [];

        foreach ($locations as $locationKey => $location) {
            foreach ($businessUnits as $businessUnitKey => $businessUnit) {
                $data["$locationKey-$businessUnitKey"] = "$location | $businessUnit";
            }
        }

        return $data;
    }
}
