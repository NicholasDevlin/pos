<?php

namespace App\Http\Controllers;

use App\Exports\DataInitiationExport;
use App\Helpers\LogHelper;
use App\Imports\DataInitiationImport;
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
        try {
            $import = new DataInitiationImport($request->input('model'));
            $import->import($request->file('file'));

            if ($import->errors()->isNotEmpty()) {
                session()->flash('fail', 'Data tidak berhasil diimpor!');
                session()->flash('failures', $import->errors());
            } else {
                session()->flash('success', 'Data berhasil diimpor!');
            }
        } catch (\Exception $e) {
            report($e);

            session()->flash('fail', 'Data tidak berhasil diimpor!');
        } finally {
            return redirect($_SERVER['HTTP_REFERER']);
        }
    }
}
