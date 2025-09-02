<?php 
$conn = new mysqli("localhost", "root", "", "trackings_systems");
$currentPage = basename($_SERVER['PHP_SELF']);
// Auto-expire past warranties
$expiredUpdate = $conn->query("UPDATE warranty_records SET status = 'EXPIRED' WHERE warranty_expiration < CURDATE() AND status != 'EXPIRED'");
if ($expiredUpdate && $conn->affected_rows > 0) {
    $log = $conn->prepare("INSERT INTO activity_log (action) VALUES (?)");
    $msg = "Auto-updated {$conn->affected_rows} record(s) to EXPIRED";
    $log->bind_param("s", $msg);
    $log->execute();
}

// Mark warranties as expiring soon
$soonQuery = $conn->query("
    UPDATE warranty_records 
    SET status = 'EXPIRING SOON' 
    WHERE status NOT IN ('EXPIRED', 'EXPIRING SOON') 
    AND DATEDIFF(warranty_expiration, CURDATE()) <= 7 
    AND DATEDIFF(warranty_expiration, CURDATE()) >= 0
");
if ($soonQuery && $conn->affected_rows > 0) {
    $log = $conn->prepare("INSERT INTO activity_log (action) VALUES (?)");
    $msg = "Marked {$conn->affected_rows} record(s) as EXPIRING SOON";
    $log->bind_param("s", $msg);
    $log->execute();
}

// Approve logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_id'])) {
    $id = intval($_POST['approve_id']);
    $check = $conn->query("SELECT status FROM warranty_records WHERE id = $id")->fetch_assoc();
    if ($check && $check['status'] === 'PENDING') {
        $conn->query("UPDATE warranty_records SET status = 'APPROVED' WHERE id = $id");
        $log = $conn->prepare("INSERT INTO activity_log (action) VALUES (?)");
        $msg = "Approved warranty record ID $id";
        $log->bind_param("s", $msg);
        $log->execute();
    }
}

// Delete logic
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $record = $conn->query("SELECT * FROM warranty_records WHERE id = $delete_id")->fetch_assoc();

    if ($record) {
        $stmt = $conn->prepare("INSERT INTO deleted_history (customer_name, phone_number, service_date, warranty_expiration, customer_service, proof_image, barcode_image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "ssssssss",
            $record['customer_name'],
            $record['phone_number'],
            $record['service_date'],
            $record['warranty_expiration'],
            $record['customer_service'],
            $record['proof_image'],
            $record['barcode_image'],
            $record['status']
        );
        $stmt->execute();

        $conn->query("DELETE FROM warranty_records WHERE id = $delete_id");

        $log = $conn->prepare("INSERT INTO activity_log (action) VALUES (?)");
        $msg = "Deleted warranty record ID $delete_id (backed up)";
        $log->bind_param("s", $msg);
        $log->execute();
    }
}

// Handle filters and search
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

if (!empty($search)) {
    $sql = "SELECT * FROM warranty_records 
            WHERE (customer_name LIKE '%$search%' 
            OR phone_number LIKE '%$search%' 
            OR status LIKE '%$search%')";
} elseif ($filter === 'expiring_soon') {
    $sql = "SELECT * FROM warranty_records WHERE status = 'EXPIRING SOON'";
} else {
    $sql = "SELECT * FROM warranty_records";
}
$result = $conn->query($sql);

// Count expiring soon
$soonCount = $conn->query("SELECT COUNT(*) as total FROM warranty_records WHERE status = 'EXPIRING SOON'")
                  ->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Warranty Records</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
  </styl
    </style>
</head>
<body class="bg-light">
  <div id="wrapper">
    <!-- Sidebar -->
    <div id="sidebar-wrapper">
      <div class="sidebar-heading p-3 text-center fw-bold border-bottom">📁 Menu</div>
      <div class="list-group list-group-flush">
        <a href="dashboard.php" class="list-group-item <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">📊 Dashboard</a>
        <a href="index.php" id="add-record" class="list-group-item">
   ➕ Add Record
</a>

        <a href="view.php" class="list-group-item <?= $currentPage === 'view.php' ? 'active' : '' ?>">📄 View Records</a>
        <a href="history.php" class="list-group-item <?= $currentPage === 'history.php' ? 'active' : '' ?>">🕓 History</a>
        <a href="actlog.php" class="list-group-item <?= $currentPage === 'actlog.php' ? 'active' : '' ?>">📝 Activity Log</a>
      </div>
    </div>

    <!-- Page Content -->
    <div id="page-content-wrapper">
      <nav class="navbar navbar-light bg-light border-bottom mb-4 px-3">
        <div class="w-100 d-flex justify-content-between align-items-center">
          <button class="btn btn-outline-dark" id="menu-toggle">☰</button>
          <h5 class="my-2">Anime Computer Services</h5>
        </div>
      </nav>
      <body class="bg-light">
<div class="container py-4">

    <?php if ($soonCount > 0): ?>
        <div class="alert alert-warning alert-dismissible fade show animated-reminder" role="alert">
            <strong>Reminder:</strong> You have <?= $soonCount ?> warranty record(s) expiring within 7 days.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <h2 class="mb-4 text-center">Warranty Tracking Records</h2>

    <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
        <div>
            <a href="history.php" class="btn btn-primary">View Deleted Records</a>
            <a href="?filter=expiring_soon" class="btn btn-warning <?= $filter === 'expiring_soon' ? 'active' : '' ?>">
                Expiring Soon (<?= $soonCount ?>)
            </a>
            <a href="?" class="btn btn-outline-secondary">Show All</a>
        </div>
        <form method="GET" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="Search..." 
                   value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-outline-primary">Search</button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle text-center">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Service Date</th>
                    <th>Expiration</th>
                    <th>Customer Service</th>
                    <th>Proof</th>
                    <th>Barcode</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = $result->fetch_assoc()) { ?>
                <tr class="<?= $row['status'] === 'EXPIRING SOON' ? 'table-warning' : '' ?>">
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['customer_name']) ?></td>
                    <td><?= htmlspecialchars($row['phone_number']) ?></td>
                    <td><?= htmlspecialchars($row['service_date']) ?></td>
                    <td><?= htmlspecialchars($row['warranty_expiration']) ?></td>
                    <td><?= htmlspecialchars($row['customer_service']) ?></td>
                    <td><img src="<?= htmlspecialchars($row['proof_image']) ?>" alt="proof" class="img-fluid" width="100"></td>
                    <td><img src="<?= htmlspecialchars($row['barcode_image']) ?>" alt="barcode" class="img-fluid" width="150"></td>
                    <td>
                        <strong>
                            <?= htmlspecialchars($row['status']) ?>
                            <?php if ($row['status'] === 'EXPIRING SOON'): ?> 🔔 <?php endif; ?>
                        </strong>
                    </td>
                    <td class="action-buttons">
                        <?php if ($row['status'] === 'PENDING') { ?>
                            <form method="POST">
                                <input type="hidden" name="approve_id" value="<?= $row['id'] ?>">
                                <button type="button" class="btn btn-success btn-sm trigger-approve" data-id="<?= $row['id'] ?>">✔ Check</button>
                            </form>
                        <?php } ?>
                        <a href="?delete_id=<?= $row['id'] ?>" class="btn btn-danger btn-sm trigger-delete">🗑 Delete</a>
                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm trigger-edit">✏ Edit</a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Please Confirm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="modalMessage">Are you sure?</p>
                <div class="spinner-border text-primary d-none" id="modalSpinner" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="modalCancel" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="modalConfirm" class="btn btn-primary">Yes, proceed</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap & Custom JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let actionType = null;
let actionId = null;
let actionUrl = null;
let approveForm = null;

document.querySelectorAll(".trigger-approve").forEach(btn => {
    btn.addEventListener("click", function (e) {
        e.preventDefault();
        actionType = "approve";
        actionId = this.dataset.id;
        approveForm = this.closest("form");
        document.getElementById("modalMessage").innerText = "Are you sure you want to APPROVE this record?";
        new bootstrap.Modal(document.getElementById("confirmModal")).show();
    });
});

document.querySelectorAll(".trigger-delete").forEach(btn => {
    btn.addEventListener("click", function (e) {
        e.preventDefault();
        actionType = "delete";
        actionUrl = this.getAttribute("href");
        document.getElementById("modalMessage").innerText = "Are you sure you want to DELETE this record?";
        new bootstrap.Modal(document.getElementById("confirmModal")).show();
    });
});

document.querySelectorAll(".trigger-edit").forEach(btn => {
    btn.addEventListener("click", function (e) {
        e.preventDefault();
        actionType = "edit";
        actionUrl = this.getAttribute("href");
        document.getElementById("modalMessage").innerText = "Proceed to edit this record?";
        new bootstrap.Modal(document.getElementById("confirmModal")).show();
    });
});

document.getElementById("modalConfirm").addEventListener("click", function () {
    const spinner = document.getElementById("modalSpinner");
    spinner.classList.remove("d-none");

    setTimeout(() => {
        spinner.classList.add("d-none");

        if (actionType === "approve" && approveForm) {
            approveForm.submit();
        } else if (actionType === "delete" || actionType === "edit") {
            window.location.href = actionUrl;
        }

        bootstrap.Modal.getInstance(document.getElementById("confirmModal")).hide();
    }, 3000);
});

// Optional: auto-dismiss reminder after 5 seconds
document.addEventListener("DOMContentLoaded", function () {
    const alertBox = document.querySelector('.alert.animated-reminder');
    if (alertBox) {
        setTimeout(() => {
            alertBox.classList.remove("show");
            alertBox.classList.add("fade");
        }, 5000);
    }
});
</script>
<script>
  document.getElementById("menu-toggle").addEventListener("click", function () {
    document.getElementById("wrapper").classList.toggle("toggled");
  });
</script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    let path = window.location.pathname.split("/").pop(); 
    
    if (path === "index.php") {
      document.getElementById("View Records").classList.add("active");
    }
  });
</script>
</body>
</html>
