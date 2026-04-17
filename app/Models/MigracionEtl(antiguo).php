<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MigracionEtl extends Model
{
    protected $table = 'migraciones_etl';

    protected $fillable = [
    'clave',
    'nombre',
    'descripcion',
    'comando',
    'estado',
    'total_registros',
    'registros_procesados',
    'ultimo_error',
    'iniciado_at',
    'completado_at',
    ];

    protected $casts = [
        'iniciado_at'   => 'datetime',
        'completado_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------

   public function porcentaje(): int
    {
        if ($this->total_registros === 0) {
            return 0;
        }
        return (int) round(($this->registros_procesados / $this->total_registros) * 100);
    } 

    public function estaEjecutando(): bool
    {
        return $this->estado === 'ejecutando';
    }

    // Marca el inicio de una ejecución
    public function marcarInicio(): void
    {
        $this->update([
            'estado'               => 'ejecutando',
            'iniciado_at'          => now(),
            'completado_at'        => null,
            'ultimo_error'         => null,
            'total_registros'      => 0,
            'registros_procesados' => 0,
        ]);
    }

    public function marcarCompletado(int $total): void
    {
        $this->update([
            'estado'               => 'completado',
            'completado_at'        => now(),
            'total_registros'      => $total,
            'registros_procesados' => $total,
        ]);
    }

    public function marcarError(string $mensaje): void
    {
        $this->update([
            'estado'      => 'error',
            'ultimo_error' => $mensaje,
        ]);
    }

    public function actualizarProgreso(int $procesados, int $total): void
    {
        $this->update([
            'registros_procesados' => $procesados,
            'total_registros'      => $total,
        ]);
    }
}
