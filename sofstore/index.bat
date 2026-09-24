@echo off
chcp 65001 > nul
TITLE Sofstore - Generador de Estructura de Proyecto XAMPP
color 0A

echo =======================================================================
echo          SOFSTORE - SISTEMA DE GESTION COMERCIAL Y RESERVAS
echo          Generador de Estructura de Proyecto para XAMPP
echo =======================================================================
echo.
echo Creando la estructura de carpetas y archivos base...
echo.

REM 1. CREACION DE DIRECTORIOS
echo [1/5] Creando carpetas del sistema...
mkdir config
mkdir assets\css
mkdir assets\js
mkdir assets\img
mkdir assets\img\products
mkdir assets\img\comprobantes
mkdir includes
mkdir controllers
mkdir api
mkdir views\client
mkdir views\admin
mkdir uploads\products
mkdir uploads\comprobantes

REM 2. CREACION DE ARCHIVOS EN RAIZ Y CONFIG
echo [2/5] Creando archivos raiz y de configuracion...
type nul > index.php
type nul > setup_db.sql
type nul > config\database.php
type nul > config\config.php

REM 3. CREACION DE ARCHIVOS ASSETS (CSS Y JS)
echo [3/5] Creando archivos CSS y JavaScript...
type nul > assets\css\variables.css
type nul > assets\css\styles.css
type nul > assets\css\admin.css
type nul > assets\css\client.css
type nul > assets\js\main.js
type nul > assets\js\cart.js
type nul > assets\js\admin.js

REM 4. CREACION DE INCLUDES Y CONTROLADORES
echo [4/5] Creando componentes PHP, incluye y controladores...
type nul > includes\header.php
type nul > includes\footer.php
type nul > includes\sidebar_admin.php
type nul > includes\functions.php

type nul > controllers\AuthController.php
type nul > controllers\ProductController.php
type nul > controllers\OrderController.php
type nul > controllers\StatsController.php

type nul > api\cart_action.php
type nul > api\process_order.php
type nul > api\update_inventory.php
type nul > api\update_order_status.php

REM 5. CREACION DE VISTAS (CLIENTE Y ADMIN)
echo [5/5] Creando vistas para Cliente y Administrador...
type nul > views\client\catalog.php
type nul > views\client\cart.php
type nul > views\client\checkout.php
type nul > views\client\my_orders.php

type nul > views\admin\dashboard.php
type nul > views\admin\inventory.php
type nul > views\admin\orders.php
type nul > views\admin\stats.php

echo.
echo =======================================================================
echo ¡Estructura de Sofstore generada con exito!
echo Ubicacion recomendada: C:\xampp\htdocs\sofstore\
echo =======================================================================
echo.
pause