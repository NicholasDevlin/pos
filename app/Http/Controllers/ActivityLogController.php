<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    const fixed_date_options = [
        '1' => 'kemarin',
        '3' => 'tiga hari yang lalu',
        '7' => 'seminggu yang lalu',
        '14' => 'dua minggu yang lalu',
        '31' => 'sebulan yang lalu',
    ];

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
    public function __invoke(Request $request): View|Collection
    {
        if ($request->ajax()) {
            $this->prepareRequest($request);

            return $this->tableData($request->only(['start_date', 'end_date']));
        }

        return view('pages.activity_logs', [
            'fixedDateOptions' => self::fixed_date_options,
        ]);
    }

    private function prepareRequest(Request $request)
    {
        $dateOption = $request->input('date_option');

        switch ($dateOption) {
            case 'fixed':
                $date = $request->input('fixed_date');
                if ($date) {
                    $start_date = Carbon::now()->subDays($date)->startOfDay()->format('Y-m-d H:i:s');
                    $end_date = Carbon::now()->endOfDay()->format('Y-m-d H:i:s');

                    $request->merge(compact('start_date', 'end_date'));
                }

                break;
            case 'relative':
                $date = $request->input('relative_date');
                if ($date) {
                    [$start_date, $end_date] = explode(' - ', $date);
                    $start_date = Carbon::createFromFormat('d-m-Y', $start_date)->startOfDay()->format('Y-m-d H:i:s');
                    $end_date = Carbon::createFromFormat('d-m-Y', $end_date)->endOfDay()->format('Y-m-d H:i:s');

                    $request->merge(compact('start_date', 'end_date'));
                }

                break;
        }

        $request->validate([
            'date_option' => ['required', Rule::in(['fixed', 'relative'])],
            'fixed_date' => ['required_if:date_option,fixed', Rule::in(array_keys(self::fixed_date_options))],
            'relative_date' => ['required_if:date_option,relative', 'string'],
            'start_date' => ['required', 'date', 'date_format:Y-m-d H:i:s'],
            'end_date' => ['required', 'date', 'date_format:Y-m-d H:i:s'],
        ]);
    }

    private function tableData($range): Collection
    {
        ['start_date' => $startDate, 'end_date' => $endDate] = $range;

        return $this->getActivityScopes(
            Activity::query()
                ->joinSub(User::select('id', 'username'), 'users', function ($join) {
                    $join->on('users.id', 'activity_log.causer_id');
                })
                ->orderByDesc('activity_log.created_at')
                ->select('activity_log.*', 'users.username')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
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
    ELSE TRUE
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
