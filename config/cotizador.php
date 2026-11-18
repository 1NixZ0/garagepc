<?php

/*
|--------------------------------------------------------------------------
| Cotizador Multimoneda (M-07)
|--------------------------------------------------------------------------
|
| RF-COT-05: el Administrador elige qué serie de cotización usa el sistema
| sin tocar el código. La serie importa: valuar hardware usado en Argentina
| al tipo de cambio oficial da un número que no se parece al mercado real.
|
| RF-COT-11 fija los dos límites de antigüedad. Pasadas las 24 horas el
| sistema advierte; pasados los 7 días deja de convertir y muestra el
| importe sólo en su moneda de origen. Un dato viejo identificado como tal
| es preferible a uno dudoso presentado como fresco.
|
*/

return [

    'base_url' => env('COTIZADOR_URL', 'https://dolarapi.com'),

    'serie' => env('COTIZADOR_SERIE', 'blue'),

    'series_disponibles' => [
        'oficial' => 'Oficial',
        'blue' => 'Blue',
        'bolsa' => 'MEP / Bolsa',
        'contadoconliqui' => 'Contado con liquidación',
        'tarjeta' => 'Tarjeta',
        'mayorista' => 'Mayorista',
        'cripto' => 'Cripto',
    ],

    'timeout' => 8,

    // RF-COT-11
    'advertencia_horas' => 24,
    'vencimiento_dias' => 7,
];
