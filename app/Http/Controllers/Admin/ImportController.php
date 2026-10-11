<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ImportTemplate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportRequest;
use App\Imports\SpreadsheetImport;
use App\Imports\StudentImport;
use App\Imports\TeacherImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ImportController extends Controller
{
    /** Jenis data yang bisa diimpor, sesuai segmen URL /admin/import/{type}. */
    public const IMPORTS = [
        'siswa' => StudentImport::class,
        'guru' => TeacherImport::class,
    ];

    public function create(string $type): View
    {
        return view('admin.import', [
            'type' => $type,
            'import' => $this->resolve($type),
        ]);
    }

    public function store(ImportRequest $request, string $type): RedirectResponse
    {
        $file = $request->file('file');

        try {
            $result = $this->resolve($type)->import($file->getRealPath(), $file->getClientOriginalExtension());
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['file' => 'Berkas tidak bisa dibaca. Pastikan isinya mengikuti template dan berkas tidak rusak, lalu coba lagi.']);
        }

        return back()->with('import_result', $result->toArray() + ['file' => $file->getClientOriginalName()]);
    }

    public function template(string $type, ImportTemplate $template): BinaryFileResponse
    {
        $path = $template->build($this->resolve($type));

        return response()
            ->download($path, "template-impor-{$type}.xlsx")
            ->deleteFileAfterSend();
    }

    private function resolve(string $type): SpreadsheetImport
    {
        abort_unless(isset(self::IMPORTS[$type]), 404);

        return app(self::IMPORTS[$type]);
    }
}
