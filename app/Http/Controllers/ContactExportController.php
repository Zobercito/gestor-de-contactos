<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Services\ContactExporter;
use App\Services\ContactQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactExportController extends Controller
{
    public function __invoke(Request $request, ContactQuery $contacts, ContactExporter $exporter): StreamedResponse
    {
        Gate::authorize('viewAny', Contact::class);

        $filters = $request->only([
            'segment', 'type', 'gender', 'city', 'province', 'country',
            'business_type', 'source', 'birthday_month',
        ]);

        $query = $contacts->build(
            array_filter($filters, fn ($value) => $value !== null && $value !== ''),
            (string) $request->query('search', ''),
            (string) $request->query('sort', 'name'),
            (string) $request->query('direction', 'asc'),
        );

        $suffix = $request->filled('segment')
            ? 'segmento-'.$request->query('segment')
            : ($request->filled('search') ? 'busqueda' : '');

        return response()->streamDownload(function () use ($query, $exporter) {
            $output = fopen('php://output', 'wb');
            $exporter->write($query, $output);
            fclose($output);
        }, $exporter->filename($suffix), [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
