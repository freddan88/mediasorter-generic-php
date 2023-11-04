@echo OFF
setlocal EnableDelayedExpansion
title PHP - Album Organizer - Windows
cd %~dp0

php app\organizerScript.php

echo.
pause