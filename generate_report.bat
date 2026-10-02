@echo off
cd /d "%~dp0"
echo Generating Village Link report...
php generate_report.php
if errorlevel 1 (
    echo.
    echo PHP not found or generation failed.
    echo Open reports\VillageLink_Final_Report.doc in Microsoft Word instead.
    pause
    exit /b 1
)
echo.
echo Done! Open reports\VillageLink_Final_Report.docx
echo Copy also saved to: %USERPROFILE%\Documents\VillageLink_Final_Report.docx
pause
