-- TP4 Calidad de Software - Rendimiento de base de datos
-- Dimension: rendimiento de base de datos. Herramienta: EXPLAIN ANALYZE de MySQL 8.4

SELECT '=== 1. ESTRUCTURA DE LAS TABLAS ===' AS seccion;
DESCRIBE catalogo_hardware;
DESCRIBE catalogo_juegos;
DESCRIBE sesiones_rendimiento;

SELECT '=== 2. INDICES EXISTENTES ===' AS seccion;
SHOW INDEX FROM catalogo_hardware;
SHOW INDEX FROM catalogo_juegos;
SHOW INDEX FROM sesiones_rendimiento;

SELECT '=== 3. AUTOCOMPLETADO DEL CATALOGO DE HARDWARE (RNF-REN-06) ===' AS seccion;
EXPLAIN ANALYZE
SELECT * FROM catalogo_hardware
WHERE tipo = 'gpu' AND modelo LIKE '%rtx%'
ORDER BY anio_lanzamiento DESC, modelo ASC
LIMIT 10;

SELECT '=== 4. AUTOCOMPLETADO DEL CATALOGO DE JUEGOS ===' AS seccion;
EXPLAIN ANALYZE
SELECT * FROM catalogo_juegos
WHERE nombre LIKE '%cyber%'
ORDER BY anio_lanzamiento DESC, nombre ASC
LIMIT 10;

SELECT '=== 5. HISTORIAL DE RENDIMIENTO POR EQUIPO (RNF-REN-05) ===' AS seccion;
EXPLAIN ANALYZE
SELECT * FROM sesiones_rendimiento
ORDER BY fecha_sesion DESC;
