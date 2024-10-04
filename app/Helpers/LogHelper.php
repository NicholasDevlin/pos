<?php

namespace App\Helpers;

use App\Models\UserManagement\Division;
use App\Models\UserManagement\Location;
use App\Models\UserManagement\ModelHasScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class LogHelper
{
    const modelToLogName = [
        "App\Models\User" => 'Pengguna',
        "App\Models\UserManagement\Division" => 'Divisi',
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

    public static function getAllModels(): array
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);
        $models = [];

        foreach ((array) data_get($composer, 'autoload.psr-4') as $namespace => $path) {
            $models = array_merge(collect(File::allFiles(base_path($path)))
                ->map(function ($item) use ($namespace) {
                    $path = $item->getRelativePathName();

                    return sprintf('%s%s',
                        $namespace,
                        strtr(substr($path, 0, strrpos($path, '.')), '/', '\\'));
                })
                ->filter(function ($class) {
                    $valid = false;

                    if (class_exists($class) && ! in_array($class, static::exclusions)) {
                        $reflection = new \ReflectionClass($class);
                        $valid = $reflection->isSubclassOf(\Illuminate\Database\Eloquent\Model::class) &&
                            ! $reflection->isAbstract();
                    }

                    return $valid;
                })
                ->map(fn ($model) => self::mapModelToLogName($model))
                ->all(), $models);
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

    public static function getDivisions($user = null): array
    {
        if ($user) {
            return ModelHasScope::join('divisions', 'divisions.id', 'model_has_scopes.division_id')
                ->where([
                    'model_has_scopes.model_id' => $user->id,
                    'divisions.status' => Division::STATUS_ACTIVE,
                ])
                ->orderBy('model_has_scopes.division_id')
                ->pluck('divisions.name', 'model_has_scopes.division_id')
                ->all();
        }

        return Division::where('status', Division::STATUS_ACTIVE)->pluck('name', 'id')->all();
    }

    public static function getLocationsDivisions($user = null): array
    {
        if ($user) {
            return ModelHasScope::join('locations', 'locations.id', 'model_has_scopes.location_id')
                ->join('divisions', 'divisions.id', 'model_has_scopes.division_id')
                ->where([
                    'model_has_scopes.model_id' => $user->id,
                    'locations.status' => Location::STATUS_ACTIVE,
                    'divisions.status' => Division::STATUS_ACTIVE,
                ])
                ->select(DB::raw("CONCAT(locations.name, ' | ', divisions.name) AS 'value'"), DB::raw("CONCAT(model_has_scopes.location_id, '-', model_has_scopes.division_id) AS 'key'"))
                ->pluck('value', 'key')
                ->all();
        }

        $locations = self::getLocations();
        $divisions = self::getDivisions();

        $data = [];

        foreach ($locations as $locationKey => $location) {
            foreach ($divisions as $divisionKey => $division) {
                $data["$locationKey-$divisionKey"] = "$location | $division";
            }
        }

        return $data;
    }
}
