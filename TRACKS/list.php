<?php
session_start();

// Initialize inventory list kung wala pa sa session
if (!isset($_SESSION['items'])) {
    $_SESSION['items'] = [
        "LAN Card" => [
            "Intel 82571 dual port Gigabit PCIE x1 LAN card",
            "Intel 82575 dual port Gigabit PCIE LAN card",
            "Intel 82599EN 10GbE SFP+ 1port Server PCIe Network Card",
            "Intel 82599T2 10GbE Dual Port SFP+ PCIe Network Card",
            "Intel i210 1GbE SFP+ 1port PCIe Server Network Card",
            "Intel i210-T1 1GbE Single port PCIE LAN Card",
            "Intel i225-V 2.5GbE Single port PCIE LAN Card",
            "Intel i350-AM4 Quad Port PCIE x4 Gigabit LAN Card",
            "Intel i350-T4 Quad Port PCIE Gigabit LAN Card",
            "Realtek RTL8126 5Gbps single port x1 PCIe Card",
            "Fiber Module SFP-10G-SR (SFP+ 10G 850nm 300m LC)"
        ],
        "Keyboard and Mouse" => [
            "Clever G10 Keyboard and Mouse USB"
        ],
        "Laptop" => [
            "Dell 7450 i5 5th gen laptop",
            "Acer Aspire 3 A314-36P-P6WW 8GB DDR5, 512GB SSD, Intel N200"
        ],
        "Micro SD Card" => [
            "128GB Imou Micro SD Card"
        ],
        "Monitor" => [
            "19\" Fuzion FN-19EL LED Monitor",
            "22\" Fuzion FN-22EL LED Monitor",
            "22\" Nvision H22V9 LED Monitor",
            "24\" Fuzion FN-24EL LED Monitor",
            "27\" Fuzion FN-27EL LED Monitor",
            "1.5M VGA Cable"
        ],
        "Motherboard" => [
            "MSI B450M-A Pro AM4 Max Motherboard",
            "Cervvo A88 FM2 Motherboard",
            "Gigabyte H610M-K FCLGA1700 Motherboard"
        ],
        "Network" => [
            "8port Dahua Fast Ethernet Switch Hub",
            "RJ45 Connector Classic",
            "Intel AX210 5400Mbps PCIe Wifi & Bluetooth"
        ],
        "CCTV Accessories" => [
            "12V 2A Power adaptor outdoor",
            "BNC Connector",
            "DC Connector Female",
            "Secuble 100M RG6 with 2C power",
            "Secuble 300M RG6 with 2C power",
            "4Way Power Splitter",
            "Video Balun Clip Type"
        ],
        "CCTV Camera" => [
            "2MP Dahua HFW1200CN-A Bullet IR Camera",
            "Dahua 2MP HFW1239CN-A-LED 2.8mm Full Color HDCVI",
            "2MP Dahua DH-IPC-HFW1230DTN-STW IP Camera"
        ],
        "Junction Box" => [
            "IP65 Junction Box 100mm"
        ],
        "CCTV Powersupply" => [
            "12V 2A Power Adaptor Indoor",
            "12V 5A 8CH Hikvision DS-2FA1205-C8"
        ],
        "Charger" => [
            "Lenovo Charger 20V/2.25A",
            "Clever Universal Charger"
        ],
        "CPU Processor" => [
            "AMD A8 7680 Processor",
            "AMD Ryzen 5 4500 (w/o Graphics) AM4 Processor",
            "Intel Core i3 12100 FCLGA1700 Processor",
            "Intel Core i5 13400 FCLGA1700 Processor"
        ],
        "DVR" => [
            "8CH Dahua XVR1B08-I Cooper XVR"
        ],
        "HDD" => [
            "1TB Seagate Skyhawk SATA",
            "2TB Seagate One Touch USB3.0 External HDD",
            "500GB Toshiba HDD",
            "8TB Toshiba S300 3.5” Surveillance HDD"
        ],
        "Headset" => [
            "A4tech B20 Wireless Earphone"
        ],
        "Heatsink Fan" => [
            "AMD AM4 Original Heatsink Fan",
            "Intel Original Heatsink Fan"
        ]
    ];
}

// Add item
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_item'])) {
    $category = trim($_POST['category']);
    $item = trim($_POST['item']);
    if (!empty($category) && !empty($item)) {
        $_SESSION['items'][$category][] = $item;
    }
}

// Delete item
if (isset($_GET['delete']) && isset($_GET['category'])) {
    $cat = $_GET['category'];
    $id = $_GET['delete'];
    if (isset($_SESSION['items'][$cat][$id])) {
        unset($_SESSION['items'][$cat][$id]);
        $_SESSION['items'][$cat] = array_values($_SESSION['items'][$cat]); // reindex
    }
}

$items = $_SESSION['items'];

// Sort categories: most items first
uasort($items, function($a, $b) {
    return count($b) <=> count($a);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Computer Services - Inventory List</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
      body { background: #121212; color: #fff; }
      .card { background: #1e1e1e; border: none; margin-bottom: 20px; }
      .card-header { background: #00ff99; color: #000; font-weight: bold; }
      .list-group-item { background: #1e1e1e; color: #fff; border: none; border-bottom: 1px solid #333; }
      .list-group-item:hover { background: #292929; }
      .btn-delete { font-size: 0.8rem; }
  </style>
</head>
<body>
<div class="container py-4">
    <h2 class="text-center mb-4">📋 Item Inventory List</h2>

    <!-- Search bar -->
    <div class="mb-4">
        <input type="text" id="searchBox" class="form-control" placeholder="🔍 Search item...">
    </div>

    <!-- Add item form -->
    <div class="card mb-4">
        <div class="card-header">➕ Add New Item</div>
        <div class="card-body">
            <form method="post">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="category" class="form-control" placeholder="Category" required>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="item" class="form-control" placeholder="Item Name" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="add_item" class="btn btn-success w-100">Add</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Item list -->
    <div class="row" id="itemList">
        <?php foreach ($items as $category => $list): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header"><?= htmlspecialchars($category) ?> (<?= count($list) ?>)</div>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($list as $id => $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><?= htmlspecialchars($item) ?></span>
                                <a href="?delete=<?= $id ?>&category=<?= urlencode($category) ?>" 
                                   class="btn btn-sm btn-danger btn-delete">Delete</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// Live search
document.getElementById("searchBox").addEventListener("keyup", function() {
    let filter = this.value.toLowerCase();
    document.querySelectorAll("#itemList li").forEach(function(item) {
        let text = item.textContent.toLowerCase();
        item.style.display = text.includes(filter) ? "" : "none";
    });
});
</script>
</body>
</html>
