@echo off
REM ================================================================
REM  LOGG Branch Fingerprint Agent - Windows Launcher
REM  جدوِل هذا الملف في Windows Task Scheduler كل 30 دقيقة
REM ================================================================

REM غيّر المسار ده لو PHP عندك في مكان تاني
set PHP_PATH=C:\xampp\php\php.exe

REM المسار للـ agent (لا تغيره)
set AGENT_PATH=%~dp0agent.php

REM تشغيل الـ agent
"%PHP_PATH%" "%AGENT_PATH%"

REM لو حصل خطأ اعرضه
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [ERROR] Agent failed with code %ERRORLEVEL%
    pause
)
