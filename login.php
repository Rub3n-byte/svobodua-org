<?php
// Set content type to JSON for responses
header('Content-Type: application/json');

// Define the path to the file where data will be stored
// Ensure your web server user has write permissions to this directory!
$dataFilePath = '\\\\192.168.153.254\\tmp\\usuarios.txt'; // Changed to match your batch script's path

// Active Directory Configuration
$ldap_server = '192.168.153.254'; // Replace with your AD server IP or hostname
$ldap_port = 389; // Standard LDAP port
$ldap_dn = 'DC=svobodua,DC=org'; // Replace with your domain's Base DN
$ldap_user = 'Administrator@svobodua.org'; // User with permissions to query AD (e.g., Administrator or a service account)
$ldap_password = 'QWERasdf1234'; // WARNING: Hardcoding credentials is INSECURE!

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get data from the form
    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';
    $firstName = $_POST["firstName"] ?? '';
    $lastName = $_POST["lastName"] ?? '';

    // --- Active Directory User Existence Check ---
    $userExistsInAD = false;
    $ldapconn = null; // Initialize to null

    try {
        // Connect to LDAP server
        $ldapconn = ldap_connect($ldap_server, $ldap_port);

        if ($ldapconn) {
            // Set LDAP protocol version to 3
            ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            // Optional: Set to true if your AD uses referrals
            ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

            // Bind to LDAP directory with administrative credentials
            $ldapbind = @ldap_bind($ldapconn, $ldap_user, $ldap_password);

            if ($ldapbind) {
                // Search for the user by sAMAccountName
                $filter = "(sAMAccountName=$username)";
                $search_result = ldap_search($ldapconn, $ldap_dn, $filter, array("sAMAccountName"));
                $entries = ldap_get_entries($ldapconn, $search_result);

                if ($entries['count'] > 0) {
                    $userExistsInAD = true;
                }
            } else {
                // LDAP bind failed
                error_log("LDAP bind failed: " . ldap_error($ldapconn));
                // You might want to return an error to the user here, or just log it
            }
        } else {
            // LDAP connection failed
            error_log("LDAP connection failed: " . ldap_error($ldapconn));
        }
    } catch (Throwable $e) {
        error_log("LDAP operation error: " . $e->getMessage());
    } finally {
        // Close LDAP connection if it was established
        if ($ldapconn) {
            ldap_close($ldapconn);
        }
    }

    if ($userExistsInAD) {
        http_response_code(409); // Conflict
        echo json_encode(['status' => 'error', 'message' => "El usuario '$username' ya existe en Active Directory."]);
        exit();
    }

    // --- If user does not exist in AD, proceed with saving data to file ---
    // Prepare data for the text file
    $txt_data = "$firstName\n$lastName\n$username\n$password\n";

    // Save to text file (`usuarios.txt`)
    // Using FILE_APPEND to add to the end of the file
    if (file_put_contents($dataFilePath, $txt_data, FILE_APPEND | LOCK_EX) !== false) {
        http_response_code(200); // OK
        echo json_encode(['status' => 'success', 'message' => "Usuario '$username' validado y datos guardados correctamente en TXT."]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar los datos del usuario en TXT. Verifique la ruta y permisos.']);
    }

} else {
    // If it's not a POST request, return Method Not Allowed
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}
?>
