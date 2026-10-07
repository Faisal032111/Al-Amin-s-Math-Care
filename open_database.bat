@echo off
chcp 65001 >nul
title MySQL Database - alaminmathcare
echo ===================================================
echo   Al Amin's Math Care - Database Console
echo   Host: 127.0.0.1  |  Port: 3306
echo   User: root       |  Database: alaminmathcare
echo ===================================================
echo.
echo Tables in alaminmathcare:
echo.
"C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe" -P 3306 -h 127.0.0.1 -u root -pFaisal@5511045# alaminmathcare -e "SHOW TABLES;"
echo.
echo ---------------------------------------------------
echo Opening interactive MySQL prompt...
echo (Type SQL queries here, e.g: SELECT * FROM courses; or type exit to quit)
echo ---------------------------------------------------
echo.
"C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe" -P 3306 -h 127.0.0.1 -u root -pFaisal@5511045# alaminmathcare
pause
