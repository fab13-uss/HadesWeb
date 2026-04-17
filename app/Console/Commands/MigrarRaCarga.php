<?php

namespace App\Console\Commands;

use App\Models\MigracionEtl;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Throwable;

#[Signature('migrar:ra-carga {--anio= : Año del relevamiento (ej: 2024)} {--etl-id= : ID en migraciones_etl para reportar progreso}')]
#[Description('Migra las tablas de ra_cargaXXXX (nacion) al esquema ra_cargaXXXX en planeamiento')]
class MigrarRaCarga extends Command
{
    private const CHUNK = 500;
    private const ESTADOS_VACIOS = [20, 60];
    private const TIPOS_CODIGO_VALOR = [
        6, 8, 10, 12, 212, 213, 214,
        151, 152, 153, 154, 155, 156, 157,
        163, 165, 167, 169, 171, 172,
    ];

    private string $schema;
    private string $conexion;

    public function handle(): int
    {
        $anio = $this->option('anio') ?? date('Y');

        if (!is_numeric($anio) || $anio < 2011 || $anio > (int) date('Y')) {
            $this->error("Año inválido: {$anio}. Debe ser un año entre 2011 y " . date('Y') . ".");
            return self::FAILURE;
        }

        $this->schema   = "ra_carga{$anio}";
        $this->conexion = "nacion_{$anio}";

        // Crear la conexión dinámica reutilizando las credenciales de nacion
        $this->configurarConexion($anio);

        $tablas = DB::connection($this->conexion)->select("
        SELECT COUNT(*) as total
        FROM information_schema.tables
        WHERE table_schema = 'public'
        ");

        if (($tablas[0]->total ?? 0) == 0) {
        $etl = $this->resolverEtl();
        $etl?->marcarError("La base ra_carga{$anio} existe pero está vacía — no hay datos para migrar.");
        $this->error("La base ra_carga{$anio} está vacía.");
        return self::FAILURE;
        }

        $etl = $this->resolverEtl();

        $this->info("Migrando ra_carga{$anio}...");

        try {
            $this->info('── Paso 1/5: Preparando esquema destino en planeamiento...');
            $this->prepararEsquemaDestino();

            $this->info('── Paso 2/5: Migrando localizacion...');
            $this->migrarLocalizacion($etl, porcentajeBase: 0);

            $this->info('── Paso 3/5: Migrando detalle_cuadro...');
            $this->migrarDetalleCuadro($etl, porcentajeBase: 10);

            $this->info('── Paso 4/5: Migrando estado_carga_nivel...');
            $this->migrarEstadoCargaNivel($etl, porcentajeBase: 20);

            $this->info('── Paso 5/5: Migrando estado_carga...');
            $this->migrarEstadoCarga($etl, porcentajeBase: 30);

            $this->info('── Paso extra: Generando tablas dinámicas de cuadros...');
            $this->migrarCuadrosDinamicos($etl, porcentajeBase: 40);

            $etl?->marcarCompletado(registros: 0);
            $this->info("✓ Migración {$this->schema} completada.");
            return self::SUCCESS;

        } catch (Throwable $e) {
            $etl?->marcarError($e->getMessage());
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    // =========================================================================
    // Conexión dinámica
    // =========================================================================

    private function configurarConexion(int|string $anio): void
    {
        // Toma las credenciales de la conexion 'nacion' ya configurada en database.php
        // y solo cambia el nombre de la base de datos
        $base = config('database.connections.nacion');
        $base['database'] = "ra_carga{$anio}";

        Config::set("database.connections.{$this->conexion}", $base);

        // Purgar por si el comando se corre varias veces en el mismo proceso
        DB::purge($this->conexion);
    }

    // =========================================================================
    // El resto de los métodos es idéntico a MigrarRaCarga2025
    // solo que usan $this->schema y $this->conexion en lugar de las constantes
    // =========================================================================

    private function prepararEsquemaDestino(): void
    {
        $s = $this->schema;
        DB::statement("CREATE SCHEMA IF NOT EXISTS {$s}");
        DB::statement("DROP TABLE IF EXISTS {$s}.localizacion CASCADE");
        DB::statement("CREATE TABLE {$s}.localizacion (
            id_localizacion     integer           NOT NULL,
            cueanexo            character varying NOT NULL,
            establecimiento     character varying,
            estado_cuadernillo  character varying)");
        DB::statement("DROP TABLE IF EXISTS {$s}.detalle_cuadro CASCADE");
        DB::statement("CREATE TABLE {$s}.detalle_cuadro (
            c_oferta                    integer,
            id_definicion_cuadro        integer NOT NULL,
            numero                      character varying,
            nombre                      character varying,
            descripcion                 character varying,
            cuadernillo                 character varying,
            id_definicion_cuadernillo   integer)");
        DB::statement("DROP TABLE IF EXISTS {$s}.estado_carga_nivel CASCADE");
        DB::statement("CREATE TABLE {$s}.estado_carga_nivel (
            id_localizacion     integer NOT NULL,
            estado_localizacion character varying,
            cuadernillo         character varying,
            estado_cuadernillo  character varying,
            nivel               character varying)");
        DB::statement("DROP TABLE IF EXISTS {$s}.estado_carga CASCADE");
        DB::statement("CREATE TABLE {$s}.estado_carga (
            id_localizacion     integer NOT NULL,
            cuadernillo         character varying,
            estado              character varying)");
        $this->line('  Esquema y tablas fijas listas.');
    }

    private function migrarLocalizacion(?MigracionEtl $etl, int $porcentajeBase): void
    {
        $total = DB::connection($this->conexion)->table('localizacion')->count();
        $bar   = $this->output->createProgressBar($total);
        $bar->start();
        $procesados = 0;

        DB::connection($this->conexion)
            ->table('localizacion as l')
            ->join('estado_type as et', 'et.c_estado', '=', 'l.c_estado')
            ->select('l.id_localizacion', 'l.cueanexo', 'l.nombre as establecimiento', 'et.nombre as estado_cuadernillo')
            ->orderBy('l.id_localizacion')
            ->chunk(self::CHUNK, function ($filas) use (&$procesados, $total, $etl, $bar, $porcentajeBase) {
                $datos = $filas->map(fn ($f) => [
                    'id_localizacion'    => $f->id_localizacion,
                    'cueanexo'           => $f->cueanexo,
                    'establecimiento'    => $f->establecimiento,
                    'estado_cuadernillo' => $f->estado_cuadernillo,
                ])->all();
                DB::table($this->schema . '.localizacion')->insert($datos);
                $procesados += count($datos);
                $bar->advance(count($datos));
                $etl?->actualizarProgreso(
                    procesados: $porcentajeBase + (int)(($procesados / max($total, 1)) * 10),
                    total: 100);
            });

        $bar->finish();
        $this->newLine();
        $this->line("  {$procesados} registros insertados.");
    }

    private function migrarDetalleCuadro(?MigracionEtl $etl, int $porcentajeBase): void
    {
        $sql = "
            SELECT DISTINCT
                CASE
                    WHEN color ILIKE 'Verde'   THEN 115
                    WHEN color ILIKE 'Naranja'  THEN 146
                    WHEN color ILIKE 'Rosa'     THEN 123
                    WHEN color ILIKE 'Blanco'   THEN 153
                    WHEN color ILIKE 'Violeta'  THEN
                        CASE
                            WHEN dc.descripcion ILIKE '%Alfabetización%'             THEN 139
                            WHEN dc.descripcion ILIKE '%Nivel Primario%'              THEN 140
                            WHEN dc.descripcion ILIKE '%Secundario/Medio/Polimodal%'  THEN 144
                        END
                    WHEN color ILIKE 'Celeste'  THEN
                        CASE
                            WHEN dc.descripcion ILIKE '%Jardín Maternal%'  THEN 100
                            WHEN dc.descripcion ILIKE '%Nivel Inicial%'    THEN 101
                            WHEN dc.descripcion ILIKE '%Nivel Primario%'   THEN 102
                            WHEN dc.descripcion ILIKE '%Secundaria/Medio%' THEN 110
                        END
                END AS c_oferta,
                dcu.id_definicion_cuadro, defcu.numero, defcu.nombre,
                dc.descripcion, ddcill.color AS cuadernillo, datcill.id_definicion_cuadernillo
            FROM datos_cuadernillo datcill
            INNER JOIN definicion_cuadernillo ddcill ON ddcill.id_definicion_cuadernillo = datcill.id_definicion_cuadernillo
            INNER JOIN datos_capitulo         datcap ON datcap.id_datos_cuadernillo      = datcill.id_datos_cuadernillo
            INNER JOIN definicion_capitulo    dc     ON dc.id_definicion_capitulo         = datcap.id_definicion_capitulo
            INNER JOIN datos_cuadro           dcu    ON dcu.id_datos_capitulo             = datcap.id_datos_capitulo
            INNER JOIN definicion_cuadro      defcu  ON defcu.id_definicion_cuadro        = dcu.id_definicion_cuadro
            ORDER BY datcill.id_definicion_cuadernillo ASC";

        $this->procesarQueryCompleta($sql, $this->schema . '.detalle_cuadro',
            fn ($f) => [
                'c_oferta'                  => $f->c_oferta,
                'id_definicion_cuadro'      => $f->id_definicion_cuadro,
                'numero'                    => $f->numero,
                'nombre'                    => $f->nombre,
                'descripcion'               => $f->descripcion,
                'cuadernillo'               => $f->cuadernillo,
                'id_definicion_cuadernillo' => $f->id_definicion_cuadernillo,
            ], $etl, $porcentajeBase);
    }

    private function migrarEstadoCargaNivel(?MigracionEtl $etl, int $porcentajeBase): void
    {
        $sql = "
            SELECT id_localizacion, estado_localizacion, cuadernillo, estado_cuadernillo, nivel
            FROM (
                SELECT l.id_localizacion,
                    CASE
                        WHEN l.c_estado = 0    THEN 'Faltante'    WHEN l.c_estado = 10   THEN 'Recibido'
                        WHEN l.c_estado = 20   THEN 'Vacío'       WHEN l.c_estado = 30   THEN 'En carga'
                        WHEN l.c_estado = 40   THEN 'En carga con inconsistencias'
                        WHEN l.c_estado = 50   THEN 'En carga con errores'
                        WHEN l.c_estado = 60   THEN 'Sin Información'
                        WHEN l.c_estado = 70   THEN 'Completo con inconsistencias'
                        WHEN l.c_estado = 80   THEN 'Completo con errores'
                        WHEN l.c_estado = 90   THEN 'Completo'    WHEN l.c_estado = 99   THEN 'Modificado'
                        WHEN l.c_estado = 100  THEN 'Verificado'  WHEN l.c_estado = 1000 THEN 'Confirmado'
                    END AS estado_localizacion,
                    ddcill.nombre AS cuadernillo, et.nombre AS estado_cuadernillo,
                    CASE
                        WHEN defcu.id_definicion_cuadro = 104  THEN 'INICIAL'
                        WHEN defcu.id_definicion_cuadro = 626  THEN 'PRIMARIO'
                        WHEN defcu.id_definicion_cuadro = 158  THEN 'SECUNDARIO'
                        WHEN defcu.id_definicion_cuadro = 287  THEN 'SUPERIOR'
                        WHEN defcu.id_definicion_cuadro = 518  THEN 'ADULTO ALFABETIZACIÓN'
                        WHEN defcu.id_definicion_cuadro = 297  THEN 'ADULTO PRIMARIO'
                        WHEN defcu.id_definicion_cuadro = 525  THEN 'ADULTO SECUNDARIO'
                        WHEN defcu.id_definicion_cuadro = 298  THEN 'FORMACIÓN PROFESIONAL'
                        WHEN defcu.id_definicion_cuadro = 667  THEN 'ESPECIAL'
                        WHEN defcu.id_definicion_cuadro = 674  THEN 'HOSPITALARIO'
                        WHEN defcu.id_definicion_cuadro = 256  THEN 'SERVICIOS COMPLEMENTARIOS'
                        WHEN defcu.id_definicion_cuadro = 262  THEN 'SECUNDARIO ARTÍSTICA'
                        WHEN defcu.id_definicion_cuadro = 780  THEN 'CARACTERÍSTICAS DEL ESTABLECIMIENTO'
                        ELSE ''
                    END AS nivel
                FROM datos_cuadernillo datcill
                INNER JOIN definicion_cuadernillo ddcill ON ddcill.id_definicion_cuadernillo = datcill.id_definicion_cuadernillo
                INNER JOIN datos_capitulo         datcap ON datcap.id_datos_cuadernillo      = datcill.id_datos_cuadernillo
                INNER JOIN datos_cuadro           dcu    ON dcu.id_datos_capitulo            = datcap.id_datos_capitulo
                INNER JOIN definicion_cuadro      defcu  ON defcu.id_definicion_cuadro       = dcu.id_definicion_cuadro
                INNER JOIN localizacion           l      ON l.id_localizacion                = datcill.id_localizacion
                INNER JOIN estado_type            et     ON et.c_estado                      = datcill.c_estado
                ORDER BY ddcill.nombre ASC
            ) sub
            WHERE nivel <> ''
            ORDER BY cuadernillo ASC";

        $this->procesarQueryCompleta($sql, $this->schema . '.estado_carga_nivel',
            fn ($f) => [
                'id_localizacion'     => $f->id_localizacion,
                'estado_localizacion' => $f->estado_localizacion,
                'cuadernillo'         => $f->cuadernillo,
                'estado_cuadernillo'  => $f->estado_cuadernillo,
                'nivel'               => $f->nivel,
            ], $etl, $porcentajeBase);
    }

    private function migrarEstadoCarga(?MigracionEtl $etl, int $porcentajeBase): void
    {
        $sql = "
            SELECT datcill.id_localizacion, ddcill.nombre AS cuadernillo, et.nombre AS estado
            FROM datos_cuadernillo datcill
            INNER JOIN estado_type            et     ON et.c_estado                      = datcill.c_estado
            INNER JOIN definicion_cuadernillo ddcill ON ddcill.id_definicion_cuadernillo = datcill.id_definicion_cuadernillo
            ORDER BY datcill.id_localizacion, ddcill.nombre ASC";

        $this->procesarQueryCompleta($sql, $this->schema . '.estado_carga',
            fn ($f) => [
                'id_localizacion' => $f->id_localizacion,
                'cuadernillo'     => $f->cuadernillo,
                'estado'          => $f->estado,
            ], $etl, $porcentajeBase);
    }

    private function migrarCuadrosDinamicos(?MigracionEtl $etl, int $porcentajeBase): void
    {
        $estadosVacios = implode(',', self::ESTADOS_VACIOS);

        $cuadros = DB::connection($this->conexion)->select("
            SELECT DISTINCT datos_cuadro.id_definicion_cuadro, definicion_cuadernillo.color
            FROM datos_cuadro
            INNER JOIN definicion_cuadro      ON definicion_cuadro.id_definicion_cuadro          = datos_cuadro.id_definicion_cuadro
            INNER JOIN datos_capitulo         ON datos_capitulo.id_datos_capitulo                 = datos_cuadro.id_datos_capitulo
            INNER JOIN datos_cuadernillo      ON datos_cuadernillo.id_datos_cuadernillo           = datos_capitulo.id_datos_cuadernillo
            INNER JOIN definicion_cuadernillo ON definicion_cuadernillo.id_definicion_cuadernillo = datos_cuadernillo.id_definicion_cuadernillo
            WHERE datos_cuadro.c_estado NOT IN ({$estadosVacios})
            ORDER BY id_definicion_cuadro ASC");

        $total = count($cuadros);
        $this->line("  {$total} cuadros a generar.");

        $bar = $this->output->createProgressBar($total);
        $bar->start();
        $procesados = 0;

        foreach ($cuadros as $cuadro) {
            $this->procesarCuadro($cuadro);
            $procesados++;
            $bar->advance();
            $etl?->actualizarProgreso(
                procesados: $porcentajeBase + (int)(($procesados / max($total, 1)) * 60),
                total: 100);
        }

        $bar->finish();
        $this->newLine();
        $this->line("  {$procesados} tablas de cuadros generadas.");
    }

    private function procesarCuadro(object $cuadro): void
    {
        $schema      = $this->schema;
        $idCuadro    = $cuadro->id_definicion_cuadro;
        $color       = $cuadro->color;
        $nombreTabla = "{$color}_{$idCuadro}";

        $columnas = DB::connection($this->conexion)->select("
            SELECT definicion_columna.nombre_corto AS columna,
                   defcuadro_defcolumna.orden,
                   definicion_columna.c_tipo_dato,
                   tipo_dato_type.nombre AS tipo_dato
            FROM defcuadro_defcolumna
            INNER JOIN definicion_columna ON definicion_columna.id_definicion_columna = defcuadro_defcolumna.id_definicion_columna
            INNER JOIN tipo_dato_type     ON tipo_dato_type.c_tipo_dato               = definicion_columna.c_tipo_dato
            WHERE defcuadro_defcolumna.id_definicion_cuadro = ?
            ORDER BY defcuadro_defcolumna.orden ASC", [$idCuadro]);

        $columnas = array_filter($columnas, fn ($c) => trim(str_replace(' ', '', $c->columna)) !== '');

        if (empty($columnas)) {
            return;
        }

        DB::statement("DROP TABLE IF EXISTS \"{$schema}\".\"{$nombreTabla}\" CASCADE");

        $columnasDestino = array_map(function ($col) {
            $nombreSeguro = $this->sanitizarColumna($col->columna);
            $tipo = ($col->c_tipo_dato < 3) ? $col->tipo_dato : 'character varying';
            return "\"{$nombreSeguro}\" {$tipo}";
        }, $columnas);

        DB::statement("
            CREATE TABLE \"{$schema}\".\"{$nombreTabla}\" (
                id_localizacion integer,
                fila            text,
                " . implode(', ', $columnasDestino) . ")");

        $tiposCV    = implode(',', self::TIPOS_CODIGO_VALOR);
        $pivotParts = array_map(function ($col) {
            $nombreOriginal = addslashes($col->columna);
            $nombreSeguro   = $this->sanitizarColumna($col->columna);
            return "MAX(CASE WHEN columna = '{$nombreOriginal}' THEN valor ELSE NULL END) AS \"{$nombreSeguro}\"";
        }, $columnas);

        $sqlDatos = "
            SELECT id_localizacion, fila, " . implode(",\n", $pivotParts) . "
            FROM (
                SELECT loc.id_localizacion, def_col.nombre_corto AS columna,
                       def_fil.nombre AS fila,
                    CASE
                        WHEN def_col.c_tipo_dato IN ({$tiposCV}) THEN cod_val.descripcion
                        ELSE CASE
                            WHEN dat_cel.valor::text = 'ficticio generado' THEN NULL
                            WHEN dat_cel.valor::text = 'Cuadros con error que dependen de datos de este cuadro  Cuadro M.1, presenta Error 1420' THEN NULL
                            ELSE dat_cel.valor
                        END
                    END AS valor
                FROM public.localizacion loc
                INNER JOIN datos_cuadernillo  dat_c   ON dat_c.id_localizacion        = loc.id_localizacion
                INNER JOIN datos_capitulo     dat_cap ON dat_cap.id_datos_cuadernillo  = dat_c.id_datos_cuadernillo
                INNER JOIN datos_cuadro       dat_cua ON dat_cua.id_datos_capitulo     = dat_cap.id_datos_capitulo
                INNER JOIN datos_celda        dat_cel ON dat_cel.id_datos_cuadro       = dat_cua.id_datos_cuadro
                LEFT  JOIN definicion_celda   def_cel ON def_cel.id_definicion_celda   = dat_cel.id_definicion_celda
                INNER JOIN definicion_columna def_col ON def_col.id_definicion_columna = def_cel.id_definicion_columna
                INNER JOIN definicion_fila    def_fil ON def_fil.id_definicion_fila    = def_cel.id_definicion_fila
                LEFT  JOIN codigo_valor       cod_val ON cod_val.id_codigo_valor::text = dat_cel.valor
                WHERE dat_cua.id_definicion_cuadro = {$idCuadro}
                ORDER BY loc.id_localizacion, def_fil.nombre, def_col.nombre ASC
            ) AS sub
            GROUP BY id_localizacion, fila";

        $filas = DB::connection($this->conexion)->select($sqlDatos);

        if (empty($filas)) {
            return;
        }

        $columnaKeys = array_map(fn ($c) => $this->sanitizarColumna($c->columna), $columnas);

        foreach (array_chunk($filas, self::CHUNK) as $lote) {
            $datos = array_map(function ($f) use ($columnaKeys) {
                $fila = ['id_localizacion' => $f->id_localizacion, 'fila' => $f->fila];
                foreach ($columnaKeys as $col) {
                    $fila[$col] = $f->$col ?? null;
                }
                return $fila;
            }, $lote);

            DB::table(DB::raw("\"{$schema}\".\"{$nombreTabla}\""))->insert($datos);
        }
    }

    private function procesarQueryCompleta(
        string $sql, string $tablaDestino, callable $mapeo,
        ?MigracionEtl $etl, int $porcentajeBase): void
    {
        $this->line('  Ejecutando consulta en nacion...');
        $filas = DB::connection($this->conexion)->select($sql);
        $total = count($filas);
        $this->line("  {$total} registros obtenidos. Insertando en planeamiento...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();
        $procesados = 0;

        foreach (array_chunk($filas, self::CHUNK) as $lote) {
            $datos = array_map($mapeo, $lote);
            DB::table($tablaDestino)->insert($datos);
            $procesados += count($lote);
            $bar->advance(count($lote));
            $etl?->actualizarProgreso(
                procesados: $porcentajeBase + (int)(($procesados / max($total, 1)) * 10),
                total: 100);
        }

        $bar->finish();
        $this->newLine();
        $this->line("  {$procesados} registros insertados en {$tablaDestino}.");
    }

    private function sanitizarColumna(string $nombre): string
    {
        return trim(preg_replace('/[.\s]+/', '_', $nombre), '_');
    }

    private function resolverEtl(): ?MigracionEtl
    {
        $id = $this->option('etl-id') ?? null;
        return $id ? MigracionEtl::find($id) : null;
    }
}