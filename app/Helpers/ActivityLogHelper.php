<?php

namespace App\Helpers;

use Spatie\Activitylog\Models\Activity;

class ActivityLogHelper
{
    private static function createActivityLogs($builder, $event, $attributeColumns, $subjectId, $scopeColumns): void
    {
        $propertyName = ($event === 'deleted') ? 'old' : 'attributes';

        $builder = $builder->withoutGlobalScopes();

        $subjectType = $builder->getModel()->getMorphClass();
        $subjects = $builder->pluck($subjectId)->all();

        $attributeColumns = $attributeColumns ?: $builder->getModel()->getFillable();
        $attributes = $builder->select($attributeColumns)->get()->all();

        $activityLogs = array_map(function ($subject, $attribute) use ($propertyName, $subjectType, $event, $scopeColumns) {
            $scope = array_filter($attribute->toArray(), fn ($column) => in_array($column, $scopeColumns), ARRAY_FILTER_USE_KEY);

            return [
                'log_name' => 'default',
                'description' => $event,
                'subject_type' => $subjectType,
                'event' => $event,
                'subject_id' => $subject,
                'causer_type' => 'App\Models\User',
                'causer_id' => auth()->user()->id,
                'properties' => json_encode([
                    $propertyName => $attribute,
                    ...($scope ? compact('scope') : []),
                ]),
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];
        }, $subjects, $attributes);

        Activity::insert($activityLogs);
    }

    public static function delete($builder, $attributeColumns = [], $subjectId = 'id', $scopeColumns = ['business_unit_id', 'location_id']): void
    {
        self::createActivityLogs($builder, 'deleted', $attributeColumns, $subjectId, $scopeColumns);

        $builder->delete();
    }

    public static function insert($model, $data, $attributeColumns = [], $subjectId = 'id', $scopeColumns = ['business_unit_id', 'location_id']): void
    {
        $uid = uniqid().session()->getId();
        $model->insert(array_map(fn ($datum) => [...$datum, 'uid' => $uid], $data));

        $builder = $model->where('uid', $uid);
        self::createActivityLogs($builder, 'created', $attributeColumns, $subjectId, $scopeColumns);
    }
}
