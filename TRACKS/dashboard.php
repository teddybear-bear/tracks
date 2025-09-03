<?php  
$conn = new mysqli("localhost", "root", "", "trackings_systems");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function countStatus($conn, $status = null) {
    $query = $status 
        ? "SELECT COUNT(*) as total FROM warranty_records WHERE status = '$status'"
        : "SELECT COUNT(*) as total FROM warranty_records";
    $result = $conn->query($query);
    return $result ? $result->fetch_assoc()['total'] : 0;
}

$total = countStatus($conn);
$pending = countStatus($conn, 'PENDING');
$approved = countStatus($conn, 'APPROVED');
$expired = countStatus($conn, 'EXPIRED');
$expiringSoon = countStatus($conn, 'EXPIRING SOON');

$logs = $conn->query("SELECT * FROM activity_log ORDER BY timestamp DESC LIMIT 10");
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Warranty Dashboard</title>

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f8f9fa;
    }

    #wrapper {
      display: flex;
      width: 100%;
      min-height: 100vh;
      overflow-x: hidden;
    }

    #sidebar-wrapper {
      width: 250px;
      background: linear-gradient(145deg, #212529, #343a40);
      color: #fff;
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      transform: translateX(0);
      transition: transform 0.3s ease-in-out;
      z-index: 1000;
      box-shadow: 3px 0 10px rgba(0, 0, 0, 0.2);
    }

    #wrapper.toggled #sidebar-wrapper {
      transform: translateX(-100%);
    }

    .sidebar-heading {
      font-size: 1.25rem;
      background: rgba(255, 255, 255, 0.05);
      letter-spacing: 1px;
    }

    .list-group-item {
      background-color: transparent;
      color: #adb5bd;
      padding: 15px 20px;
      font-weight: 500;
      border: none;
      transition: all 0.2s ease-in-out;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .list-group-item:hover {
      background-color: #495057;
      color: #fff;
      padding-left: 25px;
      font-weight: 600;
    }

    .list-group-item.active {
      background-color: #0d6efd;
      color: #fff;
    }

    #page-content-wrapper {
      flex: 1;
      padding: 20px;
      margin-left: 250px;
      transition: margin-left 0.3s ease-in-out;
      position: relative;
    }

    #wrapper.toggled #page-content-wrapper {
      margin-left: 0;
    }

    .card-box {
      border-radius: 20px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
      transition: 0.3s;
    }

    .card-box:hover {
      transform: translateY(-5px);
    }

    .scroll-log {
      max-height: 400px;
      overflow-y: auto;
    }

    .btn {
      border-radius: 30px;
      padding: 10px 25px;
    }

    .animated-alert {
      animation: slideDownFade 0.6s ease-out;
    }

    @keyframes slideDownFade {
      from {
        transform: translateY(-20px);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    .alert-top-right {
      position: absolute;
      top: 80px;
      right: 20px;
      z-index: 1050;
      min-width: 300px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

   

    @media (max-width: 768px) {
      #sidebar-wrapper {
        transform: translateX(-100%);
      }

      #wrapper.toggled #sidebar-wrapper {
        transform: translateX(0);
      }

      #page-content-wrapper {
        margin-left: 0 !important;
        width: 100%;
        padding: 15px;
      }

      .scroll-log {
        overflow-x: auto;
      }

      .scroll-log table {
        font-size: 0.85rem;
      }

      .alert-top-right {
        top: 100px;
        right: 10px;
        min-width: auto;
        max-width: 90%;
      }
    }
  </style>
</head>
<body>
  <div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <div id="sidebar-wrapper">
      <div class="sidebar-heading p-3 text-center fw-bold border-bottom">📁 Menu</div>
      <div class="list-group list-group-flush">
        <a href="dashboard.php" class="list-group-item <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">📊 Dashboard</a>
        <a href="index.php" class="list-group-item <?= $currentPage === 'index.php' ? 'active' : '' ?>">➕ Add Record</a>
        <a href="view.php" class="list-group-item <?= $currentPage === 'view.php' ? 'active' : '' ?>">📄 View Records</a>
        <a href="history.php" class="list-group-item <?= $currentPage === 'history.php' ? 'active' : '' ?>">🕓 History</a>
        <a href="actlog.php" class="list-group-item <?= $currentPage === 'actlog.php' ? 'active' : '' ?>">📝 Activity Log</a>
      </div>
    </div>

    <!-- Page content -->
    <div id="page-content-wrapper">
      <nav class="navbar navbar-light bg-light border-bottom mb-4 px-3">
        <div class="w-100 d-flex justify-content-between align-items-center">
          <button class="btn btn-outline-dark" id="menu-toggle">☰</button>
          <h5 class="my-2">Anime Computer Services</h5>
        </div>
      </nav>

      <!-- Top right alert -->
      <?php if ($expiringSoon > 0): ?>
      <div class="alert alert-warning alert-dismissible fade show animated-alert alert-top-right" role="alert">
        🔔 <strong>Notice:</strong> <?= $expiringSoon ?> record(s) will expire in the next 7 days.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>

      <div class="container-fluid">
        <!-- Stats Cards -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-3 mb-4">
          <div class="col">
            <div class="card card-box bg-primary text-white text-center">
              <div class="card-body">
                <h6>Total Records</h6>
                <p class="fs-4"><?= $total ?></p>
              </div>
            </div>
          </div>
          <div class="col">
            <div class="card card-box bg-warning text-dark text-center">
              <div class="card-body">
                <h6>Pending</h6>
                <p class="fs-4"><?= $pending ?></p>
              </div>
            </div>
          </div>
          <div class="col">
            <div class="card card-box bg-success text-white text-center">
              <div class="card-body">
                <h6>Approved</h6>
                <p class="fs-4"><?= $approved ?></p>
              </div>
            </div>
          </div>
          <div class="col">
            <div class="card card-box bg-danger text-white text-center">
              <div class="card-body">
                <h6>Expired</h6>
                <p class="fs-4"><?= $expired ?></p>
              </div>
            </div>
          </div>
          <div class="col">
            <div class="card card-box bg-warning-subtle border border-warning text-center">
              <div class="card-body">
                <h6>Expiring Soon</h6>
                <p class="fs-4"><?= $expiringSoon ?></p>
              </div>
            </div>
          </div>
        </div>

        <!-- Chart and Logs -->
        <div class="row mb-4">
          <div class="col-md-6 mb-3 mb-md-0">
            <div class="bg-white p-4 rounded shadow-sm h-100">
              <canvas id="statusChart"></canvas>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white p-4 rounded shadow-sm h-100 scroll-log table-responsive">
              <table class="table table-bordered text-center align-middle">
                <thead class="table-dark">
                  <tr>
                    <th>#</th>
                    <th>Action</th>
                    <th>Date & Time</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    if ($logs && $logs->num_rows > 0) {
                        $i = 1;
                        while($row = $logs->fetch_assoc()) { ?>
                            <tr>
                              <td><?= $i++ ?></td>
                              <td><?= htmlspecialchars($row['action']) ?></td>
                              <td><?= date('M d, Y h:i A', strtotime($row['timestamp'])) ?></td>
                            </tr>
                    <?php }} else { ?>
                      <tr><td colspan="3">No activity found.</td></tr>
                    <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div> <!-- end container-fluid -->
    </div> <!-- end page-content-wrapper -->
  </div> <!-- end wrapper -->

  <script>
    const ctx = document.getElementById('statusChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Pending', 'Approved', 'Expired', 'Expiring Soon'],
        datasets: [{
          label: 'Status',
          data: [<?= $pending ?>, <?= $approved ?>, <?= $expired ?>, <?= $expiringSoon ?>],
          backgroundColor: ['#ffc107', '#198754', '#dc3545', '#fd7e14']
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
      }
    });

    document.getElementById("menu-toggle").addEventListener("click", function () {
      document.getElementById("wrapper").classList.toggle("toggled");
    });

    // Auto-dismiss alert after 5 seconds
    setTimeout(() => {
      const alert = document.querySelector('.alert-top-right');
      if (alert) {
        bootstrap.Alert.getOrCreateInstance(alert).close();
      }
    }, 5000);
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
