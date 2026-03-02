# SQA - Ilumileds Online Store

Este documento define el **mínimo de aseguramiento de calidad (SQA)** para este proyecto sin sacrificar funcionalidad.

## 1. Definición de Hecho (DoD) para cambios de código

Un cambio solo se considera **terminado** si cumple todo lo siguiente:

1. **Compila y construye sin errores**
   - `npm run build` debe pasar.
   - No se introducen errores de TypeScript en los archivos modificados.
2. **Sin errores obvios en tiempo de ejecución**
   - No se lanzan excepciones no controladas en rutas/flows afectados.
   - Se manejan errores de red y de API con mensajes claros al usuario o logs controlados.
3. **Sin degradar UX ni flujos clave**
   - Flujos principales siguen funcionando: catálogo, filtro/categorías, carrito, checkout simulado, chatbot.
4. **Código legible y mantenible**
   - Nombres de funciones, variables y componentes descriptivos.
   - Sin duplicación obvia de lógica; extraer funciones auxiliares cuando aporte claridad.
   - Sin dejar código muerto / debug (`console.log` o archivos de prueba) salvo que estén claramente justificados.
5. **Revisión rápida de seguridad**
   - No exponer secretos en frontend, repositorio, logs o mensajes de error.
   - No devolver información sensible en respuestas de error al cliente.

## 2. Checklist SQA por cambio

Antes de hacer merge de un cambio, responder **SÍ** a estos puntos:

1. **Compilación / build**
   - [ ] Ejecuté `npm run verify` (o los comandos equivalentes) y pasó.
2. **Impacto funcional**
   - [ ] Probé manualmente los flujos que toca mi cambio.
   - [ ] Si toqué servicios o APIs, verifiqué al menos un caso de éxito y uno de error.
3. **Errores y manejo de fallos**
   - [ ] Cualquier `fetch` o llamada de red maneja errores (HTTP no-ok, timeout, JSON inválido).
   - [ ] No hay excepciones sin capturar en las rutas afectadas.
4. **Legibilidad**
   - [ ] El código nuevo es entendible sin comentarios excesivos.
   - [ ] No dejé código comentado, logs temporales ni artefactos de debug innecesarios.
5. **Seguridad básica**
   - [ ] No añadí secretos, tokens ni credenciales en el código.
   - [ ] No exponer detalles internos sensibles en mensajes mostrados al usuario.

## 3. Scripts de calidad

Los siguientes comandos se usan para validar la calidad de forma rápida:

- `npm run typecheck` → Verificación estática de tipos con TypeScript (sin emitir código).
- `npm run build` → Verifica que la app se construye correctamente.
- `npm run verify` → Ejecuta de forma encadenada los chequeos mínimos (typecheck + build).

## 4. Alcance inicial

Este SQA básico se centra en:

- Mantener **funcionalidad actual** de la tienda (sin regresiones).
- Mejorar gradualmente la calidad de código y el manejo de errores.
- Preparar el terreno para incorporar en el futuro:
  - Pruebas automatizadas (unitarias / integración).
  - Revisiones de seguridad y cumplimiento ISO/IEC 27034.
  - Integración continua (CI) con estos scripts.

