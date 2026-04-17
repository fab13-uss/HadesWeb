<?php

namespace App\Console\Commands;

use App\Models\MigracionEtl;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

#[Signature('migrar:padron {--etl-id= : ID en migraciones_etl para reportar progreso}')]
#[Description('Migra las tablas del padrón (nacion/padron) al esquema padron en planeamiento')]
class MigrarPadron extends Command
{
    private const SCHEMA     = 'padron';
    private const CONEXION   = 'nacion_padron';
    private const CHUNK      = 500;

    // Año de inicio del histórico — igual que el código original
    private const ANIO_INICIO = 1997;

    public function handle(): int
    {
        $etl = $this->resolverEtl();

        try {
            $this->info('── Paso 1/7: Preparando esquema destino en planeamiento...');
            $this->prepararEsquemaDestino();

            $this->info('── Paso 2/7: Migrando localizaciones...');
            $this->migrarLocalizaciones($etl, porcentajeBase: 0);

            $this->info('── Paso 3/7: Migrando oferta_local...');
            $this->migrarOfertaLocal($etl, porcentajeBase: 15);

            $this->info('── Paso 4/7: Migrando domicilio...');
            $this->migrarDomicilio($etl, porcentajeBase: 30);

            $this->info('── Paso 5/7: Migrando cambio_estado_localizacion...');
            $this->migrarCambioEstadoLocalizacion($etl, porcentajeBase: 45);

            $this->info('── Paso 6/7: Migrando cambio_estado_oferta_local...');
            $this->migrarCambioEstadoOfertaLocal($etl, porcentajeBase: 60);

            $this->info('── Paso 7/7: Migrando historico_ofertas_locales...');
            $this->migrarHistoricoOfertasLocales($etl, porcentajeBase: 75);

            $etl?->marcarCompletado(registros: 0);
            $this->info('✓ Migración padrón completada.');
            return self::SUCCESS;

        } catch (Throwable $e) {
            $etl?->marcarError($e->getMessage());
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    // =========================================================================
    // Paso 1 — Esquema y tablas destino
    // =========================================================================

    private function prepararEsquemaDestino(): void
    {
        $s = self::SCHEMA;

        DB::statement("CREATE SCHEMA IF NOT EXISTS {$s}");

        // --- localizaciones ---
        DB::statement("DROP TABLE IF EXISTS {$s}.localizaciones CASCADE");
        DB::statement("
            CREATE TABLE {$s}.localizaciones (
                id_establecimiento      integer,
                id_localizacion         integer         NOT NULL,
                cue                     character varying NOT NULL,
                anexo                   character varying,
                del_zonal               character varying,
                dependencia             character varying,
                codigo_jurisdiccional   character varying,
                nombre                  character varying,
                estado                  character varying,
                director                text,
                sexo                    character varying,
                dni                     integer,
                cuil_cuit               character(13),
                telefono_director       character varying,
                mail_director           character varying,
                fecha_nacimiento        date,
                sede                    character varying,
                sede_administrativa     character varying,
                sector                  character varying,
                ambito                  character varying,
                telefono_cod_area       character varying,
                telefono                character varying,
                email                   character varying,
                sitio_web               character varying,
                cooperadora             character varying,
                observaciones           text,
                fecha_creacion          date,
                fecha_baja              date
            )
        ");

        // --- oferta_local ---
        DB::statement("DROP TABLE IF EXISTS {$s}.oferta_local CASCADE");
        DB::statement("
            CREATE TABLE {$s}.oferta_local (
                id_oferta_local         integer         NOT NULL,
                id_localizacion         integer         NOT NULL,
                c_oferta                smallint        NOT NULL,
                descripcion_oferta      character varying NOT NULL,
                estado                  character varying,
                modalidad               character varying,
                subvencion              character varying,
                jornada                 character varying,
                fecha_creacion          date,
                fecha_alta              date,
                fecha_actualizacion     timestamp without time zone,
                fecha_baja              date
            )
        ");

        // --- domicilio ---
        DB::statement("DROP TABLE IF EXISTS {$s}.domicilio CASCADE");
        DB::statement("
            CREATE TABLE {$s}.domicilio (
                id_localizacion         integer         NOT NULL,
                id_domicilio            integer         NOT NULL,
                calle                   character varying,
                nro                     character varying,
                barrio                  character varying,
                referencia              character varying,
                cod_postal              character varying,
                cui                     character varying,
                localidad               character varying,
                departamento            character varying,
                calle_fondo             character varying,
                calle_derecha           character varying,
                calle_izquierda         character varying,
                x_longitud              double precision,
                y_latitud               double precision,
                fecha_actualizacion     timestamp without time zone
            )
        ");

        // --- cambio_estado_localizacion ---
        DB::statement("DROP TABLE IF EXISTS {$s}.cambio_estado_localizacion CASCADE");
        DB::statement("
            CREATE TABLE {$s}.cambio_estado_localizacion (
                id_localizacion         integer,
                codigo_jurisdiccional   character varying,
                localizacion            character varying,
                campo_prov_del_zonal    text,
                campo_prov_eib          text,
                campo_prov_dependencia  text,
                campo_prov_es_var4      text,
                campo_prov_cue_anterior text,
                calle                   character varying,
                nro                     character varying,
                barrio                  character varying,
                referencia              character varying,
                localidad               character varying,
                tipo_movimiento         character varying,
                instrumento_legal       character varying,
                nro_instr_legal         character varying,
                observacion             character varying,
                motivo                  character varying,
                fecha_alta              date,
                fecha_baja              date,
                fecha_actualizacion     timestamp without time zone,
                usuario                 character varying,
                c_estado                smallint
            )
        ");

        // --- cambio_estado_oferta_local ---
        DB::statement("DROP TABLE IF EXISTS {$s}.cambio_estado_oferta_local CASCADE");
        DB::statement("
            CREATE TABLE {$s}.cambio_estado_oferta_local (
                id_oferta_local         integer,
                id_localizacion         integer,
                campo_prov_del_zonal    text,
                campo_prov_cua          text,
                campo_prov_modalidad    text,
                campo_prov_es_var6      text,
                campo_prov_cod_jur      text,
                codigo_jurisdiccional   character varying,
                c_oferta                smallint,
                estado_movimiento       character varying,
                tipo_movimiento         character varying,
                instrumento_legal       character varying,
                nro_instr_legal         character varying,
                observacion             character varying,
                motivo                  character varying,
                fecha_actualizacion     timestamp without time zone,
                usuario                 character varying,
                c_estado                smallint
            )
        ");

        // --- historico_ofertas_locales (columnas dinámicas por año) ---
        DB::statement("DROP TABLE IF EXISTS {$s}.historico_ofertas_locales CASCADE");
        $columnas = $this->columnasHistorico();
        DB::statement("
            CREATE TABLE {$s}.historico_ofertas_locales (
                id_oferta_local integer NOT NULL,
                c_oferta        integer NOT NULL,
                {$columnas}
            )
        ");

        // --- orden_oferta y anios_estudio (estructura, datos via Seeder) ---
        DB::statement("DROP TABLE IF EXISTS {$s}.orden_oferta CASCADE");
        DB::statement("
            CREATE TABLE {$s}.orden_oferta (
                fila                bigint          NOT NULL,
                c_oferta            smallint,
                descripcion_oferta  character varying
            )
        ");

        DB::statement("DROP TABLE IF EXISTS {$s}.anios_estudio CASCADE");
        DB::statement("
            CREATE TABLE {$s}.anios_estudio (
                fila        bigint          NOT NULL,
                curso_1     character varying,
                curso_2     character varying,
                nivel       integer
            )
        ");

        $this->line('  Esquema y tablas listas.');
    }

    // =========================================================================
    // Paso 2 — localizaciones
    // =========================================================================

    private function migrarLocalizaciones(?MigracionEtl $etl, int $porcentajeBase): void
    {
        $sql = "
            SELECT
                e.id_establecimiento,
                l.id_localizacion,
                e.cue,
                l.anexo,
                val.valor                                               AS del_zonal,
                dep.valor                                               AS dependencia,
                l.codigo_jurisdiccional,
                UPPER(l.nombre)                                         AS nombre,
                UPPER(est.descripcion)                                  AS estado,
                UPPER(r.apellido) || ', ' || UPPER(r.nombre)           AS director,
                UPPER(sx.descripcion)                                   AS sexo,
                r.nro_documento                                         AS dni,
                r.cuil_cuit,
                r.telefono                                              AS telefono_director,
                r.email                                                 AS mail_director,
                r.fecha_nacimiento,
                CASE WHEN l.sede               = true THEN 'SI' ELSE 'NO' END AS sede,
                CASE WHEN l.sede_administrativa = true THEN 'SI' ELSE 'NO' END AS sede_administrativa,
                CASE WHEN sec.descripcion = 'Gestión social/cooperativa'
                     THEN 'ESTATAL'
                     ELSE UPPER(sec.descripcion) END                   AS sector,
                CASE WHEN amb.descripcion IN ('Rural Aglomerado','Rural Disperso')
                     THEN 'RURAL'
                     ELSE UPPER(amb.descripcion) END                   AS ambito,
                l.telefono_cod_area,
                l.telefono,
                l.email,
                l.sitio_web,
                coop.descripcion                                        AS cooperadora,
                l.observaciones,
                l.fecha_creacion,
                l.fecha_baja
            FROM localizacion l
            LEFT JOIN establecimiento       e    ON e.id_establecimiento  = l.id_establecimiento
            LEFT JOIN responsable           r    ON r.id_responsable       = l.id_responsable
            LEFT JOIN sexo_tipo             sx   ON sx.c_sexo              = r.c_sexo
            LEFT JOIN sector_tipo           sec  ON sec.c_sector           = e.c_sector
            LEFT JOIN ambito_tipo           amb  ON amb.c_ambito           = l.c_ambito
            LEFT JOIN cooperadora_tipo      coop ON coop.c_cooperadora     = l.c_cooperadora
            LEFT JOIN estado_tipo           est  ON est.c_estado           = l.c_estado
            LEFT JOIN loc_campo_prov_valor  val  ON val.id_localizacion    = l.id_localizacion
                                                AND val.id_campo_prov      = 1214432500
            LEFT JOIN loc_campo_prov_valor  dep  ON dep.id_localizacion    = l.id_localizacion
                                                AND dep.id_campo_prov      = 1214432502
            ORDER BY e.cue, l.anexo ASC
        ";

        $this->procesarQueryCompleta(
            sql: $sql,
            tablaDestino: self::SCHEMA . '.localizaciones',
            mapeo: fn ($f) => [
                'id_establecimiento'    => $f->id_establecimiento,
                'id_localizacion'       => $f->id_localizacion,
                'cue'                   => $f->cue,
                'anexo'                 => $f->anexo,
                'del_zonal'             => $f->del_zonal,
                'dependencia'           => $f->dependencia,
                'codigo_jurisdiccional' => $f->codigo_jurisdiccional,
                'nombre'                => $f->nombre,
                'estado'                => $f->estado,
                'director'              => $f->director,
                'sexo'                  => $f->sexo,
                'dni'                   => $f->dni,
                'cuil_cuit'             => $f->cuil_cuit,
                'telefono_director'     => $f->telefono_director,
                'mail_director'         => $f->mail_director,
                'fecha_nacimiento'      => $f->fecha_nacimiento,
                'sede'                  => $f->sede,
                'sede_administrativa'   => $f->sede_administrativa,
                'sector'                => $f->sector,
                'ambito'                => $f->ambito,
                'telefono_cod_area'     => $f->telefono_cod_area,
                'telefono'              => $f->telefono,
                'email'                 => $f->email,
                'sitio_web'             => $f->sitio_web,
                'cooperadora'           => $f->cooperadora,
                'observaciones'         => $f->observaciones,
                'fecha_creacion'        => $f->fecha_creacion,
                'fecha_baja'            => $f->fecha_baja,
            ],
            etl: $etl,
            porcentajeBase: $porcentajeBase
        );
    }

    // =========================================================================
    // Paso 3 — oferta_local
    // =========================================================================

    private function migrarOfertaLocal(?MigracionEtl $etl, int $porcentajeBase): void
    {
        $sql = "
            SELECT
                ol.id_oferta_local,
                ol.id_localizacion,
                CASE
                    WHEN ol.c_oferta IN (152,153,154,155,156,157)          THEN 153
                    WHEN ol.c_oferta IN (121,122,123,129,134,135,136,170,171) THEN 123
                    WHEN ol.c_oferta = 108                                  THEN 110
                    ELSE ol.c_oferta
                END                                                     AS c_oferta,
                CASE
                    WHEN ol.c_oferta IN (152,153,154,155,156,157)          THEN 'HOSPITALARIA'
                    WHEN ol.c_oferta IN (121,122,123,129,134,135,136,170,171) THEN 'ESPECIAL'
                    ELSE UPPER(ot.descripcion)
                END                                                     AS descripcion_oferta,
                UPPER(est.descripcion)                                  AS estado,
                CASE
                    WHEN ocp.valor IN ('CDI - Maternal')  AND ol.c_oferta = 100  THEN 'CDI'
                    WHEN ocp.valor IN ('Común')           AND ol.c_oferta = 100  THEN 'COMÚN'
                    WHEN ocp.valor IN ('CDI - Inicial')   AND ol.c_oferta = 101  THEN 'CDI'
                    WHEN ocp.valor IN ('común','Común')   AND ol.c_oferta = 101  THEN 'COMÚN'
                    WHEN ocp.valor IN ('EIB')             AND ol.c_oferta = 101  THEN 'EIB'
                    WHEN ocp.valor IN ('Rural')           AND ol.c_oferta = 101  THEN 'RURAL'
                    WHEN ocp.valor IN ('Común')           AND ol.c_oferta = 102  THEN 'COMÚN'
                    WHEN ocp.valor IN ('EIB')             AND ol.c_oferta = 102  THEN 'EIB'
                    WHEN ocp.valor IN ('Rural')           AND ol.c_oferta = 102  THEN 'RURAL'
                    WHEN ocp.valor IN ('Agro')            AND ol.c_oferta IN (108,110) THEN 'AGROTÉCNICA'
                    WHEN ocp.valor IN ('Agro / eib')      AND ol.c_oferta IN (108,110) THEN 'AGROTÉCNICA EIB'
                    WHEN ocp.valor IN ('Artística')       AND ol.c_oferta IN (108,110) THEN 'ARTÍSTICA'
                    WHEN ocp.valor IN ('Común')           AND ol.c_oferta IN (108,110) THEN 'COMÚN'
                    WHEN ocp.valor IN ('EIB')             AND ol.c_oferta IN (108,110) THEN 'EIB'
                    WHEN ocp.valor IN ('Epes-Rural')      AND ol.c_oferta IN (108,110) THEN 'EPES RURAL'
                    WHEN ocp.valor IN ('Rural')           AND ol.c_oferta IN (108,110) THEN 'RURAL'
                    WHEN ocp.valor IN ('Técnica')         AND ol.c_oferta IN (108,110) THEN 'TÉCNICO PROFESIONAL'
                    WHEN ocp.valor IN ('Común')           AND ol.c_oferta = 115  THEN 'COMÚN'
                    WHEN ocp.valor IN ('EIB')             AND ol.c_oferta = 115  THEN 'EIB'
                    WHEN ocp.valor IN ('Común')           AND ol.c_oferta = 139  THEN 'COMÚN'
                    WHEN ocp.valor IN ('Contexto de Encierro') AND ol.c_oferta = 139 THEN 'CONTEXTO DE ENCIERRO'
                    WHEN ocp.valor IN ('Permanente Primario')  AND ol.c_oferta = 139 THEN 'PERMANENTE PRIMARIO'
                    WHEN ocp.valor IN ('Común')           AND ol.c_oferta = 140  THEN 'COMÚN'
                    WHEN ocp.valor IN ('Contexto de Encierro') AND ol.c_oferta = 140 THEN 'CONTEXTO DE ENCIERRO'
                    WHEN ocp.valor IN ('Permanente Primario')  AND ol.c_oferta = 140 THEN 'PERMANENTE PRIMARIO'
                    WHEN ocp.valor IN ('Permanente Primario / eib') AND ol.c_oferta = 140 THEN 'PERMANENTE PRIMARIO EIB'
                    WHEN ocp.valor IN ('Común')           AND ol.c_oferta = 144  THEN 'COMÚN'
                    WHEN ocp.valor IN ('Contexto de Encierro')  AND ol.c_oferta = 144 THEN 'CONTEXTO DE ENCIERRO'
                    WHEN ocp.valor IN ('Permanente Secundario') AND ol.c_oferta = 144 THEN 'PERMANENTE SECUNDARIO'
                    WHEN ocp.valor IN ('Permanente Secundario / eib') AND ol.c_oferta = 144 THEN 'PERMANENTE SECUNDARIO EIB'
                    WHEN ocp.valor IN ('Común')           AND ol.c_oferta = 146  THEN 'COMÚN'
                    WHEN ocp.valor IN ('Contexto de Encierro') AND ol.c_oferta = 146 THEN 'CONTEXTO DE ENCIERRO'
                    WHEN ocp.valor IN ('Permanente Primario')  AND ol.c_oferta = 146 THEN 'PERMANENTE PRIMARIO'
                    WHEN ocp.valor IN ('Permanente Secundario') AND ol.c_oferta = 146 THEN 'PERMANENTE SECUNDARIO'
                    WHEN ocp.valor IN ('Técnica')         AND ol.c_oferta = 146  THEN 'TÉCNICO PROFESIONAL'
                    WHEN ocp.valor IN ('Técnica / eib')   AND ol.c_oferta = 146  THEN 'TÉCNICO PROFESIONAL EIB'
                    WHEN ocp.valor IN ('Especial')                               THEN 'ESPECIAL'
                    WHEN ocp.valor IN ('Hospitalaria','Hospitalaria  ')          THEN 'HOSPITALARIA'
                    ELSE ocp.valor
                END                                                     AS modalidad,
                UPPER(sub.descripcion)                                  AS subvencion,
                UPPER(jor.descripcion)                                  AS jornada,
                ol.fecha_creacion,
                ol.fecha_alta,
                ol.fecha_actualizacion,
                ol.fecha_baja
            FROM oferta_local ol
            LEFT JOIN oferta_tipo           ot  ON ot.c_oferta         = ol.c_oferta
            LEFT JOIN estado_tipo           est ON est.c_estado         = ol.c_estado
            LEFT JOIN subvencion_tipo       sub ON sub.c_subvencion     = ol.c_subvencion
            LEFT JOIN jornada_tipo          jor ON jor.c_jornada        = ol.c_jornada
            LEFT JOIN oloc_campo_prov_valor ocp ON ocp.id_oferta_local  = ol.id_oferta_local
                                               AND ocp.id_campo_prov    = 1214432513
        ";

        $this->procesarQueryCompleta(
            sql: $sql,
            tablaDestino: self::SCHEMA . '.oferta_local',
            mapeo: fn ($f) => [
                'id_oferta_local'    => $f->id_oferta_local,
                'id_localizacion'    => $f->id_localizacion,
                'c_oferta'           => $f->c_oferta,
                'descripcion_oferta' => $f->descripcion_oferta,
                'estado'             => $f->estado,
                'modalidad'          => $f->modalidad,
                'subvencion'         => $f->subvencion,
                'jornada'            => $f->jornada,
                'fecha_creacion'     => $f->fecha_creacion,
                'fecha_alta'         => $f->fecha_alta,
                'fecha_actualizacion'=> $f->fecha_actualizacion,
                'fecha_baja'         => $f->fecha_baja,
            ],
            etl: $etl,
            porcentajeBase: $porcentajeBase
        );
    }

    // =========================================================================
    // Paso 4 — domicilio
    // =========================================================================

    private function migrarDomicilio(?MigracionEtl $etl, int $porcentajeBase): void
    {
        // El CASE de normalización de barrios se mantiene idéntico al original
        $sql = "
            SELECT
                l.id_localizacion,
                d.id_domicilio,
                d.calle,
                d.nro,
                CASE
                    WHEN d.barrio IN (' 1 DE MAYO','1 DE MAYO','1°DE MAYO','B 1 DE MAYO MANZANA N 125 CASA 4','Bº 1º DE MAYO') THEN '1° DE MAYO'
                    WHEN d.barrio IN (' BºVIEJO') THEN 'VIEJO'
                    WHEN d.barrio IN ('1ºDE MAYO','1º DE MAYO') THEN '1° DE MAYO'
                    WHEN d.barrio IN ('7 de Noviembre') THEN '7 DE NOVIEMBRE'
                    WHEN d.barrio IN ('Alto') THEN 'ALTO'
                    WHEN d.barrio IN ('Antenor Gauna','ANTENOR GAUNA-MANZANA 32-CASA 19','Bº ANTENOR GAUNA') THEN 'ANTENOR GAUNA'
                    WHEN d.barrio IN ('B° LA PAZ','Bº LA PAZ','LA PÁZ') THEN 'LA PAZ'
                    WHEN d.barrio IN ('Bª EVA PERON','Bº EVA PERON') THEN 'EVA PERON'
                    WHEN d.barrio IN ('BARRIO COLLUCCIO') THEN 'COLLUCCIO'
                    WHEN d.barrio IN ('BARRIO EL SOL') THEN 'EL SOL'
                    WHEN d.barrio IN ('BARRIO FONTANA') THEN 'FONTANA'
                    WHEN d.barrio IN ('BARRIO NUEVO') THEN 'NUEVO'
                    WHEN d.barrio IN ('BARRIO SAN ANTONIO') THEN 'SAN ANTONIO'
                    WHEN d.barrio IN ('Bº 2 DE ABRIL') THEN '2 DE ABRIL'
                    WHEN d.barrio IN ('Bº CENTRO','centro','Centro') THEN 'CENTRO'
                    WHEN d.barrio IN ('Bº DIVINO NIÑO JESUS','DIVINO NIÑO EX LA COLONIA','Divino Niño Jesús','DIVINO NIÑO JESUS ') THEN 'DIVINO NIÑO JESUS'
                    WHEN d.barrio IN ('Bº DON BOSCO','Don Bosca') THEN 'DON BOSCO'
                    WHEN d.barrio IN ('Bº FLEMING') THEN 'FLEMING'
                    WHEN d.barrio IN ('Bº ILLIA I') THEN 'ILLIA I'
                    WHEN d.barrio IN ('Bº NAMQOM','Namqom','NANQOM') THEN 'NAMQOM'
                    WHEN d.barrio IN ('Bº SAN ANDRES I') THEN 'SAN ANDRES I'
                    WHEN d.barrio IN ('Bº SAN PEDRO') THEN 'SAN PEDRO'
                    WHEN d.barrio IN ('Bº UNIDO') THEN 'UNIDO'
                    WHEN d.barrio IN ('BO. CHINO') THEN 'CHINO'
                    WHEN d.barrio IN ('BO. VILLA HERMOSA') THEN 'VILLA HERMOSA'
                    WHEN d.barrio IN ('BºLA PLATA') THEN 'LA PLATA'
                    WHEN d.barrio IN ('C-24-JUAN MANUEL DE ROSAS') THEN 'JUAN MANUEL DE ROSAS'
                    WHEN d.barrio IN ('CAACUPÉ') THEN 'CAACUPE'
                    WHEN d.barrio IN ('COLLUCIO') THEN 'COLLUCCIO'
                    WHEN d.barrio IN ('EATANCIA EL ALGARROBO') THEN 'ESTANCIA EL ALGARROBO'
                    WHEN d.barrio IN ('EL PORTEÑO Mz. 23') THEN 'EL PORTEÑO'
                    WHEN d.barrio IN ('El Pucu','EL PUCU','El Pucú') THEN 'EL PUCÚ'
                    WHEN d.barrio IN ('EMILIO TOMÁS') THEN 'EMILIO TOMAS'
                    WHEN d.barrio IN ('HORNERO- POZO DEL TIGRE') THEN 'HORNERO'
                    WHEN d.barrio IN ('I.P.V') THEN 'I.P.V.'
                    WHEN d.barrio IN ('illia') THEN 'ILLIA'
                    WHEN d.barrio IN ('INDEPENENCIA') THEN 'INDEPENDENCIA'
                    WHEN d.barrio IN ('ITATÍ') THEN 'ITATI'
                    WHEN d.barrio IN ('JUAN D. PERON','JUAN D.PERON','Juan Domingo Peron','JUAN DOMINDO PERON',
                                      'JUAN DOMINGO PERÓN','Juán Domingo Perón','JUAN DOMINGO PERÓN ',
                                      'JUAN DOMINGO PERÓN - 742 VIVIENDAS','JUAN DOMINGO PERON 742',
                                      'JUAN DOMINGO PERON 742 VIVIENDAS') THEN 'JUAN DOMINGO PERON'
                    WHEN d.barrio IN ('lA MAROMA') THEN 'LA MAROMA'
                    WHEN d.barrio IN ('Laguna Siam') THEN 'LAGUNA SIAM'
                    WHEN d.barrio IN ('LAKHA WICHI','LACKA WICHI') THEN 'LAKA WICHI'
                    WHEN d.barrio IN ('LOTE 20') THEN 'LOTE 20 - EL MATADERO'
                    WHEN d.barrio IN ('LOTE Nº 5') THEN 'LOTE 5'
                    WHEN d.barrio IN ('Lote Ocho') THEN 'LOTE 8'
                    WHEN d.barrio IN ('luján','LUJAN','NUESTRA SEÑORA DEL LUJAN','NUESTRA SEÑORA DE LUJAN') THEN 'NUESTRA SEÑORA DE LUJÁN'
                    WHEN d.barrio IN ('Matadero') THEN 'MATADERO'
                    WHEN d.barrio IN ('Namuncura','NAMUNCURA') THEN 'NAMUNCURÁ'
                    WHEN d.barrio IN ('NUEVA FORMOSA') THEN 'LA NUEVA FORMOSA'
                    WHEN d.barrio IN ('MUEVA POMPEYA','POMPEYA') THEN 'NUEVA POMPEYA'
                    WHEN d.barrio IN ('QOMPI-\"JUAN SOSA\"','QOMPI \"JUAN SOSA\"') THEN 'QOMPI'
                    WHEN d.barrio IN ('REPUBLICA ARGENTINA','REPUBLICA ARGENTINA ') THEN 'REPÚBLICA ARGENTINA'
                    WHEN d.barrio IN ('Rusia') THEN 'RUSIA'
                    WHEN d.barrio IN ('SAN AGUSTIN') THEN 'SAN AGUSTÍN'
                    WHEN d.barrio IN ('SAN ANDRES I') THEN 'SAN ANDRES'
                    WHEN d.barrio IN ('San Antonio') THEN 'SAN ANTONIO'
                    WHEN d.barrio IN ('SAN ISIDRO','San Isidro Labrador') THEN 'SAN ISIDRO LABRADOR'
                    WHEN d.barrio IN ('San Martin') THEN 'SAN MARTIN'
                    WHEN d.barrio IN ('SAN Miguel') THEN 'SAN MIGUEL'
                    WHEN d.barrio IN ('toba') THEN 'TOBA'
                    WHEN d.barrio IN ('V.DEL CARMEN') THEN 'VILLA DEL CARMEN'
                    WHEN d.barrio IN ('Villa Lourdes','VILLA LOURDES ') THEN 'VILLA LOURDES'
                    WHEN d.barrio IN ('Wichi Watowe') THEN 'WICHI WATOWE'
                    ELSE d.barrio
                END                                         AS barrio,
                d.referencia,
                d.cod_postal,
                d.cui,
                loc.nombre                                  AS localidad,
                dep.nombre                                  AS departamento,
                d.calle_fondo,
                d.calle_derecha,
                d.calle_izquierda,
                d.x_longitud,
                d.y_latitud,
                ld.fecha_actualizacion
            FROM domicilio d
            LEFT JOIN  localidad_tipo       loc ON loc.c_localidad      = d.c_localidad
            INNER JOIN departamento_tipo    dep ON dep.c_departamento   = loc.c_departamento
            INNER JOIN localizacion_domicilio ld ON ld.id_domicilio     = d.id_domicilio
            INNER JOIN localizacion         l   ON l.id_localizacion    = ld.id_localizacion
            WHERE ld.c_tipo_dom = 1
            ORDER BY l.id_localizacion ASC
        ";

        $this->procesarQueryCompleta(
            sql: $sql,
            tablaDestino: self::SCHEMA . '.domicilio',
            mapeo: fn ($f) => [
                'id_localizacion'    => $f->id_localizacion,
                'id_domicilio'       => $f->id_domicilio,
                'calle'              => $f->calle,
                'nro'                => $f->nro,
                'barrio'             => $f->barrio,
                'referencia'         => $f->referencia,
                'cod_postal'         => $f->cod_postal,
                'cui'                => $f->cui,
                'localidad'          => $f->localidad,
                'departamento'       => $f->departamento,
                'calle_fondo'        => $f->calle_fondo,
                'calle_derecha'      => $f->calle_derecha,
                'calle_izquierda'    => $f->calle_izquierda,
                'x_longitud'         => $f->x_longitud,
                'y_latitud'          => $f->y_latitud,
                'fecha_actualizacion'=> $f->fecha_actualizacion,
            ],
            etl: $etl,
            porcentajeBase: $porcentajeBase
        );
    }

    // =========================================================================
    // Paso 5 — cambio_estado_localizacion
    // =========================================================================

    private function migrarCambioEstadoLocalizacion(?MigracionEtl $etl, int $porcentajeBase): void
    {
        $sql = "
            SELECT
                l.id_localizacion,
                l.codigo_jurisdiccional,
                l.nombre                                AS localizacion,
                lc.campo_prov_del_zonal,
                lc.campo_prov_eib,
                lc.campo_prov_dependencia,
                lc.campo_prov_es_var4,
                lc.campo_prov_cue_anterior,
                d.calle,
                d.nro,
                d.barrio,
                d.referencia,
                loc.nombre                              AS localidad,
                tm.descripcion                          AS tipo_movimiento,
                il.descripcion                          AS instrumento_legal,
                m.nro_instr_legal,
                m.observacion,
                mot.descripcion                         AS motivo,
                l.fecha_alta,
                l.fecha_baja,
                m.fecha_actualizacion,
                u.nombre                                AS usuario,
                ce.c_estado
            FROM localizacion l
            INNER JOIN cambio_estado_localizacion   ce  ON ce.id_localizacion  = l.id_localizacion
            INNER JOIN estado_tipo                  est ON est.c_estado         = ce.c_estado
            INNER JOIN movimiento                   m   ON m.id_movimiento      = ce.id_movimiento
            LEFT  JOIN instr_legal_tipo             il  ON il.c_instr_legal     = m.c_instr_legal
            LEFT  JOIN motivo_tipo                  mot ON mot.c_motivo         = m.c_motivo
            INNER JOIN localizacion_domicilio       ld  ON ld.id_localizacion   = l.id_localizacion
                                                       AND ld.c_tipo_dom        = 1
            INNER JOIN domicilio                    d   ON d.id_domicilio       = ld.id_domicilio
            LEFT  JOIN localidad_tipo               loc ON loc.c_localidad      = d.c_localidad
            LEFT  JOIN tipo_mov_tipo                tm  ON tm.c_tipo_mov        = m.c_tipo_mov
            LEFT  JOIN usuario                      u   ON u.id_usuario         = m.id_usuario
            LEFT  JOIN (
                SELECT
                    id_localizacion,
                    MAX(CASE WHEN id_campo_prov = 1214432500 THEN valor END) AS campo_prov_del_zonal,
                    MAX(CASE WHEN id_campo_prov = 1214432501 THEN valor END) AS campo_prov_eib,
                    MAX(CASE WHEN id_campo_prov = 1214432502 THEN valor END) AS campo_prov_dependencia,
                    MAX(CASE WHEN id_campo_prov = 1214432503 THEN valor END) AS campo_prov_es_var4,
                    MAX(CASE WHEN id_campo_prov = 1214432504 THEN valor END) AS campo_prov_cue_anterior
                FROM loc_campo_prov_valor
                GROUP BY id_localizacion
            ) lc ON lc.id_localizacion = l.id_localizacion
            ORDER BY m.fecha_actualizacion, loc.nombre, d.referencia, d.barrio, d.calle, d.nro ASC
        ";

        $this->procesarQueryCompleta(
            sql: $sql,
            tablaDestino: self::SCHEMA . '.cambio_estado_localizacion',
            mapeo: fn ($f) => [
                'id_localizacion'       => $f->id_localizacion,
                'codigo_jurisdiccional' => $f->codigo_jurisdiccional,
                'localizacion'          => $f->localizacion,
                'campo_prov_del_zonal'  => $f->campo_prov_del_zonal,
                'campo_prov_eib'        => $f->campo_prov_eib,
                'campo_prov_dependencia'=> $f->campo_prov_dependencia,
                'campo_prov_es_var4'    => $f->campo_prov_es_var4,
                'campo_prov_cue_anterior'=> $f->campo_prov_cue_anterior,
                'calle'                 => $f->calle,
                'nro'                   => $f->nro,
                'barrio'                => $f->barrio,
                'referencia'            => $f->referencia,
                'localidad'             => $f->localidad,
                'tipo_movimiento'       => $f->tipo_movimiento,
                'instrumento_legal'     => $f->instrumento_legal,
                'nro_instr_legal'       => $f->nro_instr_legal,
                'observacion'           => $f->observacion,
                'motivo'                => $f->motivo,
                'fecha_alta'            => $f->fecha_alta,
                'fecha_baja'            => $f->fecha_baja,
                'fecha_actualizacion'   => $f->fecha_actualizacion,
                'usuario'               => $f->usuario,
                'c_estado'              => $f->c_estado,
            ],
            etl: $etl,
            porcentajeBase: $porcentajeBase
        );
    }

    // =========================================================================
    // Paso 6 — cambio_estado_oferta_local
    // =========================================================================

    private function migrarCambioEstadoOfertaLocal(?MigracionEtl $etl, int $porcentajeBase): void
    {
        $sql = "
            SELECT
                ol.id_oferta_local,
                ol.id_localizacion,
                oc.campo_prov_del_zonal,
                oc.campo_prov_cua,
                oc.campo_prov_modalidad,
                oc.campo_prov_es_var6,
                oc.campo_prov_cod_jur,
                ol.codigo_jurisdiccional,
                ol.c_oferta,
                est.descripcion                         AS estado_movimiento,
                tm.descripcion                          AS tipo_movimiento,
                il.descripcion                          AS instrumento_legal,
                m.nro_instr_legal,
                m.observacion,
                mot.descripcion                         AS motivo,
                m.fecha_actualizacion,
                u.nombre                                AS usuario,
                ce.c_estado
            FROM oferta_local ol
            INNER JOIN cambio_estado_oferta_local   ce  ON ce.id_oferta_local  = ol.id_oferta_local
            INNER JOIN estado_tipo                  est ON est.c_estado         = ce.c_estado
            INNER JOIN movimiento                   m   ON m.id_movimiento      = ce.id_movimiento
            LEFT  JOIN tipo_mov_tipo                tm  ON tm.c_tipo_mov        = m.c_tipo_mov
            LEFT  JOIN instr_legal_tipo             il  ON il.c_instr_legal     = m.c_instr_legal
            LEFT  JOIN motivo_tipo                  mot ON mot.c_motivo         = m.c_motivo
            LEFT  JOIN usuario                      u   ON u.id_usuario         = m.id_usuario
            LEFT  JOIN (
                SELECT
                    id_oferta_local,
                    MAX(CASE WHEN id_campo_prov = 1214432509 THEN valor END) AS campo_prov_del_zonal,
                    MAX(CASE WHEN id_campo_prov = 1214432510 THEN valor END) AS campo_prov_cua,
                    MAX(CASE WHEN id_campo_prov = 1214432513 THEN valor END) AS campo_prov_modalidad,
                    MAX(CASE WHEN id_campo_prov = 1214432514 THEN valor END) AS campo_prov_es_var6,
                    MAX(CASE WHEN id_campo_prov = 1214432516 THEN valor END) AS campo_prov_cod_jur
                FROM oloc_campo_prov_valor
                GROUP BY id_oferta_local
            ) oc ON oc.id_oferta_local = ol.id_oferta_local
            ORDER BY m.fecha_actualizacion ASC
        ";

        $this->procesarQueryCompleta(
            sql: $sql,
            tablaDestino: self::SCHEMA . '.cambio_estado_oferta_local',
            mapeo: fn ($f) => [
                'id_oferta_local'       => $f->id_oferta_local,
                'id_localizacion'       => $f->id_localizacion,
                'campo_prov_del_zonal'  => $f->campo_prov_del_zonal,
                'campo_prov_cua'        => $f->campo_prov_cua,
                'campo_prov_modalidad'  => $f->campo_prov_modalidad,
                'campo_prov_es_var6'    => $f->campo_prov_es_var6,
                'campo_prov_cod_jur'    => $f->campo_prov_cod_jur,
                'codigo_jurisdiccional' => $f->codigo_jurisdiccional,
                'c_oferta'              => $f->c_oferta,
                'estado_movimiento'     => $f->estado_movimiento,
                'tipo_movimiento'       => $f->tipo_movimiento,
                'instrumento_legal'     => $f->instrumento_legal,
                'nro_instr_legal'       => $f->nro_instr_legal,
                'observacion'           => $f->observacion,
                'motivo'                => $f->motivo,
                'fecha_actualizacion'   => $f->fecha_actualizacion,
                'usuario'               => $f->usuario,
                'c_estado'              => $f->c_estado,
            ],
            etl: $etl,
            porcentajeBase: $porcentajeBase
        );
    }

    // =========================================================================
    // Paso 7 — historico_ofertas_locales (columnas dinámicas por año)
    // =========================================================================

    private function migrarHistoricoOfertasLocales(?MigracionEtl $etl, int $porcentajeBase): void
{
    $anioInicio = self::ANIO_INICIO;
    $anioFin    = (int) date('Y');

    // Nivel 1 (sss): CASE sin agregación — una fila por cambio de estado
    $casePorAnio = '';
    for ($a = $anioInicio; $a <= $anioFin; $a++) {
        $casePorAnio .= "CASE WHEN '{$a}-04-30' >= m.fecha_vigencia "
                      . "THEN m.fecha_actualizacion || '*' || est.descripcion END AS anio_{$a}";
        if ($a < $anioFin) $casePorAnio .= ",\n";
    }

    // Nivel 2 (fff): MAX por año agrupando por oferta
    $maxPorAnio = '';
    for ($a = $anioInicio; $a <= $anioFin; $a++) {
        $maxPorAnio .= "MAX(anio_{$a}) AS anio_{$a}";
        if ($a < $anioFin) $maxPorAnio .= ",\n";
    }

    // Nivel 3 (outer): SUBSTRING para extraer solo el estado (lo que viene después del '*')
    $substrPorAnio = '';
    for ($a = $anioInicio; $a <= $anioFin; $a++) {
        $substrPorAnio .= "SUBSTRING(anio_{$a}, POSITION('*' IN anio_{$a}) + 1) AS anio_{$a}";
        if ($a < $anioFin) $substrPorAnio .= ",\n";
    }

    $sql = "
        SELECT id_oferta_local, c_oferta, {$substrPorAnio}
        FROM (
            SELECT id_oferta_local, c_oferta, {$maxPorAnio}
            FROM (
                SELECT
                    ol.id_oferta_local,
                    ol.c_oferta,
                    {$casePorAnio}
                FROM oferta_local ol
                INNER JOIN cambio_estado_oferta_local ce  ON ce.id_oferta_local = ol.id_oferta_local
                INNER JOIN estado_tipo                est ON est.c_estado        = ce.c_estado
                INNER JOIN movimiento                 m   ON m.id_movimiento     = ce.id_movimiento
                ORDER BY ol.id_oferta_local, m.fecha_actualizacion ASC
            ) sss
            GROUP BY id_oferta_local, c_oferta
        ) fff
    ";

    $this->procesarQueryCompleta(
        sql: $sql,
        tablaDestino: self::SCHEMA . '.historico_ofertas_locales',
        mapeo: function ($f) use ($anioInicio, $anioFin) {
            $fila = [
                'id_oferta_local' => $f->id_oferta_local,
                'c_oferta'        => $f->c_oferta,
            ];
            for ($a = $anioInicio; $a <= $anioFin; $a++) {
                $col        = "anio_{$a}";
                $fila[$col] = $f->$col ?? null;
            }
            return $fila;
        },
        etl: $etl,
        porcentajeBase: $porcentajeBase
    );
}

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Genera la definición de columnas dinámicas para la tabla historico_ofertas_locales.
     * Una columna character varying por año desde ANIO_INICIO hasta el año actual.
     */
    private function columnasHistorico(): string
    {
        $cols = [];
        for ($a = self::ANIO_INICIO; $a <= (int) date('Y'); $a++) {
            $cols[] = "anio_{$a} character varying";
        }
        return implode(",\n", $cols);
    }

    /**
     * Ejecuta una query en nacion e inserta el resultado en lotes en planeamiento.
     */
    private function procesarQueryCompleta(
        string $sql,
        string $tablaDestino,
        callable $mapeo,
        ?MigracionEtl $etl,
        int $porcentajeBase
    ): void {
        $this->line('  Ejecutando consulta en nacion...');

        $filas = DB::connection(self::CONEXION)->select($sql);
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
                procesados: $porcentajeBase + (int)(($procesados / max($total, 1)) * 15),
                total: 100
            );
        }

        $bar->finish();
        $this->newLine();
        $this->line("  {$procesados} registros insertados en {$tablaDestino}.");
    }

    private function resolverEtl(): ?MigracionEtl
    {
        $id = $this->option('etl-id');
        return $id ? MigracionEtl::find($id) : null;
    }
}
