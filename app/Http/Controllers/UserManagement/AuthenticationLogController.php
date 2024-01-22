<?php

namespace App\Http\Controllers\UserManagement;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserManagement\AuthenticationLog;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class AuthenticationLogController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['role:super-admin']);
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Renderable|Collection
    {
        if ($request->ajax()) {
            return $this->tableData($request->input('type'));
        }

        return view('pages.user_management.authentication_logs.index');
    }

    private function tableData(string $type): Collection
    {
        $query = (match ($type) {
            'login' => AuthenticationLog::query()
                ->whereIn('log_name', ['login', 'failed']),
            'register' => Activity::query()
                ->joinSub(User::select('id', 'username'), 'users', function ($join) {
                    $join->on('users.id', 'activity_log.causer_id');
                    $join->orOn('users.id', 'activity_log.subject_id')->whereNull('activity_log.causer_id');
                })
                ->where([
                    'subject_type' => 'App\Models\User',
                    'event' => 'created',
                ])
                ->select('activity_log.*', 'users.username', DB::raw("'' AS tag")),
            'edit-password' => Activity::query()
                ->joinSub(User::select('id', 'username'), 'users', function ($join) {
                    $join->on('users.id', 'activity_log.causer_id');
                })
                ->where([
                    'subject_type' => 'App\Models\User',
                    'event' => 'updated',
                ])
                ->whereNotNull('properties->attributes->password')
                ->select('activity_log.*', 'users.username', DB::raw("'' AS tag")),
            default => null,
        })
            ->orderByDesc('created_at')
            ->get();

        return match ($type) {
            'login' => $query
                ->setVisible(['created_at', 'username', 'tag', 'event_frmt', 'properties_frmt'])
                ->map(function ($datum) {
                    $datum->username = $datum->properties['username'] ?? '';
                    $datum->tag = $datum->properties['tag'] ?? '';
                    $datum->event_frmt = LogHelper::mapAuthLogNameToAction($datum->log_name);
                    $datum->properties_frmt = isset($datum->properties['error']) ? '<pre><code>'.json_encode($datum->properties['error']).'</code></pre>' : '';

                    return $datum;
                }),
            'register', 'edit-password' => $query
                ->setVisible(['created_at_frmt', 'subject_id', 'username', 'tag'])
                ->map(function ($datum) {
                    $datum->created_at_frmt = $datum->created_at->format('d-m-Y H.i.s');

                    return $datum;
                }),
            default => null,
        };
    }
}
