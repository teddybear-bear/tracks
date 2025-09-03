<?php
$conn = new mysqli("localhost", "root", "", "trackings_systems");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['final_submit'])) {
    $customer_name = $_POST['customer_name'];
    $phone = $_POST['phone_number'];
    $address = $_POST['address'];
    $service_date = $_POST['service_date'];
    $warranty_expiration = $_POST['warranty_expiration'];
    $customer_service = $_POST['customer_service'];

    // ✅ Ensure service date is today only
    $today = date('Y-m-d');
    if ($service_date !== $today) {
        die("<script>alert('Service Date must be today only!'); window.history.back();</script>");
    }

    $imageName = $_FILES['proof_image']['name'];
    $imageTmp = $_FILES['proof_image']['tmp_name'];
    $imagePath = "uploads/" . basename($imageName);
    move_uploaded_file($imageTmp, $imagePath);

    $barcodeData = $_POST['barcode_image'];
    $barcodeImageName = "barcodes/" . time() . "_barcode.png";
    $barcodeData = str_replace('data:image/png;base64,', '', $barcodeData);
    $barcodeData = str_replace(' ', '+', $barcodeData);
    file_put_contents($barcodeImageName, base64_decode($barcodeData));

    $stmt = $conn->prepare("INSERT INTO warranty_records 
        (customer_name, phone_number, address, service_date, warranty_expiration, customer_service, proof_image, barcode_image) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $customer_name, $phone, $address, $service_date, $warranty_expiration, $customer_service, $imagePath, $barcodeImageName);
    $stmt->execute();

    $log_message = "Added new warranty record for " . $conn->real_escape_string($customer_name);
    $conn->query("INSERT INTO activity_log (action) VALUES ('$log_message')");

    echo "<script>alert('Record saved with barcode!'); window.location.href='view.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Warranty Tracking System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="index.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <style>
        #sidebar-wrapper, 
#sidebar-wrapper .list-group-item,
#sidebar-wrapper .sidebar-heading {
  font-family: 'Poppins', sans-serif !important;
}

        #loadingOverlay {
            display: none;
            position: fixed;
            z-index: 1050;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.73);
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        /* Sidebar Styles */
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

/* Responsive Sidebar */
@media (max-width: 768px) {
  #sidebar-wrapper {
    transform: translateX(-100%);
  }
  #wrapper.toggled #sidebar-wrapper {
    transform: translateX(0);
  }
}

    </style>
</head>
<!-- Sidebar -->
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
        <div class="form-container bg-white p-4 shadow rounded">
            <div class="d-flex align-items-center mb-3">
                <img src="logonila.png" alt="Logo" style="height: 50px; width: auto; margin-right: 10px;">
                <h2 class="mb-0">Tracking Warranty Form</h2>
            </div>

            <form id="warrantyForm" method="POST" enctype="multipart/form-data" oninput="generateBarcode()" novalidate>
                <div class="mb-3">
                    <label class="form-label">Customer Name:</label>
                    <input type="text" name="customer_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone Number:</label>
                    <input type="text" name="phone_number" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Address:</label>
                    <textarea name="address" class="form-control" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Service Date:</label>
                    <input type="date" name="service_date" id="service_date" class="form-control" readonly required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Warranty Expiration Date:</label>
                    <input type="date" name="warranty_expiration" id="warranty_expiration" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Customer Service:</label>
                    <input type="text" name="customer_service" id="customer_service" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Upload Proof Image:</label>
                    <input type="file" name="proof_image" accept="image/*" class="form-control" required>
                </div>

                <h5 class="mt-4 text-center">Generated Barcode</h5>
                <div id="barcode-wrapper" class="mx-auto">
                    <svg id="barcode"></svg>
                </div>

                <input type="hidden" name="barcode_image" id="barcode_image">
                <input type="hidden" name="final_submit" value="1">

                <div class="d-flex flex-column gap-2 mt-3">
                    <button type="button" class="btn btn-primary" onclick="openModal()">Submit</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">🖨️ Print Barcode</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="confirmModalLabel">Confirm Submission</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Are you sure you want to submit this warranty record?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-success" onclick="submitForm()">Yes, Submit</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Warning Modal -->
    <div class="modal fade" id="warningModal" tabindex="-1" aria-labelledby="warningModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content border-danger">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="warningModalLabel">Form Incomplete</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Please fill out all required fields before submitting the form.
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">OK</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading Screen -->
    <div id="loadingOverlay" class="d-none d-flex">
        <div class="text-center">
            <div style="font-size: 3rem;">⏳</div>
            <div>PROCESSING... Please wait</div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function generateBarcode() {
            const serviceDate = document.getElementById('service_date').value;
            const warrantyDate = document.getElementById('warranty_expiration').value;
            const customerService = document.getElementById('customer_service').value;

            const text = `Service: ${serviceDate}, Exp: ${warrantyDate}, CS: ${customerService}`;
            JsBarcode("#barcode", text, {
                format: "CODE128",
                displayValue: true,
                fontSize: 14,
                height: 50,
                margin: 0
            });

            setTimeout(() => {
                let svg = document.querySelector("#barcode");
                let xml = new XMLSerializer().serializeToString(svg);
                let svg64 = btoa(unescape(encodeURIComponent(xml)));
                let b64Start = 'data:image/svg+xml;base64,';
                let image64 = b64Start + svg64;

                let canvas = document.createElement("canvas");
                let ctx = canvas.getContext("2d");
                let img = new Image();
                img.onload = function () {
                    canvas.width = img.width;
                    canvas.height = img.height;
                    ctx.drawImage(img, 0, 0);
                    let png = canvas.toDataURL("image/png");
                    document.getElementById("barcode_image").value = png;
                };
                img.src = image64;
            }, 300);
        }

        function openModal() {
            const form = document.getElementById("warrantyForm");

            if (form.checkValidity()) {
                const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
                modal.show();
            } else {
                const warningModal = new bootstrap.Modal(document.getElementById('warningModal'));
                warningModal.show();
            }
        }

        function submitForm() {
            document.getElementById("loadingOverlay").classList.remove("d-none");
            document.getElementById("loadingOverlay").classList.add("d-flex");

            setTimeout(() => {
                document.getElementById('warrantyForm').submit();
            }, 1000);
        }

        window.addEventListener("DOMContentLoaded", () => {
            const today = new Date().toISOString().split("T")[0];
            const serviceDateInput = document.getElementById("service_date");
            serviceDateInput.value = today;
            generateBarcode();
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
      document.getElementById("add-record").classList.add("active");
    }
  });
</script>

</body>
</html>
