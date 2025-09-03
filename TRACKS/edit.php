<?php
$conn = new mysqli("localhost", "root", "", "trackings_systems");

$id = intval($_GET['id']);
$record = $conn->query("SELECT * FROM warranty_records WHERE id = $id")->fetch_assoc();

if (!$record) {
    echo "Record not found.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['customer_name'];
    $phone = $_POST['phone_number'];
    $service_date = $_POST['service_date'];
    $expiration = $_POST['warranty_expiration'];
    $customer_service = $_POST['customer_service'];

    $conn->query("UPDATE warranty_records SET 
        customer_name = '$name',
        phone_number = '$phone',
        service_date = '$service_date',
        warranty_expiration = '$expiration',
        customer_service = '$customer_service'
        WHERE id = $id");

    // Log activity
    $log = $conn->prepare("INSERT INTO activity_log (action) VALUES (?)");
    $msg = "Edited warranty record ID $id";
    $log->bind_param("s", $msg);
    $log->execute();

    header("Location: view.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Warranty Record</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            margin: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="date"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button, a {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 20px;
            text-decoration: none;
            border: none;
            border-radius: 6px;
            font-weight: bold;
        }

        button {
            background-color: #28a745;
            color: white;
            cursor: pointer;
        }

        a {
            background-color: #dc3545;
            color: white;
            margin-left: 10px;
        }

        button:hover {
            background-color: #218838;
        }

        a:hover {
            background-color: #c82333;
        }

        @media (max-width: 600px) {
            .container {
                padding: 15px;
            }

            button, a {
                width: 100%;
                text-align: center;
                margin: 8px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Warranty Record ID <?= $id ?></h2>
        <form method="POST">
            <label>Customer Name:</label>
            <input type="text" name="customer_name" value="<?= htmlspecialchars($record['customer_name']) ?>" required>

            <label>Phone Number:</label>
            <input type="text" name="phone_number" value="<?= htmlspecialchars($record['phone_number']) ?>" required>

            <label>Service Date:</label>
            <input type="date" name="service_date" value="<?= $record['service_date'] ?>" required>

            <label>Warranty Expiration:</label>
            <input type="date" name="warranty_expiration" value="<?= $record['warranty_expiration'] ?>" required>

            <label>Customer Service:</label>
            <input type="text" name="customer_service" value="<?= htmlspecialchars($record['customer_service']) ?>" required>

            <button type="submit">Save Changes</button>
            <a href="view.php">Cancel</a>
        </form>
    </div>
</body>
</html>
