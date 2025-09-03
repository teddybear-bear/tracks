<?php 
$conn = new mysqli("localhost", "root", "", "trackings_systems");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$expiringSoon = $conn->query("SELECT COUNT(*) as total FROM warranty_records WHERE status = 'EXPIRING SOON'")
                ->fetch_assoc()['total'] ?? 0;

$logs = $conn->query("SELECT * FROM activity_log ORDER BY timestamp DESC LIMIT 100");
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Activity Log</title>

  <!-- Bootstrap & Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

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

    .scroll-log {
      background: #fff;
      border-radius: 20px;
      padding: 20px;
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
      max-height: 420px;
      overflow-y: auto;
    }

    .table th {
      background-color: #343a40;
      color: white;
    }

    .animated-alert {
      animation: slideDownFade 0.5s ease-out;
    }

    @keyframes slideDownFade {
      from { opacity: 0; transform: translateY(-10px); }
      to   { opacity: 1; transform: translateY(0); }
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
          <h5 class="my-2">Activity Logs</h5>
        </div>
      </nav>

      <!-- Expiring Soon Alert -->
      <?php if ($expiringSoon > 0): ?>
        <div class="alert alert-warning text-center animated-alert">
          🔔 <strong>Notice:</strong> <?= $expiringSoon ?> record(s) will expire in the next 7 days.
        </div>
      <?php endif; ?>

      <!-- Filter/Search -->
      <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <input type="text" id="searchLog" class="form-control" placeholder="🔍 Search..." style="max-width: 220px;">
        <div>
          <input type="date" id="startDate" class="form-control d-inline-block me-1">
          <input type="date" id="endDate" class="form-control d-inline-block">
        </div>
        <button id="exportCSV" class="btn btn-outline-secondary">📤 Export CSV</button>
      </div>

      <!-- Logs Table -->
      <div class="scroll-log table-responsive">
        <table class="table table-bordered align-middle text-center">
          <thead>
            <tr>
              <th>#</th>
              <th>Action</th>
              <th>Date & Time</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($logs && $logs->num_rows > 0): 
              $i = 1;
              while ($row = $logs->fetch_assoc()): ?>
                <tr>
                  <td><?= $i++ ?></td>
                  <td><?= htmlspecialchars($row['action']) ?></td>
                  <td><?= date('M d, Y h:i A', strtotime($row['timestamp'])) ?></td>
                </tr>
              <?php endwhile;
            else: ?>
              <tr><td colspan="3">No activity found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div> <!-- end wrapper -->

  <script>
    document.getElementById("menu-toggle").addEventListener("click", function () {
      document.getElementById("wrapper").classList.toggle("toggled");
    });

    const searchInput = document.getElementById("searchLog");
    const startDate = document.getElementById("startDate");
    const endDate = document.getElementById("endDate");
    const rows = document.querySelectorAll("tbody tr");

    function filterLogs() {
      const query = searchInput.value.toLowerCase();
      const start = new Date(startDate.value);
      const end = new Date(endDate.value);
      rows.forEach(row => {
        const text = row.children[1].innerText.toLowerCase();
        const dateText = row.children[2].innerText;
        const logDate = new Date(dateText);
        const matchText = text.includes(query);
        const matchDate = (!startDate.value || logDate >= start) &&
                          (!endDate.value || logDate <= end);
        row.style.display = (matchText && matchDate) ? "" : "none";
      });
    }

    searchInput.addEventListener("input", filterLogs);
    startDate.addEventListener("change", filterLogs);
    endDate.addEventListener("change", filterLogs);

    document.getElementById("exportCSV").addEventListener("click", function() {
      let csv = "No,Action,Date & Time\n";
      rows.forEach(row => {
        if (row.style.display !== "none") {
          let cols = row.querySelectorAll("td");
          let line = Array.from(cols).map(td => `"${td.innerText}"`).join(",");
          csv += line + "\n";
        }
      });
      const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
      const url = URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.href = url;
      link.download = "activity_logs.csv";
      link.click();
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
