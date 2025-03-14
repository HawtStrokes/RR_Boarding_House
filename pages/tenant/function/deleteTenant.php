<?php
session_start();
include_once('../../includes/dbcon.php');

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if (isset($_GET['id'])) {
    // Validate and sanitize input parameter
    if (!empty($_GET['id']) && is_numeric($_GET['id'])) {
        $id = sanitize_input($_GET['id']);

        // Retrieve the name of the tenant being deleted
        $stmt_select_tenant = $conn->prepare("SELECT complete_name FROM tbltenant WHERE tenant_id = ?");
        $stmt_select_tenant->bind_param("i", $id);
        $stmt_select_tenant->execute();
        $stmt_select_tenant->store_result();
        if ($stmt_select_tenant->num_rows > 0) {
            $stmt_select_tenant->bind_result($tenant_name);
            $stmt_select_tenant->fetch();

            // SQL injection prevention
            $sql_delete_tenant = "DELETE FROM tbltenant WHERE tenant_id = ?";
            $sql_insert_activitylog = "INSERT INTO tblactivitylog (user_id, log_type, details, date_time) VALUES (?, 'delete', ?, NOW())";

            // Prepare and bind parameters for deleting tenant
            $stmt_delete_tenant = $conn->prepare($sql_delete_tenant);
            $stmt_delete_tenant->bind_param("i", $id);

            // Prepare and bind parameters for inserting activity log
            $stmt_insert_activitylog = $conn->prepare($sql_insert_activitylog);
            $user_id = $_SESSION['user_id']; // Assuming user_id is stored in session
            $details = "Deleted tenant: $tenant_name";
            $stmt_insert_activitylog->bind_param("is", $user_id, $details);

            // Execute the query to delete tenant
            if ($stmt_delete_tenant->execute()) {
                // Insert record into tblactivitylog
                if ($stmt_insert_activitylog->execute()) {
                    $_SESSION['success'] = 'Tenant deleted successfully';
                } else {
                    $_SESSION['error'] = 'Failed to log activity';
                }
            } else {
                $_SESSION['error'] = 'Something went wrong in deleting the tenant';
            }

            // Close statements
            $stmt_delete_tenant->close();
            $stmt_insert_activitylog->close();
        } else {
            $_SESSION['error'] = 'Tenant not found';
        }
        $stmt_select_tenant->close();
    } else {
        $_SESSION['error'] = 'Invalid tenant ID';
    }
} else {
    $_SESSION['error'] = 'Invalid request';
}

header('location: ../tenant_list.php');
?>
