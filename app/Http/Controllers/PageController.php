<?php

namespace App\Http\Controllers;

use App\Exports\DataInitiationExport;
use App\Helpers\LogHelper;
use App\Imports\DataInitiationImport;
use DB;
use Excel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Str;

class PageController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['role:super-admin'])->only(['dataInitiationIndex', 'dataInitiationDownloadTemplate', 'dataInitiationImport']);
    }

    public function dashboard(): View
    {
        return view('pages.dashboard');
    }

    public function editUserPassword(): View
    {
        return view('pages.edit_user_password');
    }

    public function dataInitiationIndex(): View
    {
        $indexedModels = LogHelper::getAllModels(false);
        $models = array_combine($indexedModels, $indexedModels);

        return view('pages.data_initiation', compact('models'));
    }

    public function dataInitiationDownloadTemplate(Request $request)
    {
        return Excel::download(new DataInitiationExport($request->input('model')), Str::random().'.xlsx');
    }

    public function dataInitiationImport(Request $request): RedirectResponse
    {
        $model = $request->input('model');

        try {
            $import = new DataInitiationImport($model);
            $import->import($request->file('file'));

            session()->flash('success', 'Data berhasil diimpor!');
        } catch (\Exception $e) {
            report($e);

            $tableName = (new $model)->getTable();
            DB::statement("ALTER TABLE $tableName AUTO_INCREMENT = 1;");
            DB::statement('ALTER TABLE activity_log AUTO_INCREMENT = 1;');

            session()->flash('fail', 'Data tidak berhasil diimpor!');
            session()->flash('failure_message', $e->getMessage());
        } finally {
            return redirect($_SERVER['HTTP_REFERER']);
        }
    }
}
