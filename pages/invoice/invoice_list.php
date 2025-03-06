<?php
// Include the authentication check file
include_once('../includes/auth_check.php');
?>
<!DOCTYPE html>
<html lang="en">

<?php include '../includes/header.php'; ?>
<style>
  .hidden-column {
    display: none;
  }
</style>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <!-- Navbar -->
  <?php include '../includes/navbar.php'; ?>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <?php include '../includes/sidebar.php'; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Invoice List</h1>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>
    <section class="content">
      <div class="container-fluid">
      <?php include '../includes/success_message.php'; ?>
      <?php include '../includes/error_message.php'; ?>
     
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <button id="payAllBtn" class="btn btn-success" onclick="payAll()" <?php echo (isset($_GET['assignment_id'])) ? '' : 'disabled'; ?>>Pay All</button>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <div class="table-responsive">
                  <table id="example3" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>Action</th>
                        <th class='hidden-column'>Invoice ID</th>
                        <th>Invoice Number</th>
                        <th>Tenant Name</th>
                        <th>Due Date Iterate</th>
                        <th>Bed Rate</th>
                        <th>Penalty Amount</th>
                        <th>Discount Amount</th>
                        <th>Total Due</th>
                        <th>Status</th>
                        <th>Remarks</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php
                      require_once('../includes/dbcon.php');
                      // Retrieve the invoice_number and assignment_id from the URL parameters
                      $invoice_number = isset($_GET['invoice_number']) ? $_GET['invoice_number'] : null;
                      $assignment_id = isset($_GET['assignment_id']) ? $_GET['assignment_id'] : null;
                      // Prepare the SQL query
                      $sql = "SELECT i.*, t.complete_name 
                              FROM tblinvoice i 
                              INNER JOIN tbltenant t ON i.tenant_id = t.tenant_id
                              WHERE 1=1";
                      // Append the condition for invoice_number if provided
                      if ($invoice_number) {
                          $sql .= " AND i.invoice_number = ?";
                      }
                      // Append the condition for assignment_id if provided
                      if ($assignment_id) {
                          $sql .= " AND i.assignment_id = ?";
                      }

                      // Order the results by invoice_id
                      $sql .= " ORDER BY i.invoice_id";

                      // Prepare and execute the query
                      $stmt = $conn->prepare($sql);
                      if ($invoice_number && $assignment_id) {
                          $stmt->bind_param("ii", $invoice_number, $assignment_id);
                      } elseif ($invoice_number) {
                          $stmt->bind_param("s", $invoice_number);
                      } elseif ($assignment_id) {
                          $stmt->bind_param("i", $assignment_id);
                      }
                      $stmt->execute();
                      $query = $stmt->get_result();

                      // Fetch and display the results
                      while($row = $query->fetch_assoc()) {
                          // Check if status is paid
                          $isPaid = ($row['status'] == 'paid');
                          
                          echo "<tr>
                                  <td>";
                          // Render Update button
                          if (!$isPaid) {
                              echo "<a href='#update_".$row['invoice_id']."' class='btn btn-primary btn-sm' data-toggle='modal'><span class='glyphicon glyphicon-edit'></span> Update</a>";
                          }
                          // Render View button
                          echo "<a href='view_invoice.php?invoice_id=".$row['invoice_id']."' class='btn btn-info btn-sm'><span class='glyphicon glyphicon-eye-open'></span> View</a>";
                          // Render Pay button
                          if (!$isPaid) {
                              echo "<a href='#pay_".$row['invoice_id']."' class='btn btn-success btn-sm' data-toggle='modal'><span class='glyphicon glyphicon-credit-card'></span> Pay</a>";
                          }
                          echo "</td>
                                <td class='hidden-column'>".$row['invoice_id']."</td>
                                <td>".$row['invoice_number']."</td>
                                <td>".$row['complete_name']."</td>
                                <td>".$row['due_date_iterate']."</td>
                                <td>".number_format($row['bed_rate'],2)."</td>
                                <td>".number_format($row['penalty_amount'],2)."</td>
                                <td>".number_format($row['discount_amount'],2)."</td>
                                <td>".number_format($row['total_due'],2)."</td>
                                <td>".$row['status']."</td>
                                <td>".$row['remarks']."</td>
                              </tr>";

                          include('update_modal.php');
                          include('pay_invoice_modal.php');
                      }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
    </section>
    <!-- /.content-header -->

    <!-- Main content -->
    <!-- Add Modal -->
 
  </div>
  <!-- /.content-wrapper -->

  

  <!-- Main Footer -->
  <?php include '../includes/dashboard_footer.php'; ?>

</div>
<!-- ./wrapper -->

<?php include '../includes/footer.php'; ?>

<!-- JavaScript function to handle payment of all invoices -->
<script>
function payAll() {
    // Get the selected assignment ID
    var assignmentId = <?php echo isset($_GET['assignment_id']) ? $_GET['assignment_id'] : 'null'; ?>;
    // Redirect to the payment page with the selected assignment ID
    window.location.href = 'pay_all_invoices.php?assignment_id=' + assignmentId;
}
</script>
</body>
</html>