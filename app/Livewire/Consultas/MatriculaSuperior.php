<?php

namespace App\Livewire\Consultas;

use App\Services\SuperiorQueryBuilder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MatriculaSuperior extends Component
{
    public array  $aniosSeleccionados = [];
    public string $delZonal           = '';
    public string $busqueda           = '';
    public string $tipoFormacion      = '';

    public bool   $consultado = false;
    public array  $resultados = [];
    public ?string $error     = null;

    #[Computed]
    public function aniosDisponibles(): array
    {
        $schemas = DB::select("
            SELECT schema_name FROM information_schema.schemata
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

    public function consultar(): void
    {
        $this->error     = null;
        $this->resultados = [];
        $this->consultado = false;

        if (empty($this->aniosSeleccionados)) {
            $this->error = 'Seleccioná al menos un año.';
            return;
        }

        try {
            $builder = new SuperiorQueryBuilder(
                anios:         array_map('intval', $this->aniosSeleccionados),
                delZonal:      $this->delZonal      ?: null,
                busqueda:      $this->busqueda      ?: null,
                tipoFormacion: $this->tipoFormacion ?: null,
            );
            $this->resultados = array_map(fn ($f) => (array) $f, $builder->ejecutar());
            $this->consultado = true;
        } catch (\Throwable $e) {
            $this->error = 'Error al ejecutar la consulta: ' . $e->getMessage();
        }
    }

    public function limpiar(): void
    {
        $this->aniosSeleccionados = [];
        $this->delZonal           = '';
        $this->busqueda           = '';
        $this->tipoFormacion      = '';
        $this->resultados         = [];
        $this->consultado         = false;
        $this->error              = null;
    }

    public function exportarExcel(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();

            $cabecera = ['Delegación Zonal', 'CUE Anexo', 'Nombre', 'Oferta', 'Modalidad', 'Año', 'Plan de Estudio / Título', 'Tipo de Formación', 'Total'];
            $sheet->fromArray($cabecera, null, 'A1');

            $ultimaCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($cabecera));
            $sheet->getStyle("A1:{$ultimaCol}1")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']],
            ]);

            $fila = 2;
            foreach ($this->resultados as $reg) {
                $sheet->fromArray([
                    $reg['del_zonal']           ?? '',
                    $reg['cueanexo']             ?? '',
                    $reg['nombre']               ?? '',
                    $reg['c_oferta']             ?? '',
                    $reg['modalidad']            ?? '',
                    $reg['anio']                 ?? '',
                    $reg['plan_estudio_titulo']  ?? '',
                    $reg['tipo_formacion']       ?? '',
                    $reg['total']                ?? '',
                ], null, "A{$fila}");
                $fila++;
            }

            foreach (range(1, count($cabecera)) as $col) {
                $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
            }
            $sheet->freezePane('A2');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');

        }, 'superior_' . date('Ymd_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function render()
    {
        return view('livewire.consultas.matricula-superior')
            ->title('Superior');
    }
}
