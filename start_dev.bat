@echo off
title Al Amin Math Care - Dev Server
color 0A

rem === 1. Determine Project Directory ===
if exist "%~dp0index.php" (
    cd /d "%~dp0"
    goto DIR_OK
)
if exist "D:\Al Amin's Math Care\index.php" (
    cd /d "D:\Al Amin's Math Care"
    goto DIR_OK
)
if exist "d:\Al Amin's Math Care\index.php" (
    cd /d "d:\Al Amin's Math Care"
    goto DIR_OK
)

echo.
echo [ERROR] Project folder D:\Al Amin's Math Care not found!
pause
exit /b 1

:DIR_OK

echo.
echo =============================================================
echo   Al Amin Math Care - Development Server
echo   PHP 8.3  ^|  http://localhost:8000
echo =============================================================
echo.
echo [DIR] Active Folder: %cd%

rem === 2. Check PHP ===
where php >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] PHP was not found in system PATH.
    pause
    exit /b 1
)
echo [OK] PHP is installed.

rem === 3. Stop old PHP instances ===
echo [..] Stopping any old PHP instances...
taskkill /IM php.exe /F >nul 2>&1
ping 127.0.0.1 -n 2 >nul

rem === 4. Check or Start MySQL ===
netstat -ano | findstr ":3306 :3307" | findstr "LISTENING" >nul 2>&1
if %errorlevel% neq 0 (
    echo [..] Checking MySQL Service...
    net start MySQL80 >nul 2>&1
    net start MySQL >nul 2>&1
)
netstat -ano | findstr ":3306 :3307" | findstr "LISTENING" >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] MySQL is active and listening.
) else (
    echo [WARN] MySQL is not detected. Please ensure MySQL is started.
)

rem === 5. Auto open browser ===
echo [..] Opening browser at http://localhost:8000 ...
start /b "" powershell -NoProfile -Command "Start-Sleep -Seconds 1; Start-Process 'http://localhost:8000/'"

rem === 6. Info ===
echo.
echo =============================================================
echo   Server is running at: http://localhost:8000/
echo   Admin Dashboard     : http://localhost:8000/admin/
echo   Student Portal      : http://localhost:8000/portal/login.php
echo =============================================================
echo.
echo Press CTRL + C to stop the server.
echo.

rem === 7. Start PHP Server ===
php -S 0.0.0.0:8000
