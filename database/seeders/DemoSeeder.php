<?php

namespace Database\Seeders;

use App\Enums\ImportStatus;
use App\Models\ActivityLog;
use App\Models\Contact;
use App\Models\Import;
use App\Models\Note;
use App\Models\Segment;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $wasEnabled = ActivityLogger::$enabled;
        ActivityLogger::$enabled = false;

        try {
            $this->seedSegments();
            $this->seedContacts();
            $this->seedImports();
            $this->seedNotes();
            $this->seedActivity();
        } finally {
            ActivityLogger::$enabled = $wasEnabled;
        }
    }

    private function seedSegments(): void
    {
        $segments = [
            ['name' => 'Clientes', 'color' => '#C65D3B', 'description' => 'Personas y empresas que ya compraron o contrataron.'],
            ['name' => 'Proveedores', 'color' => '#7A8B6F', 'description' => 'Aliados que suministran productos o servicios.'],
            ['name' => 'Prensa', 'color' => '#4F7C8A', 'description' => 'Medios, periodistas y comunicadores.'],
            ['name' => 'Eventos', 'color' => '#D9A441', 'description' => 'Asistentes a ferias, talleres y encuentros.'],
            ['name' => 'Hoteles', 'color' => '#8C6A9E', 'description' => 'Contactos del sector hotelero.'],
            ['name' => 'Restaurantes', 'color' => '#B56576', 'description' => 'Restaurantes y cafeterías aliadas.'],
            ['name' => 'ONG', 'color' => '#5C7A5C', 'description' => 'Organizaciones sociales y fundaciones.'],
            ['name' => 'Newsletter', 'color' => '#A9784E', 'description' => 'Suscriptores del boletín mensual.'],
        ];

        foreach ($segments as $segment) {
            Segment::firstOrCreate(['name' => $segment['name']], $segment);
        }
    }

    private function seedContacts(): void
    {
        if (Contact::query()->exists()) {
            return;
        }

        $admin = $this->user('admin');
        $editor = $this->user('editor');
        $segments = Segment::all();

        $fixed = [
            ['name' => 'María López', 'email' => 'maria.lopez@example.test', 'phone' => '+50760001111', 'company' => 'Hotel Sol', 'job_title' => 'Gerente general', 'contact_type' => 'B2B', 'business_type' => 'Hotel', 'gender' => 'Femenino', 'city' => 'Panamá', 'province' => 'Panamá', 'country' => 'Panamá', 'birthdate' => now()->addDays(4)->subYears(38)->format('Y-m-d'), 'source' => 'Feria', 'associated_brand' => 'Sol', 'associated_company' => 'Hotel Sol'],
            ['name' => 'Carlos Méndez', 'email' => 'carlos.mendez@example.test', 'phone' => '+50760002222', 'company' => 'Grupo Istmo', 'job_title' => 'Director comercial', 'contact_type' => 'B2B', 'business_type' => 'Retail', 'gender' => 'Masculino', 'city' => 'Colón', 'province' => 'Colón', 'country' => 'Panamá', 'birthdate' => now()->addDays(11)->subYears(45)->format('Y-m-d'), 'source' => 'Referido', 'associated_brand' => 'Istmo', 'associated_company' => 'Grupo Istmo'],
            ['name' => 'Valeria Ortiz', 'email' => 'valeria.ortiz@example.test', 'phone' => '+50760003333', 'company' => 'Café Darién', 'job_title' => 'Fundadora', 'contact_type' => 'B2B', 'business_type' => 'Restaurante', 'gender' => 'Femenino', 'city' => 'David', 'province' => 'Chiriquí', 'country' => 'Panamá', 'birthdate' => now()->addDays(19)->subYears(29)->format('Y-m-d'), 'source' => 'Instagram', 'associated_brand' => 'Café Darién', 'associated_company' => null],
            ['name' => 'Diego Herrera', 'email' => 'diego.herrera@example.test', 'phone' => '+50760004444', 'company' => null, 'job_title' => null, 'contact_type' => 'B2C', 'business_type' => null, 'gender' => 'Masculino', 'city' => 'Santiago', 'province' => 'Veraguas', 'country' => 'Panamá', 'birthdate' => now()->addDays(2)->subYears(33)->format('Y-m-d'), 'source' => 'Web', 'associated_brand' => null, 'associated_company' => null],
            ['name' => 'Sofía Jiménez', 'email' => 'sofia.jimenez@example.test', 'phone' => '+50760005555', 'company' => 'Fundación Semilla', 'job_title' => 'Coordinadora de proyectos', 'contact_type' => 'B2B', 'business_type' => 'ONG', 'gender' => 'Femenino', 'city' => 'Chitré', 'province' => 'Herrera', 'country' => 'Panamá', 'birthdate' => now()->addDays(24)->subYears(41)->format('Y-m-d'), 'source' => 'Evento', 'associated_brand' => 'Semilla', 'associated_company' => 'Fundación Semilla'],
            ['name' => 'Andrés Castillo', 'email' => 'andres.castillo@example.test', 'phone' => '+50760006666', 'company' => 'Medios del Pacífico', 'job_title' => 'Editor', 'contact_type' => 'B2B', 'business_type' => 'Proveedor', 'gender' => 'Masculino', 'city' => 'Panamá', 'province' => 'Panamá', 'country' => 'Panamá', 'birthdate' => now()->addDays(7)->subYears(52)->format('Y-m-d'), 'source' => 'Prensa', 'associated_brand' => 'Pacífico', 'associated_company' => 'Medios del Pacífico'],
        ];

        foreach ($fixed as $data) {
            $contact = Contact::create($data + ['created_by' => $admin->id, 'updated_by' => $admin->id]);
            $contact->segments()->sync($segments->random(fake()->numberBetween(1, 3))->pluck('id')->all());
        }

        Contact::factory()
            ->count(240)
            ->create([
                'created_by' => $admin->id,
                'updated_by' => $editor->id,
            ])
            ->each(function (Contact $contact, int $index) use ($segments) {
                if ($index % 13 === 0) {
                    $contact->update(['phone' => null]);
                }

                if ($index % 19 === 0 && $contact->phone !== null) {
                    $contact->update(['email' => null]);
                }

                if ($index < 18) {
                    $contact->update([
                        'birthdate' => now()
                            ->addDays($index)
                            ->subYears(fake()->numberBetween(24, 55))
                            ->format('Y-m-d'),
                    ]);
                }

                $contact->segments()->sync($segments->random(fake()->numberBetween(1, 3))->pluck('id')->all());
            });
    }

    private function seedImports(): void
    {
        if (Import::query()->exists()) {
            return;
        }

        $admin = $this->user('admin');
        $editor = $this->user('editor');

        $imports = [
            ['user' => $admin, 'name' => 'lista-clientes-octubre.csv', 'status' => ImportStatus::Completed, 'total' => 148, 'created' => 121, 'duplicates' => 24, 'errors' => 3, 'days' => 2],
            ['user' => $editor, 'name' => 'feria-hotelera.csv', 'status' => ImportStatus::Completed, 'total' => 96, 'created' => 80, 'duplicates' => 14, 'errors' => 2, 'days' => 5],
            ['user' => $editor, 'name' => 'prensa-septiembre.csv', 'status' => ImportStatus::Failed, 'total' => 0, 'created' => 0, 'duplicates' => 0, 'errors' => 1, 'days' => 9, 'failed' => true],
            ['user' => $admin, 'name' => 'newsletter-agosto.csv', 'status' => ImportStatus::Completed, 'total' => 212, 'created' => 198, 'duplicates' => 12, 'errors' => 2, 'days' => 18],
            ['user' => $editor, 'name' => 'restaurantes-agosto.csv', 'status' => ImportStatus::Completed, 'total' => 64, 'created' => 55, 'duplicates' => 8, 'errors' => 1, 'days' => 25],
        ];

        foreach ($imports as $data) {
            $finished = now()->subDays($data['days'])->addMinutes(3);
            Import::forceCreate([
                'user_id' => $data['user']->id,
                'filename' => 'imports/'.Str::uuid().'.csv',
                'original_name' => $data['name'],
                'status' => $data['status'],
                'delimiter' => ',',
                'encoding' => 'UTF-8',
                'total_rows' => $data['total'],
                'created_count' => $data['created'],
                'skipped_count' => $data['duplicates'] + $data['errors'],
                'duplicate_count' => $data['duplicates'],
                'error_count' => $data['errors'],
                'mapping' => [
                    'name' => 'Nombre y Apellido',
                    'email' => 'Correo',
                    'phone' => 'Teléfono celular / Whatsapp',
                    'company' => 'Empresa',
                ],
                'errors' => $data['failed'] ?? false
                    ? [['row' => 2, 'type' => 'error', 'message' => 'No se reconoció ninguna columna obligatoria.']]
                    : [['row' => 7, 'type' => 'error', 'message' => 'Correo con formato inválido.'], ['row' => 19, 'type' => 'error', 'message' => 'Fila sin correo ni teléfono.']],
                'started_at' => $finished->copy()->subMinutes(3),
                'finished_at' => $data['failed'] ?? false ? null : $finished,
                'created_at' => $finished->copy()->subDays(0),
                'updated_at' => $finished,
            ]);
        }

        Import::forceCreate([
            'user_id' => $editor->id,
            'filename' => 'imports/'.Str::uuid().'.csv',
            'original_name' => 'contactos-en-proceso.csv',
            'status' => ImportStatus::Processing,
            'delimiter' => ';',
            'encoding' => 'UTF-8',
            'total_rows' => 6200,
            'created_count' => 1880,
            'skipped_count' => 120,
            'duplicate_count' => 90,
            'error_count' => 30,
            'mapping' => ['name' => 'Nombre y Apellido', 'email' => 'Correo'],
            'started_at' => now()->subMinutes(2),
        ]);
    }

    private function seedNotes(): void
    {
        if (Note::query()->exists()) {
            return;
        }

        $users = User::whereIn('email', [
            config('demo.accounts.admin'),
            config('demo.accounts.editor'),
        ])->get();

        $contacts = Contact::query()->inRandomOrder()->limit(60)->get();
        $templates = [
            'Prefiere que lo contacten por WhatsApp después de las 3 p. m.',
            'Pidió una cotización para el próximo trimestre.',
            'Se conoció en la feria de turismo; interesada en alianzas.',
            'Recomendó a dos personas del sector hotelero.',
            'Hay que enviarle el catálogo actualizado y la lista de precios.',
            'Solo acepta llamadas, no revisa correo con frecuencia.',
            'Quedó en confirmar la reunión la próxima semana.',
            'Es contacto clave para la campaña de fin de año.',
        ];

        foreach ($contacts as $contact) {
            Note::forceCreate([
                'contact_id' => $contact->id,
                'user_id' => $users->random()->id,
                'body' => fake()->randomElement($templates),
                'created_at' => now()->subDays(fake()->numberBetween(0, 20))->subHours(fake()->numberBetween(0, 12)),
            ]);
        }
    }

    private function seedActivity(): void
    {
        if (ActivityLog::query()->exists()) {
            return;
        }

        $admin = $this->user('admin');
        $editor = $this->user('editor');
        $users = [$admin, $editor];
        $contacts = Contact::query()->inRandomOrder()->limit(40)->get();
        $imports = Import::query()->whereNotNull('finished_at')->get();
        $rows = [];

        foreach ($contacts->take(16) as $index => $contact) {
            $rows[] = [
                'user_id' => $users[$index % 2]->id,
                'subject_type' => Contact::class,
                'subject_id' => $contact->id,
                'event' => 'created',
                'description' => "Creó el contacto {$contact->name}",
                'properties' => json_encode(['name' => $contact->name]),
                'created_at' => now()->subDays(fake()->numberBetween(1, 18))->subHours(fake()->numberBetween(0, 12)),
                'updated_at' => now(),
            ];
        }

        foreach ($contacts->skip(16)->take(8) as $index => $contact) {
            $rows[] = [
                'user_id' => $users[$index % 2]->id,
                'subject_type' => Contact::class,
                'subject_id' => $contact->id,
                'event' => 'updated',
                'description' => "Actualizó el contacto {$contact->name}",
                'properties' => json_encode(['changes' => ['phone' => $contact->phone]]),
                'created_at' => now()->subDays(fake()->numberBetween(0, 8))->subHours(fake()->numberBetween(0, 10)),
                'updated_at' => now(),
            ];
        }

        foreach ($contacts->skip(24)->take(5) as $contact) {
            $rows[] = [
                'user_id' => $editor->id,
                'subject_type' => Contact::class,
                'subject_id' => $contact->id,
                'event' => 'note_added',
                'description' => "Añadió una nota a {$contact->name}",
                'properties' => json_encode(['excerpt' => 'Prefiere que lo contacten por WhatsApp.']),
                'created_at' => now()->subDays(fake()->numberBetween(0, 6)),
                'updated_at' => now(),
            ];
        }

        foreach ($imports as $import) {
            $rows[] = [
                'user_id' => $import->user_id,
                'subject_type' => Import::class,
                'subject_id' => $import->id,
                'event' => 'imported',
                'description' => "Importó {$import->created_count} contactos desde {$import->original_name}",
                'properties' => json_encode(['created' => $import->created_count, 'duplicates' => $import->duplicate_count]),
                'created_at' => $import->finished_at ?? $import->created_at,
                'updated_at' => now(),
            ];
        }

        DB::table('activity_logs')->insert($rows);
    }

    private function user(string $key): User
    {
        return User::where('email', config("demo.accounts.{$key}"))->firstOrFail();
    }
}
