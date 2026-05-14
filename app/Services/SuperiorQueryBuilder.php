<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class MatriculaQueryBuilder
{
    private array $tablasExistentes = [];
    private array $joinsAgregados   = [];

    public function __construct(
        private array   $anios,
        private array   $ofertas,
        private ?string $delZonal,
        private ?string $busqueda,
        private string  $estado,
    ) {}

    public function ejecutar(): array
    {
        return DB::select($this->construirSQL());
    }

    public function ejecutarPaginado(int $pagina, int $porPagina): array
    {
        $offset = ($pagina - 1) * $porPagina;
        $sql    = $this->construirSQL() . " LIMIT {$porPagina} OFFSET {$offset}";
        return DB::select($sql);
    }

    public function contarTotal(): int
    {
        $baseUE = $this->sqlBaseUnidadesEducativas();
        $where  = $this->sqlWherePrincipal();
        $sql    = "SELECT COUNT(*) as total FROM ({$baseUE}) ue {$where}";
        return (int) (DB::select($sql)[0]->total ?? 0);
    }

    public function getSql(): string
    {
        return $this->construirSQL();
    }

    private function construirSQL(): string
    {
        $baseUE   = $this->sqlBaseUnidadesEducativas();
        $joins    = $this->sqlJoins();
        $columnas = $this->sqlColumnasMatricula();
        $where    = $this->sqlWherePrincipal();
        $orderBy  = "ORDER BY ue.del_zonal, ue.cueanexo, ue.c_oferta";

        return "
            SELECT
                ue.del_zonal,
                ue.cueanexo,
                ue.nombre,
                ue.c_oferta,
                ue.descripcion_oferta,
                ue.modalidad,
                ue.estado
                {$columnas}
            FROM (
                {$baseUE}
            ) ue
            {$joins}
            {$where}
            {$orderBy}
        ";
    }

    private function sqlBaseUnidadesEducativas(): string
    {
        $excluidos     = implode(',', MatriculaConfig::EXCLUIR_OFERTA_LOCAL);
        $ofertasNormal = array_diff($this->ofertas, MatriculaConfig::OFERTAS_CON_GROUPBY);
        $ofertasGrupo  = array_intersect($this->ofertas, MatriculaConfig::OFERTAS_CON_GROUPBY);

        $parts = [];

        if (!empty($ofertasNormal)) {
            $lista       = implode(',', $ofertasNormal);
            $estadoWhere = $this->estado !== 'TODOS'
                ? "AND oloc.estado = '{$this->estado}'"
                : '';

            $parts[] = "
                SELECT oloc.id_localizacion, loc.cue || loc.anexo AS cueanexo,
                       oloc.c_oferta, oloc.descripcion_oferta, oloc.modalidad,
                       loc.nombre, loc.del_zonal, oloc.estado
                FROM padron.oferta_local oloc
                INNER JOIN padron.localizaciones loc ON loc.id_localizacion = oloc.id_localizacion
                WHERE oloc.c_oferta IN ({$lista})
                  AND oloc.id_oferta_local NOT IN ({$excluidos})
                  {$estadoWhere}
            ";
        }

        if (!empty($ofertasGrupo)) {
            $lista = implode(',', $ofertasGrupo);
            $parts[] = "
                SELECT oloc.id_localizacion, loc.cue || loc.anexo AS cueanexo,
                       oloc.c_oferta, oloc.descripcion_oferta, oloc.modalidad,
                       loc.nombre, loc.del_zonal, 'ACTIVO' AS estado
                FROM padron.oferta_local oloc
                INNER JOIN padron.localizaciones loc ON loc.id_localizacion = oloc.id_localizacion
                WHERE oloc.estado = 'ACTIVO'
                  AND oloc.c_oferta IN ({$lista})
                GROUP BY oloc.id_localizacion, loc.cue, loc.anexo, oloc.c_oferta,
                         oloc.descripcion_oferta, oloc.modalidad, loc.nombre, loc.del_zonal
            ";
        }

        return implode("\nUNION ALL\n", $parts);
    }

    private function sqlJoins(): string
    {
        $joins = [];

        foreach ($this->anios as $anio) {
            foreach ($this->ofertas as $cOferta) {
                $def = MatriculaConfig::OFERTAS[$cOferta] ?? null;
                if (!$def) continue;

                $alias  = "mat{$anio}_{$cOferta}";
                $schema = "ra_carga{$anio}";

                $tablasRequeridas = !empty($def['es_union'])
                    ? $def['tablas']
                    : [$def['tabla']];

                $todasExisten = collect($tablasRequeridas)
                    ->every(fn ($t) => $this->tablaExiste($schema, $t));

                if (!$todasExisten) {
                    continue;
                }

                $this->joinsAgregados["{$anio}_{$cOferta}"] = true;

                $subquery = $this->sqlSubqueryMatricula($schema, $def);

                $joins[] = "LEFT JOIN ({$subquery}) {$alias}
                    ON {$alias}.id_localizacion = ue.id_localizacion
                    AND ue.c_oferta = {$cOferta}";
            }
        }

        return implode("\n", $joins);
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

    private function sqlSubqueryMatricula(string $schema, array $def): string
    {
        $colTotal   = $def['col_total']   ?? 'total';
        $colVarones = $def['col_varones'] ?? 'varones';

        if (!empty($def['es_union'])) {
            $unionParts = array_map(fn ($tabla) =>
                "SELECT id_localizacion, SUM({$colTotal}) AS total, SUM({$colVarones}) AS varones
                 FROM \"{$schema}\".\"{$tabla}\" GROUP BY id_localizacion",
                $def['tablas']
            );
            $union = implode("\nUNION ALL\n", $unionParts);
            return "
                SELECT id_localizacion,
                       SUM(total) AS matricula,
                       SUM(varones) AS varones
                FROM ({$union}) _u
                GROUP BY id_localizacion
            ";
        }

        $tabla     = $def['tabla'];
        $filtroSQL = '';

        if (!empty($def['filtro_fila'])) {
            [$columna, $operador, $valores] = $def['filtro_fila'];
            $filtroSQL = match ($operador) {
                'in'       => "WHERE {$columna} IN (" . implode(',', array_map(fn ($v) => "'{$v}'", $valores)) . ")",
                'not_in'   => "WHERE {$columna} NOT IN (" . implode(',', array_map(fn ($v) => "'{$v}'", $valores)) . ")",
                'not_like' => "WHERE {$columna} NOT ILIKE '{$valores}'",
                default    => '',
            };
        }

        return "
            SELECT id_localizacion,
                   SUM({$colTotal}) AS matricula,
                   SUM({$colVarones}) AS varones
            FROM \"{$schema}\".\"{$tabla}\"
            {$filtroSQL}
            GROUP BY id_localizacion
        ";
    }

    private function sqlColumnasMatricula(): string
    {
        $cols = '';

        foreach ($this->anios as $anio) {
            $caseMatricula = "CASE\n";
            $caseVarones   = "CASE\n";

            foreach ($this->ofertas as $cOferta) {
                if (!isset($this->joinsAgregados["{$anio}_{$cOferta}"])) {
                    continue;
                }
                $alias = "mat{$anio}_{$cOferta}";
                $caseMatricula .= "    WHEN ue.c_oferta = {$cOferta} THEN {$alias}.matricula\n";
                $caseVarones   .= "    WHEN ue.c_oferta = {$cOferta} THEN {$alias}.varones\n";
            }

            $caseMatricula .= "    ELSE NULL END AS \"matricula_{$anio}\"";
            $caseVarones   .= "    ELSE NULL END AS \"varones_{$anio}\"";

            $cols .= ",\n{$caseMatricula}";
            $cols .= ",\n{$caseVarones}";
        }

        return $cols;
    }

    private function sqlWherePrincipal(): string
    {
        $condiciones = [];

        if ($this->delZonal) {
            $dz = addslashes($this->delZonal);
            $condiciones[] = "ue.del_zonal = '{$dz}'";
        }

        if ($this->busqueda) {
            $b = addslashes($this->busqueda);
            $condiciones[] = "(ue.nombre ILIKE '%{$b}%' OR ue.cueanexo ILIKE '%{$b}%')";
        }

        if ($this->estado !== 'TODOS') {
            $condiciones[] = "ue.estado = '{$this->estado}'";
        }

        return empty($condiciones) ? '' : 'WHERE ' . implode(' AND ', $condiciones);
    }
}