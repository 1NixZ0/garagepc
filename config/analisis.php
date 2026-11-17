<?php

/*
|--------------------------------------------------------------------------
| Motor de Análisis Proactivo (M-06)
|--------------------------------------------------------------------------
|
| Los umbrales viven acá y no en el código, conforme a RF-ANA-06 y RNF-MNT-03:
| el Administrador puede ajustarlos sin que nadie toque una clase.
|
| Cada regla puede apagarse por separado. Eso es lo que permite contener una
| falla sin detener el motor entero, como describe el apartado 8.1.1 del
| informe: si la regla de degradación larga alertas falsas, se apaga esa sola
| y las otras tres siguen trabajando.
|
*/

return [

    'umbrales' => [

        // RF-ANA-02. Meses desde la compra que un componente debe SUPERAR
        // para que se evalúe si existe una generación posterior.
        'antiguedad_meses' => 36,

        // RF-ANA-04. Diferencia de niveles entre CPU y GPU que dispara el
        // aviso de posible desbalance. La escala del catálogo va de 1 a 5.
        'desbalance_niveles' => 2,

        // RF-REN-08. Por debajo de esta cantidad de sesiones no se evalúa
        // degradación: no hay base suficiente para un desvío representativo.
        'degradacion_sesiones_minimas' => 8,

        // RF-REN-07. Tolerancia porcentual del umbral de degradación.
        // El umbral es máx(promedio − desvío, promedio × (1 − tolerancia)).
        'degradacion_tolerancia' => 0.10,

        // RF-ANA-07. Meses sin intervención que disparan el aviso de
        // mantenimiento preventivo.
        'mantenimiento_meses' => 6,
    ],

    'reglas' => [
        'antiguedad' => true,
        'degradacion' => true,
        'desbalance' => true,
        'mantenimiento' => true,
    ],
];
