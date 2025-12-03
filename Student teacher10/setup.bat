@echo off
echo Setting up Student Teacher Management System...

:: Create a directory for downloads
mkdir "%USERPROFILE%\Downloads\setup_temp" 2>nul

:: Download XAMPP
echo Downloading XAMPP...
powershell -Command "& {$url = 'https://sourceforge.net/projects/xampp/files/XAMPP%%20Windows/8.2.4/xampp-windows-x64-8.2.4-0-VS16-installer.exe/download'; $output = '%USERPROFILE%\Downloads\setup_temp\xampp-installer.exe'; (New-Object System.Net.WebClient).DownloadFile($url, $output)}"

:: Check if download was successful
if exist "%USERPROFILE%\Downloads\setup_temp\xampp-installer.exe" (
    echo XAMPP downloaded successfully!
    echo Please follow these steps:
    echo 1. Run the XAMPP installer from: %USERPROFILE%\Downloads\setup_temp\xampp-installer.exe
    echo 2. Install with default options
    echo 3. After installation, come back to this window and press any key to continue
    pause
) else (
    echo Failed to download XAMPP
    echo Please download manually from: https://www.apachefriends.org/download.html
    echo After installation, press any key to continue
    pause
)

:: Start XAMPP services
echo Starting XAMPP services...
if exist "C:\xampp\xampp-control.exe" (
    start "" "C:\xampp\xampp-control.exe"
    echo Please start Apache and MySQL in the XAMPP Control Panel
    echo After starting the services, press any key to continue
    pause
)

:: Setup database
echo Setting up database...
if exist "C:\xampp\mysql\bin\mysql.exe" (
    :: Wait for MySQL to be ready
    timeout /t 10 /nobreak
    
    :: Create database and import schema
    "C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS student_teacher_db;"
    "C:\xampp\mysql\bin\mysql.exe" -u root student_teacher_db < database\schema.sql
    
    echo Database setup complete!
) else (
    echo MySQL not found. Please ensure XAMPP is installed correctly.
)

:: Copy files to web directory
echo Copying files to web directory...
if exist "C:\xampp\htdocs" (
    xcopy /E /I /Y "." "C:\xampp\htdocs\student-teacher\"
    echo Files copied successfully!
) else (
    echo Web directory not found. Please ensure XAMPP is installed correctly.
)

echo Setup complete!
echo You can now access the system at: http://localhost/student-teacher
pause