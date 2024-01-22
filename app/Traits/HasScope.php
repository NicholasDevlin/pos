<?php

namespace App\Traits;

use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpFoundation\Response;

trait HasScope
{
    private static array $scopes = [];

    public static function bootHasScope()
    {
        static::addGlobalScope('scopes', function (Builder $builder) {
            $tableName = with(new static)->getTable();
            $routeName = self::mapTableNameToRouteName($tableName);

            foreach (array_keys(self::$scopes[$routeName] ?? []) as $permission) {
                if (Auth::user()->can("{$routeName}.show.{$permission}")) {
                    return self::getScopeFilter($builder, self::$scopes[$routeName][$permission], Auth::user());
                }
            }

            abort(Response::HTTP_FORBIDDEN);
        });
    }

    private static function getScopeFilter($builder, $action, $user = null)
    {
        return match ($action) {
            'SHOW ALL LOCATIONS', 'SHOW ALL DIVISIONS', 'SHOW ALL LOCATIONS-DIVISIONS' => $builder,
            'SHOW SCOPE LOCATIONS' => $builder->whereIn('location_id', array_keys(LogHelper::getLocations($user))),
            'SHOW SCOPE DIVISIONS' => $builder->whereIn('division_id', array_keys(LogHelper::getDivisions($user))),
            'SHOW SCOPE LOCATIONS-DIVISIONS' => $builder
                ->whereIn(DB::raw("CONCAT(location_id, '-', division_id)"), array_keys(LogHelper::getLocationsDivisions($user))),
            'SHOW OWN LOCATIONS', 'SHOW OWN DIVISIONS', 'SHOW OWN LOCATIONS-DIVISIONS' => $builder
                ->whereIn(with(new static)->getTable().'.id', function ($query) use ($user, $builder) {
                    $query->select('subject_id')
                        ->from('activity_log')
                        ->where([
                            'subject_type' => get_class($builder->getModel()),
                            'causer_id' => $user->id,
                            'description' => 'created',
                        ]);
                }),
            default => abort(Response::HTTP_FORBIDDEN),
        };
    }

    private static function mapTableNameToRouteName($tableName): string
    {
        return [][$tableName];
    }

    public function getScopes($module = null): array
    {
        $routeName = $module ?: explode('.', Request::route()->getName())[0];

        foreach (array_keys(self::$scopes[$routeName] ?? []) as $permission) {
            if (Auth::user()->can("{$routeName}.show.{$permission}")) {
                return $this->getScopeData(self::$scopes[$routeName][$permission], Auth::user());
            }
        }

        return [];
    }

    private function getScopeData($action, $user = null): array
    {
        return match ($action) {
            'SHOW ALL LOCATIONS' => LogHelper::getLocations(),
            'SHOW SCOPE LOCATIONS', 'SHOW OWN LOCATIONS' => LogHelper::getLocations($user),
            'SHOW ALL DIVISIONS' => LogHelper::getDivisions(),
            'SHOW SCOPE DIVISIONS', 'SHOW OWN DIVISIONS' => LogHelper::getDivisions($user),
            'SHOW ALL LOCATIONS-DIVISIONS' => LogHelper::getLocationsDivisions(),
            'SHOW SCOPE LOCATIONS-DIVISIONS', 'SHOW OWN LOCATIONS-DIVISIONS' => LogHelper::getLocationsDivisions($user),
            default => [],
        };
    }
}
