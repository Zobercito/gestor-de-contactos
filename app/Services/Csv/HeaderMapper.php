<?php

namespace App\Services\Csv;

use Illuminate\Support\Str;

class HeaderMapper
{
    /**
     * @return array<string, array{label: string, aliases: array<int, string>}>
     */
    public static function fields(): array
    {
        return [
            'name' => [
                'label' => 'Nombre',
                'aliases' => ['nombre', 'nombre y apellido', 'nombre completo', 'contacto', 'name', 'full name', 'contact name', 'nombre del contacto'],
            ],
            'email' => [
                'label' => 'Correo',
                'aliases' => ['correo', 'correo electronico', 'email', 'e mail', 'mail', 'correo e'],
            ],
            'phone' => [
                'label' => 'Teléfono',
                'aliases' => ['telefono', 'telefono celular whatsapp', 'telefono celular', 'celular', 'whatsapp', 'movil', 'phone', 'mobile', 'telefono movil'],
            ],
            'company' => [
                'label' => 'Empresa',
                'aliases' => ['empresa', 'compania', 'company', 'organizacion', 'organization'],
            ],
            'job_title' => [
                'label' => 'Cargo',
                'aliases' => ['cargo', 'puesto', 'posicion', 'job title', 'position', 'title'],
            ],
            'contact_type' => [
                'label' => 'Tipo de cliente',
                'aliases' => ['tipo de cliente', 'tipo cliente', 'b2b b2c', 'b2b', 'b2c', 'cliente', 'contact type'],
            ],
            'business_type' => [
                'label' => 'Tipo de negocio',
                'aliases' => ['tipo de negocio', 'tipo negocio', 'tipo de negocio hotel proveedor ong', 'sector', 'industria', 'business type', 'industry'],
            ],
            'gender' => [
                'label' => 'Género',
                'aliases' => ['genero', 'sexo', 'gender'],
            ],
            'city' => [
                'label' => 'Ciudad',
                'aliases' => ['ciudad', 'city'],
            ],
            'province' => [
                'label' => 'Provincia',
                'aliases' => ['provincia', 'estado', 'region', 'state'],
            ],
            'country' => [
                'label' => 'País',
                'aliases' => ['pais', 'country'],
            ],
            'birthdate' => [
                'label' => 'Fecha de nacimiento',
                'aliases' => ['cumpleanos', 'fecha de nacimiento', 'cumpleanos fecha de nacimiento', 'nacimiento', 'fecha nacimiento', 'birthdate', 'birthday', 'date of birth', 'dob'],
            ],
            'source' => [
                'label' => 'Origen',
                'aliases' => ['origen', 'origen del contacto', 'fuente', 'source'],
            ],
            'associated_brand' => [
                'label' => 'Marca asociada',
                'aliases' => ['marca asociada', 'marca', 'associated brand', 'brand'],
            ],
            'associated_company' => [
                'label' => 'Empresa asociada',
                'aliases' => ['empresa asociada', 'associated company'],
            ],
        ];
    }

    /**
     * @param  array<int, string>  $headers
     * @return array<string, int|null> field => header index
     */
    public function suggest(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $index => $header) {
            $normalized[$index] = self::normalize($header);
        }

        $used = [];
        $suggestions = [];

        foreach (self::fields() as $field => $definition) {
            $suggestions[$field] = null;

            foreach ($definition['aliases'] as $alias) {
                $alias = self::normalize($alias);

                foreach ($normalized as $index => $header) {
                    if (isset($used[$index])) {
                        continue;
                    }

                    if ($header === $alias) {
                        $suggestions[$field] = $index;
                        $used[$index] = true;

                        continue 3;
                    }
                }
            }
        }

        foreach (self::fields() as $field => $definition) {
            if ($suggestions[$field] !== null) {
                continue;
            }

            foreach ($definition['aliases'] as $alias) {
                $alias = self::normalize($alias);

                if (mb_strlen($alias) < 4) {
                    continue;
                }

                foreach ($normalized as $index => $header) {
                    if (isset($used[$index]) || $header === '') {
                        continue;
                    }

                    if (str_contains($header, $alias) || str_contains($alias, $header)) {
                        $suggestions[$field] = $index;
                        $used[$index] = true;

                        continue 3;
                    }
                }
            }
        }

        return $suggestions;
    }

    /**
     * @param  array<int, string|null>  $record
     * @param  array<string, int|null>  $mapping
     * @return array<string, string|null>
     */
    public function mapRecord(array $record, array $mapping): array
    {
        $mapped = [];

        foreach (self::fields() as $field => $definition) {
            $index = $mapping[$field] ?? null;
            $value = $index === null ? null : ($record[$index] ?? null);
            $value = $value === null ? null : trim((string) $value);

            $mapped[$field] = $value === '' ? null : $value;
        }

        return $mapped;
    }

    public static function normalize(string $value): string
    {
        $value = Str::ascii(mb_strtolower(trim($value)));
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? $value;

        return trim(preg_replace('/\s+/', ' ', $value) ?? $value);
    }
}
