<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Datos estáticos del padrón — se corren UNA sola vez con:
 *   php artisan db:seed --class=PadronStaticDataSeeder
 *
 * No forman parte del ETL porque no vienen de nacion,
 * son valores fijos de referencia que no cambian entre migraciones.
 */
class PadronStaticDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedOrdenOferta();
        $this->seedAniosEstudio();
    }

    private function seedOrdenOferta(): void
    {
        DB::table('padron.orden_oferta')->truncate();

        DB::table('padron.orden_oferta')->insert([
            ['fila' => 1,  'c_oferta' => 100, 'descripcion_oferta' => 'MATERNAL'],
            ['fila' => 2,  'c_oferta' => 101, 'descripcion_oferta' => 'INICIAL'],
            ['fila' => 3,  'c_oferta' => 102, 'descripcion_oferta' => 'PRIMARIO'],
            ['fila' => 4,  'c_oferta' => 110, 'descripcion_oferta' => 'SECUNDARIO'],
            ['fila' => 5,  'c_oferta' => 116, 'descripcion_oferta' => 'COMÚN - SERVICIOS COMPLEMENTARIOS'],
            ['fila' => 6,  'c_oferta' => 119, 'descripcion_oferta' => 'COMÚN - CURSOS Y TALLERES DE ARTÍSTICA'],
            ['fila' => 7,  'c_oferta' => 115, 'descripcion_oferta' => 'SUPERIOR'],
            ['fila' => 8,  'c_oferta' => 139, 'descripcion_oferta' => 'ADULTO-ALFABETIZACIÓN'],
            ['fila' => 9,  'c_oferta' => 140, 'descripcion_oferta' => 'ADULTO-PRIMARIO'],
            ['fila' => 10, 'c_oferta' => 144, 'descripcion_oferta' => 'ADULTO-SECUNDARIO'],
            ['fila' => 11, 'c_oferta' => 146, 'descripcion_oferta' => 'FORMACION PROFESIONAL'],
            ['fila' => 12, 'c_oferta' => 123, 'descripcion_oferta' => 'ESPECIAL'],
            ['fila' => 13, 'c_oferta' => 153, 'descripcion_oferta' => 'HOSPITALARIA'],
        ]);

        $this->command->info('orden_oferta: 13 registros insertados.');
    }

    private function seedAniosEstudio(): void
    {
        DB::table('padron.anios_estudio')->truncate();

        DB::table('padron.anios_estudio')->insert([
            // MATERNAL
            ['fila' => 1,  'curso_1' => 'Lactantes',       'curso_2' => 'Lactantes',       'nivel' => 100],
            ['fila' => 2,  'curso_1' => 'Deambuladores',    'curso_2' => 'Deambuladores',    'nivel' => 100],
            ['fila' => 3,  'curso_1' => 'Sala de 2 años',   'curso_2' => 'Sala de 2 años',   'nivel' => 100],

            // INICIAL
            ['fila' => 4,  'curso_1' => 'Sala de 3 años',   'curso_2' => 'Sala de 3 años',   'nivel' => 101],
            ['fila' => 5,  'curso_1' => 'Sala de 4 años',   'curso_2' => 'Sala de 4 años',   'nivel' => 101],
            ['fila' => 6,  'curso_1' => 'Sala de 5 años',   'curso_2' => 'Sala de 5 años',   'nivel' => 101],

            // PRIMARIO
            ['fila' => 7,  'curso_1' => 'Aprestamiento',    'curso_2' => 'Aprestamiento',    'nivel' => 102],
            ['fila' => 8,  'curso_1' => '1er Año/Grado',    'curso_2' => '1° Grado',         'nivel' => 102],
            ['fila' => 8,  'curso_1' => '1°',               'curso_2' => '1° Grado',         'nivel' => 102],
            ['fila' => 9,  'curso_1' => '2do Año/Grado',    'curso_2' => '2° Grado',         'nivel' => 102],
            ['fila' => 9,  'curso_1' => '2°',               'curso_2' => '2° Grado',         'nivel' => 102],
            ['fila' => 10, 'curso_1' => '3er Año/Grado',    'curso_2' => '3° Grado',         'nivel' => 102],
            ['fila' => 10, 'curso_1' => '3°',               'curso_2' => '3° Grado',         'nivel' => 102],
            ['fila' => 11, 'curso_1' => '4to Año/Grado',    'curso_2' => '4° Grado',         'nivel' => 102],
            ['fila' => 11, 'curso_1' => '4°',               'curso_2' => '4° Grado',         'nivel' => 102],
            ['fila' => 12, 'curso_1' => '5to Año/Grado',    'curso_2' => '5° Grado',         'nivel' => 102],
            ['fila' => 12, 'curso_1' => '5°',               'curso_2' => '5° Grado',         'nivel' => 102],
            ['fila' => 13, 'curso_1' => '6to Año/Grado',    'curso_2' => '6° Grado',         'nivel' => 102],
            ['fila' => 13, 'curso_1' => '6°',               'curso_2' => '6° Grado',         'nivel' => 102],

            // SECUNDARIO
            ['fila' => 14, 'curso_1' => '1er Año/Grado',    'curso_2' => '1° Año',           'nivel' => 110],
            ['fila' => 14, 'curso_1' => '1°',               'curso_2' => '1° Año',           'nivel' => 110],
            ['fila' => 15, 'curso_1' => '2do Año/Grado',    'curso_2' => '2° Año',           'nivel' => 110],
            ['fila' => 15, 'curso_1' => '2°',               'curso_2' => '2° Año',           'nivel' => 110],
            ['fila' => 16, 'curso_1' => '3er Año/Grado',    'curso_2' => '3° Año',           'nivel' => 110],
            ['fila' => 16, 'curso_1' => '3°',               'curso_2' => '3° Año',           'nivel' => 110],
            ['fila' => 17, 'curso_1' => '4to Año/Grado',    'curso_2' => '4° Año',           'nivel' => 110],
            ['fila' => 17, 'curso_1' => '4°',               'curso_2' => '4° Año',           'nivel' => 110],
            ['fila' => 18, 'curso_1' => '5to Año/Grado',    'curso_2' => '5° Año',           'nivel' => 110],
            ['fila' => 18, 'curso_1' => '5°',               'curso_2' => '5° Año',           'nivel' => 110],
            ['fila' => 19, 'curso_1' => '6to Año/Grado',    'curso_2' => '6° Año',           'nivel' => 110],
            ['fila' => 19, 'curso_1' => '6°',               'curso_2' => '6° Año',           'nivel' => 110],
            ['fila' => 20, 'curso_1' => '7mo Año/Grado',    'curso_2' => '7° Año',           'nivel' => 110],
            ['fila' => 20, 'curso_1' => '7°',               'curso_2' => '7° Año',           'nivel' => 110],

            // SUPERIOR
            ['fila' => 22, 'curso_1' => 'Superior',         'curso_2' => 'Superior',         'nivel' => 115],

            // ADULTO ALFABETIZACIÓN
            ['fila' => 23, 'curso_1' => 'Adulto Alfabetización', 'curso_2' => 'Adulto Alfabetización', 'nivel' => 139],

            // ADULTO PRIMARIO
            ['fila' => 24, 'curso_1' => 'Alfabetización con terminalidad',  'curso_2' => 'Alfabetización con terminalidad', 'nivel' => 140],
            ['fila' => 25, 'curso_1' => 'Organización no graduada',         'curso_2' => 'Organización no graduada',        'nivel' => 140],
            ['fila' => 26, 'curso_1' => '1er Ciclo',                        'curso_2' => '1° ciclo',                       'nivel' => 140],
            ['fila' => 26, 'curso_1' => '1er Año/Grado',                    'curso_2' => '1° ciclo',                       'nivel' => 140],
            ['fila' => 27, 'curso_1' => '2er Ciclo',                        'curso_2' => '2° ciclo',                       'nivel' => 140],
            ['fila' => 27, 'curso_1' => '2do Año/Grado',                    'curso_2' => '2° ciclo',                       'nivel' => 140],
            ['fila' => 28, 'curso_1' => '3er Ciclo',                        'curso_2' => '3° ciclo',                       'nivel' => 140],
            ['fila' => 28, 'curso_1' => '3er Año/Grado',                    'curso_2' => '3° ciclo',                       'nivel' => 140],

            // ADULTO SECUNDARIO
            ['fila' => 29, 'curso_1' => '1er Año/Grado',           'curso_2' => '1° Año',                   'nivel' => 144],
            ['fila' => 30, 'curso_1' => '2do Año/Grado',           'curso_2' => '2° Año',                   'nivel' => 144],
            ['fila' => 31, 'curso_1' => '3er Año/Grado',           'curso_2' => '3° Año',                   'nivel' => 144],
            ['fila' => 32, 'curso_1' => '4to Año/Grado',           'curso_2' => '4° Año',                   'nivel' => 144],
            ['fila' => 33, 'curso_1' => 'Organización no graduada','curso_2' => 'Organización no graduada',  'nivel' => 144],

            // FORMACIÓN PROFESIONAL
            ['fila' => 34, 'curso_1' => '1er Año/Grado',           'curso_2' => '1° Año',                   'nivel' => 146],
            ['fila' => 35, 'curso_1' => '2do Año/Grado',           'curso_2' => '2° Año',                   'nivel' => 146],
            ['fila' => 36, 'curso_1' => '3er Año/Grado',           'curso_2' => '3° Año',                   'nivel' => 146],
            ['fila' => 37, 'curso_1' => '7mo Año/Grado',           'curso_2' => '7° Año',                   'nivel' => 146],
            ['fila' => 38, 'curso_1' => '15to Año/Grado',          'curso_2' => '15to Año/Grado',           'nivel' => 146],
            ['fila' => 39, 'curso_1' => 'Organización no graduada','curso_2' => 'Organización no graduada',  'nivel' => 146],

            // ESPECIAL — JARDÍN
            ['fila' => 40, 'curso_1' => 'Educación Temprana',              'curso_2' => 'Educación Temprana',              'nivel' => 123],
            ['fila' => 41, 'curso_1' => 'Jardín Infantes',                 'curso_2' => 'Jardín Infantes',                 'nivel' => 123],
            ['fila' => 41, 'curso_1' => 'Jardín de Infantes',              'curso_2' => 'Jardín Infantes',                 'nivel' => 123],
            ['fila' => 41, 'curso_1' => 'Nivel Inicial',                   'curso_2' => 'Jardín Infantes',                 'nivel' => 123],
            ['fila' => 42, 'curso_1' => 'Jardín Infantes - Sala de 3 años','curso_2' => 'Jardín Infantes - Sala de 3 años','nivel' => 123],
            ['fila' => 43, 'curso_1' => 'Jardín Infantes - Sala de 4 años','curso_2' => 'Jardín Infantes - Sala de 4 años','nivel' => 123],
            ['fila' => 44, 'curso_1' => 'Jardín Infantes - Sala de 5 años','curso_2' => 'Jardín Infantes - Sala de 5 años','nivel' => 123],
            ['fila' => 45, 'curso_1' => 'Jardín Infantes - Organización no graduada','curso_2' => 'Jardín Infantes - Organización no graduada','nivel' => 123],

            // ESPECIAL — PRIMARIA
            ['fila' => 46, 'curso_1' => 'Primaria',                'curso_2' => 'Primaria',                 'nivel' => 123],
            ['fila' => 46, 'curso_1' => 'Primario',                'curso_2' => 'Primaria',                 'nivel' => 123],
            ['fila' => 47, 'curso_1' => 'Primaria - 1er Año/Grado','curso_2' => 'Primaria - 1° Grado',      'nivel' => 123],
            ['fila' => 47, 'curso_1' => 'Primaria - 1°',           'curso_2' => 'Primaria - 1° Grado',      'nivel' => 123],
            ['fila' => 48, 'curso_1' => 'Primaria - 2do Año/Grado','curso_2' => 'Primaria - 2° Grado',      'nivel' => 123],
            ['fila' => 48, 'curso_1' => 'Primaria - 2°',           'curso_2' => 'Primaria - 2° Grado',      'nivel' => 123],
            ['fila' => 49, 'curso_1' => 'Primaria - 3er Año/Grado','curso_2' => 'Primaria - 3° Grado',      'nivel' => 123],
            ['fila' => 49, 'curso_1' => 'Primaria - 3°',           'curso_2' => 'Primaria - 3° Grado',      'nivel' => 123],
            ['fila' => 50, 'curso_1' => 'Primaria - 4to Año/Grado','curso_2' => 'Primaria - 4° Grado',      'nivel' => 123],
            ['fila' => 50, 'curso_1' => 'Primaria - 4°',           'curso_2' => 'Primaria - 4° Grado',      'nivel' => 123],
            ['fila' => 51, 'curso_1' => 'Primaria - 5to Año/Grado','curso_2' => 'Primaria - 5° Grado',      'nivel' => 123],
            ['fila' => 51, 'curso_1' => 'Primaria - 5°',           'curso_2' => 'Primaria - 5° Grado',      'nivel' => 123],
            ['fila' => 52, 'curso_1' => 'Primaria - 6to Año/Grado','curso_2' => 'Primaria - 6° Grado',      'nivel' => 123],
            ['fila' => 52, 'curso_1' => 'Primaria - 6°',           'curso_2' => 'Primaria - 6° Grado',      'nivel' => 123],
            ['fila' => 53, 'curso_1' => 'Primaria - Organización no graduada',  'curso_2' => 'Primaria - Organización no graduada',  'nivel' => 123],
            ['fila' => 53, 'curso_1' => 'Primaria - Organización Modular',      'curso_2' => 'Primaria - Organización no graduada',  'nivel' => 123],

            // ESPECIAL — SECUNDARIA
            ['fila' => 54, 'curso_1' => 'Secundaria Especial',                              'curso_2' => 'Secundaria Especial',                             'nivel' => 123],
            ['fila' => 54, 'curso_1' => 'Secundario',                                       'curso_2' => 'Secundaria Especial',                             'nivel' => 123],
            ['fila' => 55, 'curso_1' => 'Secundaria Especial - Organización no graduada',   'curso_2' => 'Secundaria Especial -  Organización no graduada', 'nivel' => 123],
            ['fila' => 55, 'curso_1' => 'Educación Integral para Adolescentes y Jóvenes',  'curso_2' => 'Educación Integral para Adolescentes y Jóvenes',  'nivel' => 123],
            ['fila' => 56, 'curso_1' => 'Cursos Extracurriculares de la Escuela Especial',  'curso_2' => 'Cursos Extracurriculares de la Escuela Especial',  'nivel' => 123],
            ['fila' => 57, 'curso_1' => 'Taller de Educación Integral',                     'curso_2' => 'Taller de Educación Integral',                    'nivel' => 123],
            ['fila' => 57, 'curso_1' => 'Ex Taller de Educacion Integral para Adolescentes y Jóvenes', 'curso_2' => 'Ex Taller de Educacion Integral para Adolescentes y Jóvenes', 'nivel' => 123],
            ['fila' => 58, 'curso_1' => 'Secundaria Integral',              'curso_2' => 'Secundaria Integral',              'nivel' => 123],
            ['fila' => 59, 'curso_1' => 'Secundaria Integral - 1er Año/Grado','curso_2' => 'Secundaria Integral - 1° Año',  'nivel' => 123],
            ['fila' => 60, 'curso_1' => 'Secundaria Integral - 2do Año/Grado','curso_2' => 'Secundaria Integral - 2° Año',  'nivel' => 123],
            ['fila' => 61, 'curso_1' => 'Secundaria Integral - 3er Año/Grado','curso_2' => 'Secundaria Integral - 3° Año',  'nivel' => 123],
            ['fila' => 62, 'curso_1' => 'Secundaria Integral - 4to Año/Grado','curso_2' => 'Secundaria Integral - 4° Año',  'nivel' => 123],
            ['fila' => 63, 'curso_1' => 'Secundaria Integral - 5to Año/Grado','curso_2' => 'Secundaria Integral - 5° Año',  'nivel' => 123],
            ['fila' => 64, 'curso_1' => 'Secundaria Integral - 6to Año/Grado','curso_2' => 'Secundaria Integral - 6° Año',  'nivel' => 123],
            ['fila' => 65, 'curso_1' => 'Secundaria Integral - 7mo Año/Grado','curso_2' => 'Secundaria Integral - 7° Año',  'nivel' => 123],
            ['fila' => 66, 'curso_1' => 'Secundaria Integral - Organización no graduada','curso_2' => 'Secundaria Integral - Organización no graduada','nivel' => 123],

            // ESPECIAL — INTEGRADOS SIMULTÁNEOS
            ['fila' => 67, 'curso_1' => 'INTEGRADOS ESTABLECIMIENTOS SIMULTÁNEOS',          'curso_2' => 'INTEGRADOS ESTABLECIMIENTOS SIMULTÁNEOS',          'nivel' => 123],
            ['fila' => 68, 'curso_1' => 'IS - Jardín de Infantes',                          'curso_2' => 'INTEGRADOS ESTABLECIMIENTOS SIMULTÁNEOS - Jardín de Infantes','nivel' => 123],
            ['fila' => 68, 'curso_1' => 'IS - Inicial',                                     'curso_2' => 'INTEGRADOS ESTABLECIMIENTOS SIMULTÁNEOS - Jardín de Infantes','nivel' => 123],
            ['fila' => 69, 'curso_1' => 'IS - Primario',                                    'curso_2' => 'INTEGRADOS ESTABLECIMIENTOS SIMULTÁNEOS - PRIMARIO', 'nivel' => 123],
            ['fila' => 69, 'curso_1' => 'IS - EGB 1 / EGB 2',                               'curso_2' => 'INTEGRADOS ESTABLECIMIENTOS SIMULTÁNEOS - PRIMARIO', 'nivel' => 123],
            ['fila' => 69, 'curso_1' => 'IS - Primario de 6 años',                          'curso_2' => 'INTEGRADOS ESTABLECIMIENTOS SIMULTÁNEOS - PRIMARIO', 'nivel' => 123],
            ['fila' => 69, 'curso_1' => 'IS - Primario de 6 años/EGB 1 y 2',                'curso_2' => 'INTEGRADOS ESTABLECIMIENTOS SIMULTÁNEOS - PRIMARIO', 'nivel' => 123],
            ['fila' => 69, 'curso_1' => 'IS - Primario de 7 años',                          'curso_2' => 'INTEGRADOS ESTABLECIMIENTOS SIMULTÁNEOS - PRIMARIO', 'nivel' => 123],
            ['fila' => 71, 'curso_1' => 'IS - Secundario',                                  'curso_2' => 'INTEGRADOS ESTABLECIMIENTOS SIMULTÁNEOS - Secundario','nivel' => 123],
            ['fila' => 71, 'curso_1' => 'IS - Secundario / Medio',                          'curso_2' => 'INTEGRADOS ESTABLECIMIENTOS SIMULTÁNEOS - Secundario','nivel' => 123],
            ['fila' => 71, 'curso_1' => 'IS - Medio / Secundario',                          'curso_2' => 'INTEGRADOS ESTABLECIMIENTOS SIMULTÁNEOS - Secundario','nivel' => 123],
            ['fila' => 72, 'curso_1' => 'IS - Primario/EGB Adultos',                        'curso_2' => 'INTEGRADOS ESTABLECIMIENTOS SIMULTÁNEOS - ADULTO PRIMARIO','nivel' => 123],
            ['fila' => 73, 'curso_1' => 'IS - Medio / Polimodal Adultos ',                  'curso_2' => 'INTEGRADOS ESTABLECIMIENTOS SIMULTÁNEOS - ADULTO SECUNDARIO','nivel' => 123],
            ['fila' => 74, 'curso_1' => 'IS - Formación Profesional',                       'curso_2' => 'INTEGRADOS ESTABLECIMIENTOS SIMULTÁNEOS - Formación Profesional','nivel' => 123],
            ['fila' => 75, 'curso_1' => 'IS - Residencia laboral / Pasantías / Artística',  'curso_2' => 'INTEGRADOS ESTABLECIMIENTOS SIMULTÁNEOS - Residencia laboral / Pasantías / Artística','nivel' => 123],

            // ESPECIAL — APOYO DOCENTE
            ['fila' => 76, 'curso_1' => 'INTEGRADOS APOYO DOCENTE',                         'curso_2' => 'INTEGRADOS APOYO DOCENTE',                         'nivel' => 123],
            ['fila' => 77, 'curso_1' => 'AP - Jardín de Infantes',                          'curso_2' => 'INTEGRADOS APOYO DOCENTE - Jardín de Infantes',    'nivel' => 123],
            ['fila' => 77, 'curso_1' => 'AP - Inicial',                                     'curso_2' => 'INTEGRADOS APOYO DOCENTE - Jardín de Infantes',    'nivel' => 123],
            ['fila' => 78, 'curso_1' => 'AP - Primario',                                    'curso_2' => 'INTEGRADOS APOYO DOCENTE - PRIMARIO',              'nivel' => 123],
            ['fila' => 78, 'curso_1' => 'AP - Primario de 6 años',                          'curso_2' => 'INTEGRADOS APOYO DOCENTE - PRIMARIO',              'nivel' => 123],
            ['fila' => 78, 'curso_1' => 'AP - Primario de 6 años/EGB 1 y 2',                'curso_2' => 'INTEGRADOS APOYO DOCENTE - PRIMARIO',              'nivel' => 123],
            ['fila' => 79, 'curso_1' => 'AP - Primario de 7 años',                          'curso_2' => 'INTEGRADOS APOYO DOCENTE - PRIMARIO',              'nivel' => 123],
            ['fila' => 80, 'curso_1' => 'AP - Secundario',                                  'curso_2' => 'INTEGRADOS APOYO DOCENTE - Secundario',            'nivel' => 123],
            ['fila' => 80, 'curso_1' => 'AP - Secundario / Medio',                          'curso_2' => 'INTEGRADOS APOYO DOCENTE - Secundario',            'nivel' => 123],
            ['fila' => 80, 'curso_1' => 'AP - Medio / Secundario',                          'curso_2' => 'INTEGRADOS APOYO DOCENTE - Secundario',            'nivel' => 123],
            ['fila' => 80, 'curso_1' => 'AP - Polimodal',                                   'curso_2' => 'INTEGRADOS APOYO DOCENTE - Secundario',            'nivel' => 123],
            ['fila' => 80, 'curso_1' => 'AP - EGB 3',                                       'curso_2' => 'INTEGRADOS APOYO DOCENTE - Secundario',            'nivel' => 123],
            ['fila' => 81, 'curso_1' => 'AP - Superior',                                    'curso_2' => 'INTEGRADOS APOYO DOCENTE - Superior',              'nivel' => 123],
            ['fila' => 82, 'curso_1' => 'AP - Primario/EGB Adultos',                        'curso_2' => 'INTEGRADOS APOYO DOCENTE - ADULTO PRIMARIO',       'nivel' => 123],
            ['fila' => 83, 'curso_1' => 'AP - Medio / Polimodal Adultos ',                  'curso_2' => 'INTEGRADOS APOYO DOCENTE - ADULTO SECUNDARIO',     'nivel' => 123],
            ['fila' => 84, 'curso_1' => 'AP - Formación Profesional',                       'curso_2' => 'INTEGRADOS APOYO DOCENTE - Formación Profesional', 'nivel' => 123],
            ['fila' => 85, 'curso_1' => 'AP - Residencia laboral / Pasantías / Artística',  'curso_2' => 'INTEGRADOS APOYO DOCENTE - Residencia laboral / Pasantías / Artística','nivel' => 123],

            // HOSPITALARIA
            ['fila' => 86, 'curso_1' => 'Común | Inicial',                                          'curso_2' => 'INICIAL',            'nivel' => 153],
            ['fila' => 86, 'curso_1' => 'Inicial',                                                  'curso_2' => 'INICIAL',            'nivel' => 153],
            ['fila' => 87, 'curso_1' => 'Común | Primario / EGB',                                   'curso_2' => 'PRIMARIO',           'nivel' => 153],
            ['fila' => 87, 'curso_1' => 'Primario',                                                 'curso_2' => 'PRIMARIO',           'nivel' => 153],
            ['fila' => 87, 'curso_1' => 'Primario de 6 años/EGB 1 y 2',                             'curso_2' => 'PRIMARIO',           'nivel' => 153],
            ['fila' => 88, 'curso_1' => 'Común | Medio / Secundario / Polimodal',                   'curso_2' => 'SECUNDARIO',         'nivel' => 153],
            ['fila' => 89, 'curso_1' => 'Adultos | Medio / Secundario / Polimodal',                 'curso_2' => 'ADULTO SECUNDARIO',  'nivel' => 153],
            ['fila' => 90, 'curso_1' => 'Especial | Inicial',                                       'curso_2' => 'ESPECIAL INICIAL',   'nivel' => 153],
            ['fila' => 91, 'curso_1' => 'Especial | Primario / EGB',                                'curso_2' => 'ESPECIAL PRIMARIO',  'nivel' => 153],
            ['fila' => 92, 'curso_1' => 'Especial | Medio / Secundario / Polimodal',                'curso_2' => 'ESPECIAL SECUNDARIO','nivel' => 153],
            ['fila' => 93, 'curso_1' => 'Especial | Educación Integral para Adolescentes y Jóvenes/Secundario Especial','curso_2' => 'ESPECIAL INTEGRADOS','nivel' => 153],
        ]);

        $this->command->info('anios_estudio: registros insertados.');
    }
}
