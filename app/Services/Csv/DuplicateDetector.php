<?php

namespace App\Services\Csv;

use App\Models\Contact;

class DuplicateDetector
{
    /** @var array<string, true> */
    private array $emails = [];

    /** @var array<string, true> */
    private array $phones = [];

    /** @var array<string, true> */
    private array $fileEmails = [];

    /** @var array<string, true> */
    private array $filePhones = [];

    public static function fromDatabase(): self
    {
        $detector = new self;

        Contact::query()
            ->select(['id', 'email', 'phone'])
            ->where(function ($query) {
                $query->whereNotNull('email')->orWhereNotNull('phone');
            })
            ->chunkById(1000, function ($contacts) use ($detector) {
                foreach ($contacts as $contact) {
                    $detector->seed($contact->email, $contact->phone);
                }
            });

        return $detector;
    }

    public function seed(?string $email, ?string $phone): void
    {
        if ($emailKey = self::emailKey($email)) {
            $this->emails[$emailKey] = true;
        }

        if ($phoneKey = self::phoneKey($phone)) {
            $this->phones[$phoneKey] = true;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function duplicateReason(array $data): ?string
    {
        $email = self::emailKey($data['email'] ?? null);
        $phone = self::phoneKey($data['phone'] ?? null);

        if ($email !== null && isset($this->emails[$email])) {
            return 'El correo ya está registrado en otro contacto.';
        }

        if ($phone !== null && isset($this->phones[$phone])) {
            return 'El teléfono ya está registrado en otro contacto.';
        }

        if ($email !== null && isset($this->fileEmails[$email])) {
            return 'El correo se repite dentro del archivo.';
        }

        if ($phone !== null && isset($this->filePhones[$phone])) {
            return 'El teléfono se repite dentro del archivo.';
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function remember(array $data): void
    {
        if ($email = self::emailKey($data['email'] ?? null)) {
            $this->fileEmails[$email] = true;
        }

        if ($phone = self::phoneKey($data['phone'] ?? null)) {
            $this->filePhones[$phone] = true;
        }
    }

    public static function emailKey(?string $email): ?string
    {
        $email = mb_strtolower(trim((string) $email));

        return $email === '' ? null : $email;
    }

    public static function phoneKey(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone) ?? '';

        return $digits === '' ? null : $digits;
    }
}
