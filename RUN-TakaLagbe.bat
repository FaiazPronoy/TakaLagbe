@echo off
title TakaLagbe
cd /d "%~dp0"

set "XAMPP=C:\xampp"

echo ==========================================
echo   TAKALAGBE  -  CSE370
echo ==========================================
echo.
echo   Before this runs: open the XAMPP Control Panel
echo   and press Start on BOTH Apache and MySQL.
echo.
pause

if not exist "%XAMPP%\htdocs\" (
  echo [X] XAMPP not found at %XAMPP%
  echo     If yours is somewhere else, edit line 5 of this file.
  echo.
  pause
  exit /b 1
)

echo [1/3] Copying into htdocs ...
robocopy "TakaLagbe-v2" "%XAMPP%\htdocs\TakaLagbe-v2" /E /NFL /NDL /NJH /NJS /NP >nul

echo [2/3] Importing the database ...
"%XAMPP%\mysql\bin\mysql.exe" -u root < "%XAMPP%\htdocs\TakaLagbe-v2\takalagbe.sql"
if errorlevel 1 (
  echo.
  echo [X] Could not reach MySQL.
  echo     Start MySQL in the XAMPP Control Panel, then run this again.
  echo     If your MySQL root has a password, set TKL_DB_PASS before running this.
  echo.
  pause
  exit /b 1
)

echo [3/3] Filling it with demo data ...
"%XAMPP%\php\php.exe" "%XAMPP%\htdocs\TakaLagbe-v2\tools\seed_demo.php" --yes

echo.
echo   Done. Opening the app.
echo.
echo   App     http://localhost/TakaLagbe-v2/
echo           01711482093 / demo1234, PIN 482915
echo   Admin   http://localhost/TakaLagbe-v2/admin_login.php
echo           admin / admin123
echo.

start "" http://localhost/TakaLagbe-v2/
pause
