@echo off
rem KI-BASE deploy: sync app\ and public\ to the server with WinSCP.
rem Shows a preview first and asks for confirmation.
setlocal
cd /d "%~dp0.."

set "WINSCP=%ProgramFiles(x86)%\WinSCP\WinSCP.com"
if not exist "%WINSCP%" set "WINSCP=%ProgramFiles%\WinSCP\WinSCP.com"
if not exist "%WINSCP%" set "WINSCP=%LOCALAPPDATA%\Programs\WinSCP\WinSCP.com"
if not exist "%WINSCP%" (
    echo WinSCP.com not found. Install WinSCP or fix the path in deploy\deploy.bat.
    pause
    exit /b 1
)

echo === PREVIEW: changes that will be made on the server ===
"%WINSCP%" /script="deploy\winscp-preview.txt" /log="deploy\last-deploy.log"
if errorlevel 1 (
    echo Preview failed - see deploy\last-deploy.log
    pause
    exit /b 1
)

echo.
choice /c YN /m "Upload these changes to the server"
if errorlevel 2 (
    echo Cancelled. Nothing was changed.
    pause
    exit /b 0
)

"%WINSCP%" /script="deploy\winscp-sync.txt" /log="deploy\last-deploy.log"
if errorlevel 1 (
    echo DEPLOY FAILED - see deploy\last-deploy.log
    pause
    exit /b 1
)

echo Deploy finished.
pause
