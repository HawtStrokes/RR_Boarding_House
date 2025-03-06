<?php
// Include the authentication check file
include_once('../includes/auth_check.php');

// Include database connection
include_once('../includes/dbcon.php');

// Query to get the number of active tenants
$sql_active_tenants = "SELECT COUNT(*) AS num_active_tenants FROM tbltenant WHERE status = 'active'";
$result_active_tenants = $conn->query($sql_active_tenants);
$row_active_tenants = $result_active_tenants->fetch_assoc();
$num_active_tenants = $row_active_tenants['num_active_tenants'];

// Query to get the number of beds
$sql_num_beds = "SELECT COUNT(bed_id) AS total_beds FROM tblbed";
$result_num_beds = $conn->query($sql_num_beds);
$row_num_beds = $result_num_beds->fetch_assoc();
$total_beds = $row_num_beds['total_beds'];

// Query to get the total collection or payments
$sql_total_payments = "SELECT SUM(amount_paid) AS total_payments FROM tblpayment";
$result_total_payments = $conn->query($sql_total_payments);
$row_total_payments = $result_total_payments->fetch_assoc();
$total_payments = $row_total_payments['total_payments'];

// Query to get the total collectibles
$sql_total_collectibles = "SELECT SUM(total_due) AS total_collectibles FROM tblinvoice WHERE status = 'unpaid'";
$result_total_collectibles = $conn->query($sql_total_collectibles);
$row_total_collectibles = $result_total_collectibles->fetch_assoc();
$total_collectibles = $row_total_collectibles['total_collectibles'];
?>

<!DOCTYPE html>
<html lang="en">

<?php include '../includes/header.php' ?>
<style>
  .hidden-column {
    display: none;
  }
</style>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <!-- Navbar -->
  <?php include '../includes/navbar.php' ?>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <?php include '../includes/sidebar.php' ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Dashboard</h1>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
                <h3><?php echo $num_active_tenants; ?></h3>
                <p>Number of Active Tenants</p>
              </div>
              <div class="icon">
                <i class="ion ion-bag"></i>
              </div>
              <a href="../tenant/tenant_list.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
                <h3><?php echo $total_beds; ?></h3>
                <p>Total Beds</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <a href="../bed/bed_list.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
              <div class="inner">
                <h3><?php echo number_format($total_payments, 2); ?></h3>
                <p>Total Collection (PHP)</p>
              </div>
              <div class="icon">
                <i class="ion ion-person-add"></i>
              </div>
              <a href="../report/payment_list.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3><?php echo number_format($total_collectibles, 2); ?></h3>
                <p>Total Collectibles (PHP)</p>
              </div>
              <div class="icon">
                <i class="ion ion-pie-graph"></i>
              </div>
              <a href="../report/collectibles_by_tenant.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
        </div>
        <div class="row">
          <!-- First Table: List of Due Dates -->
          <div class="col-lg-6">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">List of Due Dates (Greater than or Equal to Current Date)</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th class='hidden-column'>Invoice ID</th>
                      <th>Invoice Number</th>
                      <th>Due Date</th>
                      <!-- Add more columns if needed -->
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                        require_once('../includes/dbcon.php');
                        $sql = "SELECT invoice_id, invoice_number, due_date_iterate
                        FROM tblinvoice
                        WHERE CURDATE() >= STR_TO_DATE(due_date_iterate, '%M %d, %Y') and status ='unpaid'
                        ORDER BY due_date_iterate ASC;";
                        //use for MySQLi-OOP
                        $query = $conn->query($sql);
                        while($row = $query->fetch_assoc())
                        {
                          echo 
                          "<tr>
                            <td class='hidden-column'>".$row['invoice_id']."</td>
                            <td><a href='../invoice/invoice_list.php?invoice_number=".$row['invoice_number']."'</a>".$row['invoice_number']."</td>
                            <td>".$row['due_date_iterate']."</td>
                          </tr>";
                        }
                      ?>
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->

          <!-- Second Table: Upcoming Invoices -->
          <div class="col-lg-6">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Upcoming Invoices (7 Days Before Due Date)</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th class='hidden-column'>Invoice ID</th>
                      <th>Invoice Number</th>
                      <th>Due Date</th>
                      <!-- Add more columns if needed -->
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                        require_once('../includes/dbcon.php');
                        $sql = "SELECT invoice_id, invoice_number, due_date_iterate, DATEDIFF(CURDATE(), STR_TO_DATE(due_date_iterate, '%M %d, %Y')) AS days_difference
                        FROM tblinvoice
                        WHERE DATEDIFF(CURDATE(), STR_TO_DATE(due_date_iterate, '%M %d, %Y')) BETWEEN -7 AND -1 and status ='unpaid';";
                        //use for MySQLi-OOP
                        $query = $conn->query($sql);
                        while($row = $query->fetch_assoc())
                        {
                          echo 
                          "<tr>
                            <td class='hidden-column'>".$row['invoice_id']."</td>
                            <td><a href='../invoice/invoice_list.php?invoice_number=".$row['invoice_number']."'</a>".$row['invoice_number']."</td>
                            <td>".$row['due_date_iterate']."</td>
                          </tr>";
                        }
                      ?>
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
      </div><!-- /.container-fluid -->
      </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- Main Footer -->
  <?php include '../includes/dashboard_footer.php' ?>

</div>
<!-- ./wrapper -->

<?php include '../includes/footer.php' ?>

</body>
</html>
