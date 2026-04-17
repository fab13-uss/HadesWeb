<?php

namespace App\Services;

/**
 * Mapeo estable entre c_oferta y las tablas/filas del RA.
 * Este conocimiento es fijo entre años — solo cambia el esquema (ra_cargaXXXX).
 */
class MatriculaConfig
{
    public const OFERTAS = [
        100 => [
            'nombre'      => 'Maternal',
            'tabla'       => 'Celeste_104',
            'filtro_fila' => ['sala', 'in', ['Deambuladores', 'Lactantes', 'Sala de 2 años']],
            'col_total'   => 'total',
            'col_varones' => 'varones',
        ],
        101 => [
            'nombre'      => 'Inicial',
            'tabla'       => 'Celeste_104',
            'filtro_fila' => ['sala', 'not_in', ['Deambuladores', 'Lactantes', 'Sala de 2 años']],
            'col_total'   => 'total',
            'col_varones' => 'varones',
        ],
        102 => [
            'nombre'      => 'Primario',
            'tabla'       => 'Celeste_626',
            'filtro_fila' => ['"grado_año"', 'not_like', 'Aprestamiento'],
            'col_total'   => 'total',
            'col_varones' => 'varones',
        ],
        110 => [
            'nombre'      => 'Secundario',
            'tabla'       => 'Celeste_158',
            'col_total'   => 'total',
            'col_varones' => 'varones',
        ],
        115 => [
            'nombre'      => 'Superior',
            'tabla'       => 'Verde_287',
            'col_total'   => 'total',
            'col_varones' => 'varones',
        ],
        139 => [
            'nombre'      => 'Adulto Alfabetización',
            'tabla'       => 'Violeta_518',
            'col_total'   => 'total',
            'col_varones' => 'varones',
        ],
        140 => [
            'nombre'      => 'Adulto Primario',
            'tabla'       => 'Violeta_297',
            'col_total'   => 'total',
            'col_varones' => 'varones',
        ],
        144 => [
            'nombre'      => 'Adulto Secundario',
            'tabla'       => 'Violeta_525',
            'col_total'   => 'total',
            'col_varones' => 'varones',
        ],
        146 => [
            'nombre'      => 'Formación Profesional',
            'tabla'       => 'Naranja_298',
            'col_total'   => 'total',
            'col_varones' => 'varones',
        ],
        123 => [
            'nombre'      => 'Especial',
            'es_union'    => true,
            'tablas'      => ['Rosa_504', 'Rosa_506', 'Rosa_513', 'Rosa_522'],
            'col_total'   => 'total',
            'col_varones' => 'varones',
        ],
        153 => [
            'nombre'      => 'Hospitalaria',
            'tabla'       => 'Blanco_674',
            'col_total'   => 'total + total_hospitalaria',
            'col_varones' => 'varones + varones_hospitalaria',
        ],
    ];

    public const EXCLUIR_OFERTA_LOCAL = [1214436333, 1214436330, 1214436090, 1214436076];
    public const OFERTAS_CON_GROUPBY  = [123, 153];
    public const OFERTAS_SIN_CDI      = [100, 101, 102, 110, 115, 139, 140, 144, 146];

    public static function getNombre(int $cOferta): string
    {
        return self::OFERTAS[$cOferta]['nombre'] ?? "Oferta {$cOferta}";
    }

    public static function getTodasLasOfertas(): array
    {
        return collect(self::OFERTAS)
            ->map(fn ($def, $cod) => ['codigo' => $cod, 'nombre' => $def['nombre']])
            ->values()
            ->all();
    }
}
