# Datos del nuevo usuario
$Nombre = "Juan"
$Apellido = "Pérez"
$Usuario = "jperez"
$Contraseña = "P@ssw0rd123"

# Datos del servidor AD
$ServidorAD = "192.168.13.159"
$AdminUsuario = "Administrator"
$AdminContraseña = "QWERasdf1234"
$OU = "OU=testeo1,DC=svobodua,DC=org"
# Importar el módulo de Active Directory
Import-Module ActiveDirectory
# Crear el la conexion remota
# Convertir la contraseña en un objeto seguro
$SecureAdminPassword = ConvertTo-SecureString $AdminContraseña -AsPlainText -Force
$Credenciales = New-Object System.Management.Automation.PSCredential ($AdminUsuario, $SecureAdminPassword)

# Crear sesión remota con el servidor AD
$SesionRemota = New-PSSession -ComputerName $ServidorAD -Credential $Credenciales
Invoke-Command -Session $SesionRemota -ScriptBlock {
    param ($Nombre, $Apellido, $Usuario, $Contraseña, $OU)

    # Convertir la contraseña en formato seguro
    $SecurePassword = ConvertTo-SecureString $Contraseña -AsPlainText -Force

    # Crear el usuario en AD
    New-ADUser -Name "$Nombre $Apellido" `
               -GivenName $Nombre `
               -Surname $Apellido `
               -SamAccountName $Usuario `
               -UserPrincipalName "$Usuario@svobodua.org" `
               -Path $OU `
               -AccountPassword $SecurePassword `
               -Enabled $true
} -ArgumentList $Nombre, $Apellido, $Usuario, $Contraseña, $OU
# Cerrar la sesión remota
Remove-PSSession -Session $SesionRemota
