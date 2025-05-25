@echo off
setlocal EnableDelayedExpansion

:: Dominio y contraseña
set DOMAIN=svobodua
set PASSWORD=QWERasdf1234

set OU=OU=ONG-Voluntarios,DC=%DOMAIN%,DC=org
set GROUP="CN=G-VOLUNTARIOS,OU=ONG-Voluntarios,DC=%DOMAIN%,DC=org"
set LOCALPATH=\\192.168.13.159\Voluntarios\home
set REMOTEPATH=\\192.168.13.159\Departaments\Voluntarios\home

set NOMBRES=as
set APELLIDOS=as
set USERNAME=as
mkdir "!REMOTEPATH!\!USERNAME!"
dsadd user "CN=!USERNAME!,%OU%" -samid !USERNAME! -upn !USERNAME!@%DOMAIN%.org -display "!USERNAME!" -pwd %PASSWORD% -hmdir !LOCALPATH!\!USERNAME! -hmdrv P: -disabled no
icacls "!REMOTEPATH!\!USERNAME!" /grant %DOMAIN%\!USERNAME!:F
dsmod group %GROUP% -addmbr "CN=!USERNAME!,%OU%"


echo Proceso completado correctamente.
pause
endlocal
