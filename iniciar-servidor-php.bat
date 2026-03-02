@echo off
title Servidor PHP - Ilumileds (no cierres esta ventana)
cd /d "%~dp0"

echo.
echo  Servidor PHP para la app Ilumileds
echo  La app en http://localhost:5173 usara este servidor para el chatbot y los productos.
echo.
echo  NO CIERRES esta ventana mientras uses la app.
echo  Para parar el servidor: cierra esta ventana.
echo.

php -S localhost:8000 -t .
if errorlevel 1 (
  echo.
  echo  ERROR: No se encontro "php" en el equipo.
  echo  Necesitas instalar PHP o usar XAMPP y colocar este proyecto en htdocs.
  echo.
)
pause
