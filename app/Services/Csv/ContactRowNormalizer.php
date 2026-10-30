<?php

namespace App\Services\Csv;

use App\Enums\ContactType;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ContactRowNormalizer
{
    private const DATE_FORMATS = [
        'Y-m-d', 'Y/m/d', 'Ymd',
        'd/m/Y', 'd-m-Y', 'd.m.Y',
        'd/m/y', 'm/d/Y',
        'j/n/Y', 'j-n-Y',
        'Y-m-d H:i:s',
        'd/m/Y H:i',
    ];

    /**
     * @param  array<string, string|null>  $record
     * @return array{data: array<string, mixed>, errors: array<int, string>, warnings: array<int, string>}
     */
    public function normalize(array $record): array
    {
        $errors = [];
        $warnings = [];

        $name = $this->clean($record['name'] ?? null, 150);

        if ($name === null) {
            $errors[] = 'Falta el nombre.';
        }

        $email = $this->normalizeEmail($record['email'] ?? null, $errors);
        $phone = $this->normalizePhone($record['phone'] ?? null, $errors);

        if ($email === null && $phone === null && ! $this->hasInvalidContactInfo($record)) {
            $errors[] = 'El contacto necesita al menos un correo o un teléfono.';
        }

        $birthdate = $this->normalizeBirthdate($record['birthdate'] ?? null, $warnings);

        $data = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'company' => $this->clean($record['company'] ?? null, 150),
            'job_title' => $this->clean($record['job_title'] ?? null, 120),
            'contact_type' => $this->normalizeContactType($record['contact_type'] ?? null),
            'business_type' => $this->clean($record['business_type'] ?? null, 80),
            'gender' => $this->normalizeGender($record['gender'] ?? null),
            'city' => $this->clean($record['city'] ?? null, 80),
            'province' => $this->clean($record['province'] ?? null, 80),
            'country' => $this->clean($record['country'] ?? null, 80),
            'birthdate' => $birthdate?->toDateString(),
            'source' => $this->clean($record['source'] ?? null, 80),
            'associated_brand' => $this->clean($record['associated_brand'] ?? null, 120),
            'associated_company' => $this->clean($record['associated_company'] ?? null, 150),
        ];

        return [
            'data' => $data,
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * @param  array<int, string>  $errors
     */
    private function normalizeEmail(?string $value, array &$errors): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $value = mb_strtolower(str_replace(' ', '', preg_replace('/^mailto:/i', '', $value) ?? $value));

        if (! filter_var($value, FILTER_VALIDATE_EMAIL) || mb_strlen($value) > 190) {
            $errors[] = 'El correo «'.Str::limit($value, 60).'» no es válido.';

            return null;
        }

        return $value;
    }

    /**
     * @param  array<int, string>  $errors
     */
    private function normalizePhone(?string $value, array &$errors): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $phone = preg_replace('/[^\d+]/', '', $value) ?? '';

        if (str_starts_with($phone, '00')) {
            $phone = '+'.substr($phone, 2);
        }

        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if (mb_strlen($digits) < 7 || mb_strlen($digits) > 15) {
            $errors[] = 'El teléfono «'.Str::limit($value, 30).'» no es válido.';

            return null;
        }

        return mb_substr($phone, 0, 40);
    }

    private function hasInvalidContactInfo(array $record): bool
    {
        return filled($record['email'] ?? null) || filled($record['phone'] ?? null);
    }

    /**
     * @param  array<int, string>  $warnings
     */
    private function normalizeBirthdate(?string $value, array &$warnings): ?Carbon
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $value = str_replace('T', ' ', $value);
        $date = null;

        foreach (self::DATE_FORMATS as $format) {
            if (! Carbon::hasFormat($value, $format)) {
                continue;
            }

            $candidate = Carbon::createFromFormat($format, $value);

            $date = $candidate->startOfDay();

            break;
        }

        if ($date === null) {
            $timestamp = strtotime($value);

            if ($timestamp !== false) {
                $date = Carbon::createFromTimestamp($timestamp)->startOfDay();
            }
        }

        if ($date === null) {
            $warnings[] = 'La fecha «'.Str::limit($value, 30).'» no se pudo interpretar y se omitió.';

            return null;
        }

        if ($date->isFuture() || $date->year < 1900) {
            $warnings[] = 'La fecha «'.Str::limit($value, 30).'» está fuera de rango y se omitió.';

            return null;
        }

        return $date;
    }

    private function normalizeContactType(?string $value): ?string
    {
        $value = HeaderMapper::normalize((string) $value);

        if ($value === '') {
            return null;
        }

        if (str_contains($value, 'b2b') || str_contains($value, 'empresa') || str_contains($value, 'business')) {
            return ContactType::B2B->value;
        }

        if (str_contains($value, 'b2c') || str_contains($value, 'persona') || str_contains($value, 'individual') || str_contains($value, 'consumidor')) {
            return ContactType::B2C->value;
        }

        return null;
    }

    private function normalizeGender(?string $value): ?string
    {
        $value = HeaderMapper::normalize((string) $value);

        return match (true) {
            $value === '' => null,
            in_array($value, ['f', 'femenino', 'female', 'mujer'], true) => 'Femenino',
            in_array($value, ['m', 'masculino', 'male', 'hombre'], true) => 'Masculino',
            in_array($value, ['otro', 'otra', 'other', 'no binario', 'no binaria'], true) => 'Otro',
            default => Str::title(mb_substr((string) $value, 0, 40)),
        };
    }

    private function clean(?string $value, int $limit): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return mb_substr($value, 0, $limit);
    }
}
