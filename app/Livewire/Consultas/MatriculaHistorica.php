<?php

namespace App\Livewire\Consultas;

use App\Services\MatriculaConfig;
use App\Services\MatriculaQueryBuilder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MatriculaHistorica extends Component
{
    // Filtros
    public array  $aniosSeleccionados   = [];
    public array  $ofertasSeleccionadas = [];
    public string $delZonal             = '';
    public string $busqueda             = '';
    public string $estado               = 'ACTIVO';

    // Estado UI
    public bool  $consultado = false;
    public array $resultados = [];
    public ?string $error    = null;

    // =========================================================================
    // Datos para los selects
    // =========================================================================

    #[Computed]
    public function aniosDisponibles(): array
    {
        // Solo años que ya fueron migrados a planeamiento
        $schemas = DB::select("
            SELECT schema_name
            FROM information_schema.schemata
            WHERE schema_name LIKE 'ra_carga%'
            ORDER BY schema_name DESC
        ");

        return collect($schemas)
            ->map(fn ($s) => (int) str_replace('ra_carga', '', $s->schema_name))
            ->filter(fn ($a) => $a >= 2011)
            ->values()
            ->all();
    }

    #[Computed]
    public function todasLasOfertas(): array
    {
        return MatriculaConfig::getTodasLasOfertas();
    }

    #[Computed]
    public function delegacionesZonales(): array
    {
        return DB::table('padron.localizaciones')
            ->whereNotNull('del_zonal')
            ->where('del_zonal', '!=', '')
            ->distinct()
            ->orderBy('del_zonal')
            ->pluck('del_zonal')
            ->all();
    }

    // =========================================================================
    // Acciones
    // =========================================================================

    public function consultar(): void
    {
        $this->error     = null;
        $this->resultados = [];
        $this->consultado = false;

        if (empty($this->aniosSeleccionados)) {
            $this->error = 'Seleccioná al menos un año.';
            return;
        }

        if (empty($this->ofertasSeleccionadas)) {
            $this->error = 'Seleccioná al menos una oferta.';
            return;
        }

        try {
            $builder = new MatriculaQueryBuilder(
                anios:    array_map('intval', $this->aniosSeleccionados),
                ofertas:  array_map('intval', $this->ofertasSeleccionadas),
                delZonal: $this->delZonal ?: null,
                busqueda: $this->busqueda ?: null,
                estado:   $this->estado,
            );

            $filas = $builder->ejecutar();
            $this->resultados = array_map(fn ($f) => (array) $f, $filas);
            $this->consultado = true;

        } catch (\Throwable $e) {
            $this->error = 'Error al ejecutar la consulta: ' . $e->getMessage();
        }
    }

    public function limpiar(): void
    {
        $this->aniosSeleccionados   = [];
        $this->ofertasSeleccionadas = [];
        $this->delZonal             = '';
        $this->busqueda             = '';
        $this->estado               = 'ACTIVO';
        $this->resultados           = [];
        $this->consultado           = false;
        $this->error                = null;
    }

    public function exportarExcel(): StreamedResponse
{
    $anios   = array_map('intval', $this->aniosSeleccionados);
    $ofertas = array_map('intval', $this->ofertasSeleccionadas);

    return response()->streamDownload(function () use ($anios, $ofertas) {

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // ── Cabecera ──
        $cabecera = [
            'Delegación Zonal', 'CUE Anexo', 'Nombre',
            'Oferta', 'Descripción Oferta', 'Modalidad', 'Estado',
        ];
        foreach ($anios as $anio) {
            $cabecera[] = "Matrícula {$anio}";
            $cabecera[] = "Varones {$anio}";
        }
        $sheet->fromArray($cabecera, null, 'A1');

        // Estilo de cabecera
        $ultimaCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($cabecera));
        $sheet->getStyle("A1:{$ultimaCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']],
        ]);

        // ── Filas ──
        $fila = 2;
        foreach ($this->resultados as $registro) {
            $row = [
                $registro['del_zonal']         ?? '',
                $registro['cueanexo']           ?? '',
                $registro['nombre']             ?? '',
                $registro['c_oferta']           ?? '',
                $registro['descripcion_oferta'] ?? '',
                $registro['modalidad']          ?? '',
                $registro['estado']             ?? '',
            ];
            foreach ($anios as $anio) {
                $row[] = $registro["matricula_{$anio}"] ?? '';
                $row[] = $registro["varones_{$anio}"]   ?? '';
            }
            $sheet->fromArray($row, null, "A{$fila}");
            $fila++;
        }

        // Ancho automático de columnas
        foreach (range(1, count($cabecera)) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        // Freezar la primera fila
        $sheet->freezePane('A2');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');

    }, 'matricula_historica_' . date('Ymd_His') . '.xlsx', [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ]);
}

    // =========================================================================

    public function render()
    {
        return view('livewire.consultas.matricula-historica')
            ->title('Matrícula Histórica');
    }
}
