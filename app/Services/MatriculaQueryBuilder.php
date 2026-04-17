<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Construye dinámicamente la query de matrícula histórica
 * basándose en los años y ofertas seleccionados por el usuario.
 */
class MatriculaQueryBuilder
{
    private array $tablasExistentes = [];

    private array $joinsAgregados = [];

    public function __construct(
        private array $anios,
        private array $ofertas,
        private ?string $delZonal,
        private ?string $busqueda,
        private string $estado,
    ) {}

    public function ejecutar(): array
    {
    [$sql, $bindings] = $this->construirSQL();
    return DB::select($sql, $bindings);
    }

    public function getSql(): string
{
    [$sql, $bindings] = $this->construirSQL();
    return $sql;
}

 private function construirSQL(): array
{
    $baseUE  = $this->sqlBaseUnidadesEducativas();
    $joins   = $this->sqlJoins();
    $columnas = $this->sqlColumnasMatricula();

    [$where, $bindings] = $this->sqlWherePrincipal();

    $orderBy = "ORDER BY ue.del_zonal, ue.cueanexo, ue.c_oferta";

    $sql = "
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

    return [$sql, $bindings];
}

    // =========================================================================
    // Base de unidades educativas desde padrón
    // =========================================================================

    private function sqlBaseUnidadesEducativas(): string
    {
        $ofertas       = $this->ofertas;
        $excluidos     = implode(',', MatriculaConfig::EXCLUIR_OFERTA_LOCAL);
        $ofertasNormal = array_diff($ofertas, MatriculaConfig::OFERTAS_CON_GROUPBY);
        $ofertasGrupo  = array_intersect($ofertas, MatriculaConfig::OFERTAS_CON_GROUPBY);

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

    // =========================================================================
    // LEFT JOINs por año × oferta
    // =========================================================================

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

            // Registrar que este alias sí fue agregado
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
            $resultado = DB::select("
                SELECT 1 FROM information_schema.tables
                WHERE table_schema = ?
                AND table_name = ?
                LIMIT 1
            ", [$schema, $tabla]);

            $this->tablasExistentes[$key] = !empty($resultado);
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

    // =========================================================================
    // Columnas de matrícula en el SELECT principal
    // =========================================================================

    private function sqlColumnasMatricula(): string
{
    $cols = '';

    foreach ($this->anios as $anio) {
        $caseMatricula = "CASE\n";
        $caseVarones   = "CASE\n";

        foreach ($this->ofertas as $cOferta) {
            // Solo referenciar el alias si realmente tiene JOIN
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

    // =========================================================================
    // WHERE principal
    // =========================================================================

private function sqlWherePrincipal(): array
{
    $condiciones = [];
    $bindings = [];

    if (!empty($this->delZonal)) {
        $condiciones[] = "ue.del_zonal = ?";
        $bindings[] = $this->delZonal;
    }

    if (!empty($this->busqueda)) {
        $condiciones[] = "(ue.nombre ILIKE ? OR ue.cueanexo ILIKE ?)";
        $bindings[] = "%{$this->busqueda}%";
        $bindings[] = "%{$this->busqueda}%";
    }

    if ($this->estado !== 'TODOS') {
    $condiciones[] = "ue.estado = ?";
    $bindings[] = $this->estado;
    }

    $where = empty($condiciones)
        ? ''
        : 'WHERE ' . implode(' AND ', $condiciones);

    return [$where, $bindings];
    }
}