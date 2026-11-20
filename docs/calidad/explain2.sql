-- TP4 - Rendimiento de base de datos
-- Las tres consultas son las que arman los controladores, obtenidas con
-- toRawSql() y no reescritas a mano. MySQL 8.4.3.

SELECT '=== INDICES DISPONIBLES ===' AS seccion;
SHOW INDEX FROM sesiones_rendimiento;
SHOW INDEX FROM catalogo_hardware;
SHOW INDEX FROM catalogo_juegos;

SELECT '=== FILAS POR TABLA ===' AS seccion;
SELECT COUNT(*) AS sesiones_totales FROM sesiones_rendimiento;
SELECT juego, resolucion, nivel_calidad, COUNT(*) AS cantidad
FROM sesiones_rendimiento GROUP BY juego, resolucion, nivel_calidad ORDER BY cantidad DESC;

SELECT '=== H-08: HISTORIAL DE RENDIMIENTO (RNF-REN-05) ===' AS seccion;
EXPLAIN ANALYZE
select * from `sesiones_rendimiento`
where `sesiones_rendimiento`.`equipo_id` = 4
  and `sesiones_rendimiento`.`equipo_id` is not null
order by `fecha_sesion` desc;

SELECT '=== H-08b: LA CONSULTA AGRUPADA QUE EL CONTROLADOR NO HACE ===' AS seccion;
EXPLAIN ANALYZE
select `juego`, `resolucion`, `nivel_calidad`, count(*) as cantidad
from `sesiones_rendimiento`
where `equipo_id` = 4
group by `juego`, `resolucion`, `nivel_calidad`;

SELECT '=== H-10: AUTOCOMPLETADO DE HARDWARE (RNF-REN-06) ===' AS seccion;
EXPLAIN ANALYZE
select * from `catalogo_hardware` where `tipo` = 'gpu' and `modelo` like '%rtx%'
order by `anio_lanzamiento` desc, `modelo` asc limit 10;

SELECT '=== H-09: AUTOCOMPLETADO DE JUEGOS (RNF-REN-06) ===' AS seccion;
EXPLAIN ANALYZE
select * from `catalogo_juegos` where `nombre` like '%cyber%'
order by `anio_lanzamiento` desc, `nombre` asc limit 10;
