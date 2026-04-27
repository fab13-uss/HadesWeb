<?php

namespace App\Livewire;

use App\Models\MigracionEtl;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    #[Computed]
    public function ultimasMigraciones()
    {
        return MigracionEtl::whereIn('estado', ['completado', 'error'])
            ->whereNotNull('completado_at')
            ->orderByDesc('completado_at')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function totalEsquemas(): int
    {
        try {
            return DB::select("
                SELECT COUNT(*) as total FROM information_schema.schemata
                WHERE schema_name LIKE 'ra_carga%'
            ")[0]->total ?? 0;
        } catch (\Throwable) {
            return 0;
        }
    }

    #[Computed]
    public function padronMigrado(): bool
    {
        try {
            return !empty(DB::select("
                SELECT 1 FROM information_schema.schemata
                WHERE schema_name = 'padron' LIMIT 1
            "));
        } catch (\Throwable) {
            return false;
        }
    }

    public function render()
    {
        return view('livewire.dashboard')->title('Dashboard');
    }
}