<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SuperiorQueryBuilder
{
    private array $tablasExistentes = [];

    public function __construct(
        private array   $anios,
        private ?string $delZonal,
        private ?string $busqueda,
        private ?string $tipoFormacion,
    ) {}

    public function ejecutar(): array
    {
        [$sql, $bindings] = $this->construirSQL();
        return DB::select($sql, $bindings);
    }

    private function construirSQL(): array
    {
        $unionParts = [];

        foreach ($this->anios as $anio) {
            // 🔐 VALIDACIÓN IMPORTANTE
            if (!is_numeric($anio)) {
                continue;
            }

            $schema = "ra_carga{$anio}";

            if (!$this->tablaExiste($schema, 'Verde_287')) {
                continue;
            }

            $unionParts[] = "
                SELECT id_localizacion, {$anio} AS anio, plan_estudio_titulo, tipo_formacion, total
                FROM \"{$schema}\".\"Verde_287\"
            ";
        }

        if (empty($unionParts)) {
            return ["SELECT NULL::integer AS del_zonal WHERE false", []];
        }

        $unionSQL = implode("\nUNION ALL\n", $unionParts);

        [$where, $bindings] = $this->sqlWhere();

        $sql = "
            WITH oferta_base AS (
                SELECT loc.id_localizacion, loc.del_zonal,
                       loc.cue || loc.anexo AS cueanexo,
                       loc.nombre, oloc.c_oferta, oloc.modalidad
                FROM padron.oferta_local oloc
                LEFT JOIN padron.localizaciones loc ON oloc.id_localizacion = loc.id_localizacion
                WHERE oloc.c_oferta = 115
                  AND oloc.estado = 'ACTIVO'
            ),
            mat_sup AS (
                {$unionSQL}
            )
            SELECT
                ob.del_zonal,
                ob.cueanexo,
                ob.nombre,
                ob.c_oferta,
                ob.modalidad,
                ms.anio,
                ms.plan_estudio_titulo,
                ms.tipo_formacion,
                ms.total
            FROM oferta_base ob
            LEFT JOIN mat_sup ms ON ms.id_localizacion = ob.id_localizacion
            {$where}
            ORDER BY ob.del_zonal, ob.cueanexo, ms.anio, ms.plan_estudio_titulo
        ";

        return [$sql, $bindings];
    }

    private function sqlWhere(): array
    {
        $condiciones = [];
        $bindings = [];

        if (!empty($this->delZonal)) {
            $condiciones[] = "ob.del_zonal = ?";
            $bindings[] = $this->delZonal;
        }

        if (!empty($this->busqueda)) {
            $condiciones[] = "(ob.nombre ILIKE ? OR ob.cueanexo ILIKE ?)";
            $bindings[] = "%{$this->busqueda}%";
            $bindings[] = "%{$this->busqueda}%";
        }

        if (!empty($this->tipoFormacion)) {
            $condiciones[] = "ms.tipo_formacion ILIKE ?";
            $bindings[] = "%{$this->tipoFormacion}%";
        }

        $where = empty($condiciones)
            ? ''
            : 'WHERE ' . implode(' AND ', $condiciones);

        return [$where, $bindings];
    }

    private function tablaExiste(string $schema, string $tabla): bool
    {
        $key = "{$schema}.{$tabla}";

        if (!isset($this->tablasExistentes[$key])) {
            $res = DB::select("
                SELECT 1 FROM information_schema.tables
                WHERE table_schema = ? AND table_name = ? LIMIT 1
            ", [$schema, $tabla]);

            $this->tablasExistentes[$key] = !empty($res);
        }

        return $this->tablasExistentes[$key];
    }
}