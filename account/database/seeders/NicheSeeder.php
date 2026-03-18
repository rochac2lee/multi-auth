<?php

namespace Database\Seeders;

use App\Models\Niche;
use Illuminate\Database\Seeder;

class NicheSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = [
            'Newborn',
            'Acompanhamento Infantil',
            'Ensaio Infantil',
            'Aniversário Infantil',
            'Escolar',
            'Gestante',
            'Pets',
            'Aniversário 15 Anos',
            'Pré-Wedding',
            'Casamento',
            'Formatura',
            'Shows e Eventos',
            'Esportes',
            'Sensual',
            'Moda',
            'Retrato Corporativo',
            'Publicidade e Gastronomia',
            'Arquitetura e Interiores',
            'Fotojornalismo',
            'Outros',
        ];

        foreach ($names as $name) {
            Niche::firstOrCreate(['name' => $name]);
        }
    }
}

