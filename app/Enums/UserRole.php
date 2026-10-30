<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Editor = 'editor';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Editor => 'Editor',
            self::Viewer => 'Lector',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Admin => 'Gestiona contactos, segmentos, importaciones y usuarios.',
            self::Editor => 'Crea y edita contactos, segmentos e importaciones.',
            self::Viewer => 'Consulta contactos, segmentos e historial; puede exportar.',
        };
    }

    public function canWrite(): bool
    {
        return $this !== self::Viewer;
    }

    public function canDelete(): bool
    {
        return $this === self::Admin;
    }

    public function canManageUsers(): bool
    {
        return $this === self::Admin;
    }
}
