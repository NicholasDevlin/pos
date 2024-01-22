<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['permission:logs.show.all|logs.show.scope|logs.show.own']);
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Renderable|Collection
    {
        if ($request->ajax()) {
            return $this->tableData();
        }

        return view('pages.activity_logs');
    }

    private function tableData(): Collection
    {
        return $this->getActivityScopes(
            Activity::query()
                ->joinSub(User::select('id', 'username'), 'users', function ($join) {
                    $join->on('users.id', 'activity_log.causer_id');
                })
                ->orderByDesc('activity_log.created_at')
                ->select('activity_log.*', 'users.username')
        )
            ->get()
            ->setVisible(['subject_type_frmt', 'event_frmt', 'subject_id', 'username', 'changes_frmt', 'created_at_frmt'])
            ->map(function ($datum) {
                $datum->subject_type_frmt = LogHelper::mapModelToLogName($datum->subject_type);
                $datum->event_frmt = LogHelper::mapEventToAction($datum->event);
                $datum->changes_frmt = $this->showLogChanges($datum);
                $datum->created_at_frmt = $datum->created_at->format('d-m-Y H.i.s');

                return $datum;
            });
    }

    private function getActivityScopes(Builder|Activity $activities)
    {
        if (auth()->user()->can('logs.show.all')) {
            return $activities;
        } elseif (auth()->user()->can('logs.show.scope')) {
            return $activities
                ->whereRaw(<<<'WHERE'
CASE
    WHEN JSON_VALUE(properties, '$.scope.location_id') IS NOT NULL AND JSON_VALUE(properties, '$.scope.division_id') IS NOT NULL THEN
        (JSON_VALUE(properties, '$.scope.location_id'), JSON_VALUE(properties, '$.scope.division_id')) IN
        (SELECT location_id, division_id FROM model_has_scopes WHERE model_id = ?)
    WHEN JSON_VALUE(properties, '$.scope.location_id') IS NOT NULL THEN
        JSON_VALUE(properties, '$.scope.location_id') IN
        (SELECT DISTINCT location_id FROM model_has_scopes WHERE model_id = ?)
    WHEN JSON_VALUE(properties, '$.scope.division_id') IS NOT NULL THEN
        JSON_VALUE(properties, '$.scope.division_id') IN
        (SELECT DISTINCT division_id FROM model_has_scopes WHERE model_id = ?)
    ELSE FALSE
END
WHERE, array_fill(0, 3, auth()->user()->id))
                ->whereIn('subject_type', function ($query) {
                    $query->select('model_type')
                        ->from('role_has_log_scopes')
                        ->whereIn('role_id', auth()->user()->roles->pluck('id')->all());
                });
        } elseif (auth()->user()->can('logs.show.own')) {
            return $activities
                ->where('causer_id', auth()->user()->id)
                ->whereIn('subject_type', function ($query) {
                    $query->select('model_type')
                        ->from('role_has_log_scopes')
                        ->whereIn('role_id', auth()->user()->roles->pluck('id')->all());
                });
        }

        return 'Unauthorized';
    }

    private function showLogChanges(Activity $model): string
    {
        $table = [
            'headers' => ['<thead><tr>', '<th style="width: 140px"></th>', '</tr></thead>'],
            'body' => [],
        ];

        $changes = $model->changes->all();
        $changesFormatted = [
            'properties' => [],
            'old' => [],
            'attributes' => [],
        ];

        foreach (['Lama' => 'old', 'Baru' => 'attributes'] as $propertyIndex => $property) {
            if (! isset($changes[$property])) {
                continue;
            }

            $changesProperty = $changes[$property];
            $changesFormatted[$property] = array_map(fn ($p) => is_array($p) ? '<td><pre>'.print_r($p, true).'</pre></td>' : "<td>$p</td>", array_values($changesProperty));

            array_splice($table['headers'], count($table['headers']) - 1, 0, ["<th style='width: 140px'>$propertyIndex</th>"]);

            if (! count($changesFormatted['properties'])) {
                $changesFormatted['properties'] = array_map(fn ($property) => LogHelper::mapColumnNameToProperty($property), array_keys($changesProperty));
            }
        }

        foreach ($changesFormatted['properties'] as $propertyIndex => $property) {
            $body = ['<tr>', "<td>$property</td>", '</tr>'];
            array_splice($body, count($body) - 1, 0, [
                count($changesFormatted['old']) ? mb_strimwidth($changesFormatted['old'][$propertyIndex], 0, 20, '...') : '',
                count($changesFormatted['attributes']) ? mb_strimwidth($changesFormatted['attributes'][$propertyIndex], 0, 20, '...') : '',
            ]);

            $table['body'][] = implode('', array_filter($body));
        }

        return '<table class="table table-sm table-logs" style="width: 420px">'.implode('', $table['headers']).implode('', $table['body']).'</table>';
    }
}
