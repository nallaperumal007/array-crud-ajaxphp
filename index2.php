<?php
// Database credentials
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

// Create the table if it doesn't exist
$tableCreationQuery = "
CREATE TABLE IF NOT EXISTS information (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Doorno VARCHAR(50),
    name VARCHAR(100),
    address TEXT,
    details JSON
)";
$conn->query($tableCreationQuery);

// Function to find a record with the same Doorno, name, and address
function findRecord($conn, $Doorno, $name, $address) {
    $sql = "SELECT id, details FROM information WHERE Doorno=? AND name=? AND address=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $Doorno, $name, $address);
    $stmt->execute();
    $result = $stmt->get_result();
    $record = $result->fetch_assoc();
    $stmt->close();
    return $record;
}

// Function to get the next available detail `no` for new records
function getNextDetailNo($details) {
    $maxNo = 0;
    foreach ($details as $detail) {
        if ($detail['no'] > $maxNo) {
            $maxNo = $detail['no'];
        }
    }
    return $maxNo + 1;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
            'no' => $i + 1,  // Temporary `no` for new details
            'date' => $dates[$i],
            'time' => $times[$i],
            'amount' => $amounts[$i],
            'type' => $types[$i],
            'year' => $years[$i]
        ];
    }

    // Check if record with same Doorno, name, and address exists
    $existingRecord = findRecord($conn, $Doorno, $name, $address);

    if ($existingRecord) {
        // Append new details to existing record
        $id = $existingRecord['id'];
        $existingDetails = json_decode($existingRecord['details'], true);

        // Get the next available `no` for new details
        $nextNo = getNextDetailNo($existingDetails);

        // Update `no` for new details
        foreach ($detailsArray as &$detail) {
            $detail['no'] = $nextNo++;
        }
        unset($detail); // Unset reference

        // Merge and update details
        $existingDetails = array_merge($existingDetails, $detailsArray);
        $updatedDetailsJson = json_encode($existingDetails);

        $sql = "UPDATE information SET details=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $updatedDetailsJson, $id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Insert new record
        $detailsJson = json_encode($detailsArray);
        $sql = "INSERT INTO information (Doorno, name, address, details) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $Doorno, $name, $address, $detailsJson);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: index.php");
    exit();
}

// Handle record deletion
if (isset($_GET['delete']) && isset($_GET['no'])) {
    $id = $_GET['delete'];
    $no = $_GET['no'];

    // Fetch the existing record
    $sql = "SELECT details FROM information WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $record = $result->fetch_assoc();
    $details = json_decode($record['details'], true);
    $stmt->close();

    // Remove the specific detail
    $updatedDetails = array_filter($details, function($detail) use ($no) {
        return $detail['no'] != $no;
    });

    // Convert updated details to JSON
    $updatedDetailsJson = json_encode(array_values($updatedDetails));

    // Update the record in the database
    $sql = "UPDATE information SET details=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $updatedDetailsJson, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit();
}

// Handle record update
if (isset($_GET['update']) && isset($_GET['no'])) {
    $id = $_GET['update'];
    $no = $_GET['no'];

    // Fetch existing record for editing
    $sql = "SELECT * FROM information WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $record = $result->fetch_assoc();
    $details = json_decode($record['details'], true);
    $stmt->close();

    // Find the specific detail to update
    $detailToUpdate = null;
    foreach ($details as $detail) {
        if ($detail['no'] == $no) {
            $detailToUpdate = $detail;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Information Form</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 20px;
            background-color: #f0f8ff;
            color: #333;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #007bff;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .detail {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f8f9fa;
        }
        table {
            margin-top: 20px;
        }
        .table thead th {
            background-color: #007bff;
            color: white;
        }
        .table tbody tr:nth-of-type(even) {
            background-color: #f2f2f2;
        }
        .table tbody tr:hover {
            background-color: #e9ecef;
        }
        .modal-header {
            background-color: #007bff;
            color: white;
        }
        .modal-content {
            border-radius: 8px;
        }
    </style>
    <script>
        function addDetail() {
            const container = document.getElementById('details-container');
            const newDetail = container.querySelector('.detail').cloneNode(true);
            // Clear the values of the cloned detail
            const inputs = newDetail.querySelectorAll('input, select');
            inputs.forEach(input => input.value = '');
            container.appendChild(newDetail);
        }

        function openUpdateModal(id, no) {
            // Fetch existing record data via AJAX
            fetch(`get_record.php?id=${id}&no=${no}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalDoorno').value = data.Doorno;
                    document.getElementById('modalName').value = data.name;
                    document.getElementById('modalAddress').value = data.address;
                    
                    const container = document.getElementById('modal-details-container');
                    container.innerHTML = ''; // Clear existing details
                    
                    data.details.forEach(detail => {
                        const detailHtml = `
                            <div class="detail">
                                <div class="form-group">
                                    <label>Date:</label>
                                    <input type="date" name="date[]" class="form-control" value="${detail.date}" required>
                                </div>
                                <div class="form-group">
                                    <label>Time:</label>
                                    <input type="time" name="time[]" class="form-control" value="${detail.time}" required>
                                </div>
                                <div class="form-group">
                                    <label>Amount:</label>
                                    <input type="number" step="0.01" name="amount[]" class="form-control" value="${detail.amount}" required>
                                </div>
                                <div class="form-group">
                                    <label>Type:</label>
                                    <select name="type[]" class="form-control" required>
                                        <option value="water"${detail.type == 'water' ? ' selected' : ''}>Water</option>
                                        <option value="current"${detail.type == 'current' ? ' selected' : ''}>Current</option>
                                        <option value="House"${detail.type == 'House' ? ' selected' : ''}>House</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Year:</label>
                                    <input type="number" name="year[]" class="form-control" value="${detail.year}" required>
                                </div>
                                <input type="hidden" name="no[]" value="${detail.no}">
                                <hr>
                            </div>
                        `;
                        container.insertAdjacentHTML('beforeend', detailHtml);
                    });
                    
                    document.getElementById('updateModal').dataset.id = id;
                    $('#updateModal').modal('show');
                });
        }
    </script>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Information Form</h1>

        <!-- Modal -->
        <div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateModalLabel">Update Record</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="index.php" method="post">
                            <fieldset class="mb-4">
                                <legend>Main Information</legend>
                                <div class="form-group">
                                    <label for="modalDoorno">Doorno:</label>
                                    <input type="text" id="modalDoorno" name="Doorno" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="modalName">Name:</label>
                                    <input type="text" id="modalName" name="name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="modalAddress">Address:</label>
                                    <textarea id="modalAddress" name="address" class="form-control" required></textarea>
                                </div>
                            </fieldset>

                            <fieldset class="mb-4">
                                <legend>Details</legend>
                                <div id="modal-details-container">
                                    <!-- Dynamic details fields go here -->
                                </div>
                                <button type="button" class="btn btn-secondary" onclick="addDetail()">Add Another Detail</button>
                            </fieldset>

                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <h2>Submit New Record</h2>
        <form action="index.php" method="post">
            <fieldset class="mb-4">
                <legend>Main Information</legend>
                <div class="form-group">
                    <label for="Doorno">Doorno:</label>
                    <input type="text" id="Doorno" name="Doorno" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="address">Address:</label>
                    <textarea id="address" name="address" class="form-control" required></textarea>
                </div>
            </fieldset>

            <fieldset class="mb-4">
                <legend>Details</legend>
                <div id="details-container">
                    <div class="detail">
                        <div class="form-group">
                            <label>Date:</label>
                            <input type="date" name="date[]" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Time:</label>
                            <input type="time" name="time[]" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Amount:</label>
                            <input type="number" step="0.01" name="amount[]" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Type:</label>
                            <select name="type[]" class="form-control" required>
                                <option value="water">Water</option>
                                <option value="current">Current</option>
                                <option value="House">House</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Year:</label>
                            <input type="number" name="year[]" class="form-control" required>
                        </div>
                        <hr>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary" onclick="addDetail()">Add Another Detail</button>
            </fieldset>

            <button type="submit" class="btn btn-primary">Submit</button>
        </form>

        <h2 class="mt-5">Existing Records</h2>
        <?php
        $sql = "SELECT * FROM information";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<table class='table table-striped'>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Doorno</th>
                            <th>Name</th>
                            <th>Address</th>
                            <th>No</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Year</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>";

            while ($row = $result->fetch_assoc()) {
                $id = $row['id'];
                $details = json_decode($row['details'], true);
                foreach ($details as $detail) {
                    echo "<tr>
                            <td>{$id}</td>
                            <td>{$row['Doorno']}</td>
                            <td>{$row['name']}</td>
                            <td>{$row['address']}</td>
                            <td>{$detail['no']}</td>
                            <td>" . date('d.m.Y', strtotime($detail['date'])) . "</td>
                            <td>{$detail['time']}</td>
                            <td>{$detail['amount']}</td>
                            <td>{$detail['type']}</td>
                            <td>{$detail['year']}</td>
                            <td>
                                <button type='button' class='btn btn-warning btn-sm' onclick=\"openUpdateModal({$id}, {$detail['no']})\">Update</button> |
                                <a href='index.php?delete={$id}&no={$detail['no']}' class='btn btn-danger btn-sm' onclick=\"return confirm('Are you sure you want to delete this record?')\">Delete</a>
                            </td>
                        </tr>";
                }
            }
            echo "</tbody></table>";
        } else {
            echo "<p>No records found.</p>";
        }

        $conn->close();
        ?>

    </div>

    <!-- jQuery, Popper.js, and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
