<?php
$servername = "localhost";
$username = "root"; // Your MySQL username
$password = ""; // Your MySQL password
$dbname = "information"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = $_POST['id'];
        $no = $_POST['no'];

        // Get current details
        $stmt = $conn->prepare("SELECT details FROM information WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $details = json_decode($row['details'], true);
        // Remove the specific detail
        $details = array_filter($details, function($detail) use ($no) {
            return $detail['no'] != $no;
        });

        // Update the record
        $detailsJson = json_encode(array_values($details));
        $stmt = $conn->prepare("UPDATE information SET details = ? WHERE id = ?");
        $stmt->bind_param("si", $detailsJson, $id);
        $stmt->execute();
        echo json_encode(['status' => 'success']);
        exit();
    }

    if (isset($_POST['id'])) {
        $id = $_POST['id'];
        $Doorno = $_POST['Doorno'];
        $name = $_POST['name'];
        $address = $_POST['address'];
        $dates = $_POST['date'];
        $times = $_POST['time'];
        $amounts = $_POST['amount'];
        $types = $_POST['type'];
        $years = $_POST['year'];

        // Prepare details as JSON
        $detailsArray = [];
        for ($i = 0; $i < count($dates); $i++) {
            $detailsArray[] = [
                'no' => $i + 1,
                'date' => $dates[$i],
                'time' => $times[$i],
                'amount' => $amounts[$i],
                'type' => $types[$i],
                'year' => $years[$i]
            ];
        }

        // Update the record
        $detailsJson = json_encode($detailsArray);
        $stmt = $conn->prepare("UPDATE information SET Doorno = ?, name = ?, address = ?, details = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $Doorno, $name, $address, $detailsJson, $id);
        $stmt->execute();
        echo json_encode(['status' => 'success']);
        exit();
    }
}

// Handle retrieving a record for editing
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM information WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    echo json_encode($row);
}

$conn->close();
