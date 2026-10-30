<?php

use App\Jobs\ProcessContactImport;
use App\Models\Contact;
use App\Models\Import;
use App\Models\User;
use App\Services\ContactExporter;
use App\Services\Csv\ContactImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

function makeCsvUpload(string $content, string $name = 'contactos.csv'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, $content);
}

beforeEach(function () {
    Storage::fake('local');
    $this->actor = User::factory()->editor()->create();
});

it('imports a CSV with mapping, normalization and duplicates', function () {
    Contact::factory()->create(['email' => 'existente@example.com', 'phone' => null]);

    $csv = <<<'CSV'
    Origen,Nombre y Apellido,Correo,Teléfono celular,Empresa
    Web,Ana Pérez,ANA@EXAMPLE.COM,+507 6000-1111,Hotel Sol
    Web,Ana Repetida,ana@example.com,,
    Web,Ya Existe,existente@example.com,,
    Web,Sin Canal,,,
    Feria,Beto Ruiz,beto@example.com,+507 6000-2222,Grupo Mar
    CSV;

    $service = app(ContactImportService::class);
    $import = $service->createImport($this->actor, makeCsvUpload($csv));
    $inspection = $service->inspect($import);

    expect($inspection['mapping']['name'])->toBe('Nombre y Apellido')
        ->and($inspection['mapping']['email'])->toBe('Correo')
        ->and($inspection['mapping']['phone'])->toBe('Teléfono celular');

    $import->update(['mapping' => $inspection['mapping']]);

    $analysis = $service->analyze($import, $inspection['mapping']);
    expect($analysis['counts']['ready'])->toBe(2)
        ->and($analysis['counts']['duplicates'])->toBe(2)
        ->and($analysis['counts']['errors'])->toBe(1);

    $service->process($import);

    $import->refresh();
    expect($import->status->value)->toBe('completed')
        ->and($import->created_count)->toBe(2)
        ->and($import->duplicate_count)->toBe(2)
        ->and($import->error_count)->toBe(1);

    $ana = Contact::where('email', 'ana@example.com')->firstOrFail();
    expect($ana->phone)->toBe('+50760001111')
        ->and($ana->company)->toBe('Hotel Sol')
        ->and($ana->source)->toBe('Web');

    expect($import->errors)->toHaveCount(3);
});

it('detects semicolon-delimited files', function () {
    $csv = "Nombre;Correo\nAna;ana@example.com\n";
    $service = app(ContactImportService::class);

    $import = $service->createImport($this->actor, makeCsvUpload($csv));
    $inspection = $service->inspect($import);

    expect($inspection['delimiter'])->toBe(';')
        ->and($inspection['mapping']['name'])->toBe('Nombre');
});

it('queues large imports instead of processing synchronously', function () {
    Queue::fake();
    config()->set('contacts.import.queue_threshold', 1);
    config()->set('demo.enabled', false);

    $csv = "Nombre,Correo\nAna,ana@example.com\nBeto,beto@example.com\n";
    $import = Import::factory()->create([
        'user_id' => $this->actor->id,
        'status' => 'pending',
        'total_rows' => 5,
    ]);

    $queued = app(ContactImportService::class)->queueOrProcess($import);

    expect($queued)->toBeTrue();
    Queue::assertPushed(ProcessContactImport::class);
    expect(Contact::count())->toBe(0);
});

it('rejects files without a name column', function () {
    $csv = "Correo\nana@example.com\n";
    $service = app(ContactImportService::class);
    $import = $service->createImport($this->actor, makeCsvUpload($csv));
    $inspection = $service->inspect($import);

    expect($inspection['mapping']['name'])->toBeNull();

    $import->update(['mapping' => $inspection['mapping']]);

    expect(fn () => $service->process($import))
        ->toThrow(RuntimeException::class, 'nombre');
});

it('exports contacts as UTF-8 CSV with formula protection', function () {
    Contact::factory()->create([
        'name' => '=SUM(1,1)',
        'email' => 'formula@example.com',
        'phone' => '+50760009999',
    ]);

    $output = fopen('php://temp', 'r+');
    $exporter = app(ContactExporter::class);
    $count = $exporter->write(Contact::query()->where('email', 'formula@example.com'), $output);
    rewind($output);
    $content = stream_get_contents($output);
    fclose($output);

    expect($count)->toBe(1)
        ->and($content)->toStartWith("\xEF\xBB\xBF")
        ->and($content)->toContain("'=SUM(1,1)")
        ->and($content)->toContain("'+50760009999")
        ->and($content)->toContain('Correo');
});
