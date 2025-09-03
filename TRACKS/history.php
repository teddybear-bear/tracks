<?php
$conn = new mysqli("localhost", "root", "", "trackings_systems");

// Handle delete all action
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_all'])) {
    $conn->query("DELETE FROM deleted_history");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$result = $conn->query("SELECT * FROM deleted_history ORDER BY deleted_at DESC");
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Deleted Warranty History</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap + Fonts -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <style>
    body { font-family: 'Poppins', sans-serif; background: #f8f9fa; }

    #wrapper { display: flex; width: 100%; min-height: 100vh; overflow-x: hidden; }
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

    #menu-toggle {
      transition: transform 0.3s ease;
    }

    #menu-toggle:hover {
      transform: rotate(90deg);
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
    #sidebar-wrapper {
      width: 250px; background: linear-gradient(145deg, #212529, #343a40);
      color: #fff; position: fixed; top: 0; left: 0; height: 100vh;
      transform: translateX(0); transition: transform 0.3s ease-in-out;
      z-index: 1000; box-shadow: 3px 0 10px rgba(0, 0, 0, 0.2);
    }
    #wrapper.toggled #sidebar-wrapper { transform: translateX(-100%); }

    .sidebar-heading { font-size: 1.25rem; background: rgba(255, 255, 255, 0.05); }

    .list-group-item {
      background-color: transparent; color: #adb5bd;
      padding: 15px 20px; font-weight: 500; border: none;
      transition: all 0.2s; display: flex; align-items: center; gap: 10px;
    }
    .list-group-item:hover { background-color: #495057; color: #fff; padding-left: 25px; }
    .list-group-item.active { background-color: #0d6efd; color: #fff; }

    #page-content-wrapper {
      flex: 1; padding: 20px; margin-left: 250px;
      transition: margin-left 0.3s ease-in-out; position: relative;
    }
    #wrapper.toggled #page-content-wrapper { margin-left: 0; }

    #menu-toggle { transition: transform 0.3s ease; }
    #menu-toggle:hover { transform: rotate(90deg); }

    /* Table styling */
    table { border-collapse: collapse; width: 100%; margin-top: 10px; background: #fff; }
    th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
    img { max-width: 100px; max-height: 100px; }

    /* Buttons */
    .delete-all-btn {
      background-color: #dc3545; color: white; padding: 7px 15px;
      border: none; border-radius: 4px; cursor: pointer;
      float: right; margin-bottom: 15px;
    }
    .delete-all-btn:hover { background-color: #c82333; }

    /* Modal */
    .modal { display: none; position: fixed; z-index: 999; left: 0; top: 0;
      width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
    .modal-content {
      background: #fff; margin: 15% auto; padding: 20px;
      border-radius: 8px; width: 300px; text-align: center;
    }

    .modal-buttons { margin-top: 15px; display: flex; justify-content: space-around; }
    .modal-buttons button {
      padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;
    }
    .confirm-btn { background-color: #dc3545; color: #fff; }
    .cancel-btn { background-color: #6c757d; color: #fff; }
    .confirm-btn:hover { background-color: #c82333; }
    .cancel-btn:hover { background-color: #5a6268; }

    /* Loading */
    #loadingOverlay {
      display: none; position: fixed; z-index: 1000; top: 0; left: 0;
      width: 100%; height: 100%; background: rgba(0,0,0,0.7);
      justify-content: center; align-items: center;
      color: white; font-size: 20px; font-weight: bold;
    }

    @media (max-width: 768px) {
      #sidebar-wrapper { transform: translateX(-100%); }
      #wrapper.toggled #sidebar-wrapper { transform: translateX(0); }
      #page-content-wrapper { margin-left: 0 !important; width: 100%; }
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
        <h5 class="my-2">Deleted Warranty Records</h5>
      </div>
    </nav>

    <a class="btn btn-success mb-3" href="view.php">← Back to Records</a>
    <button class="delete-all-btn" onclick="openModal()">🗑️ Delete All</button>

    <!-- Modal -->
    <div id="confirmModal" class="modal">
      <div class="modal-content">
        <p style="font-size: 18px;">❗<strong> Confirm Permanent Deletion</strong></p>
        <p>Are you sure you want to delete <strong>ALL</strong> deleted records?</p>
        <form id="deleteForm" method="POST">
          <div class="modal-buttons">
            <button type="button" class="confirm-btn" onclick="submitWithLoading()">✅ Yes, Delete</button>
            <button type="button" class="cancel-btn" onclick="closeModal()">❌ Cancel</button>
          </div>
          <input type="hidden" name="delete_all" value="1">
        </form>
      </div>
    </div>

    <!-- Loading -->
    <div id="loadingOverlay">⏳ Deleting all records... Please wait.</div>

    <!-- Table -->
    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead class="table-dark">
          <tr>
            <th>ID</th><th>Customer</th><th>Phone</th>
            <th>Service Date</th><th>Expiration</th>
            <th>Customer Service</th><th>Proof Image</th>
            <th>Barcode</th><th>Status</th><th>Deleted At</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['customer_name']) ?></td>
              <td><?= htmlspecialchars($row['phone_number']) ?></td>
              <td><?= $row['service_date'] ?></td>
              <td><?= $row['warranty_expiration'] ?></td>
              <td><?= htmlspecialchars($row['customer_service']) ?></td>
              <td><?php if (!empty($row['proof_image'])): ?><img src="<?= $row['proof_image'] ?>" alt="Proof"><?php else: ?>No Image<?php endif; ?></td>
              <td><?php if (!empty($row['barcode_image'])): ?><img src="<?= $row['barcode_image'] ?>" alt="Barcode"><?php else: ?>No Barcode<?php endif; ?></td>
              <td><?= $row['status'] ?></td>
              <td><?= $row['deleted_at'] ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="10">No deleted records found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  document.getElementById("menu-toggle").addEventListener("click", function () {
    document.getElementById("wrapper").classList.toggle("toggled");
  });

  function openModal() { document.getElementById('confirmModal').style.display = 'block'; }
  function closeModal() { document.getElementById('confirmModal').style.display = 'none'; }
  window.onclick = function(event) {
    const modal = document.getElementById('confirmModal');
    if (event.target === modal) closeModal();
  }
  function submitWithLoading() {
    closeModal();
    document.getElementById('loadingOverlay').style.display = 'flex';
    setTimeout(() => { document.getElementById('deleteForm').submit(); }, 3000);
  }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
