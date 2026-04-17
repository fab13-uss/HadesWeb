<?php

namespace App\Livewire\Consultas;

use Livewire\Component;

class ConsultasDashboard extends Component
{
    public string $tabActiva = 'matricula-historica';

    /**
     * Definición de las tabs disponibles.
     * Para agregar una nueva consulta, solo agregá una entrada acá.
     */
    public function tabs(): array
    {
        return [
            'matricula-historica' => [
                'label'       => 'Matrícula Histórica',
                'descripcion' => 'Comparativa por oferta y año',
                'componente'  => 'consultas.matricula-historica',
            ],
            'superior' => [
                'label'       => 'Nivel Superior',
                'descripcion' => 'Por plan de estudio y tipo de formación',
                'componente'  => 'consultas.matricula-superior',
            ],
            // Para agregar más consultas:
            // 'nueva-clave' => [
            //     'label'      => 'Nombre visible',
            //     'descripcion'=> 'Descripción corta',
            //     'componente' => 'consultas.nombre-componente',
            // ],
        ];
    }

    public function cambiarTab(string $tab): void
    {
        if (array_key_exists($tab, $this->tabs())) {
            $this->tabActiva = $tab;
        }
    }

    public function render()
    {
        return view('livewire.consultas.dashboard', [
            'tabs'      => $this->tabs(),
            'tabActiva' => $this->tabActiva,
        ])->title('Consultas');
    }
}
