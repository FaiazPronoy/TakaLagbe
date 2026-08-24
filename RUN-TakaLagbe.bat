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

if not exist "TakaLagbe-v2\" (
  echo [1/3] Extracting TakaLagbe-v2.zip ...
  powershell -NoProfile -Command "Expand-Archive -Path 'TakaLagbe-v2.zip' -DestinationPath '.' -Force"
) else (
  echo [1/3] Already extracted.
)

echo [2/3] Copying into htdocs ...
robocopy "TakaLagbe-v2" "%XAMPP%\htdocs\TakaLagbe-v2" /E /NFL /NDL /NJH /NJS /NP >nul

echo [3/3] Importing the database ...
"%XAMPP%\mysql\bin\mysql.exe" -u root < "%XAMPP%\htdocs\TakaLagbe-v2\takalagbe.sql"
if errorlevel 1 (
  echo.
  echo [X] Could not reach MySQL.
  echo     Start MySQL in the XAMPP Control Panel, then run this again.
  echo     If your MySQL root has a password, edit config/config.php inside the project.
  echo.
  pause
  exit /b 1
)

echo.
echo   Done. Opening the app.
echo.
echo   App     http://localhost/TakaLagbe-v2/
echo   Admin   http://localhost/TakaLagbe-v2/admin_login.php
echo           username admin  /  password admin123
echo.
echo   Sign up a normal customer through the app. You will set a
echo   password and a separate 6 digit transaction PIN.
echo.

start "" http://localhost/TakaLagbe-v2/
pause
