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

// Handle search query
$searchResults = [];
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $doornoSearch = $_POST['search'];
    $recordsQuery = "SELECT * FROM information WHERE Doorno LIKE ?";
    $stmt = $conn->prepare($recordsQuery);
    $searchParam = '%' . $doornoSearch . '%';
    $stmt->bind_param("s", $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Default to empty results
    $result = [];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Records</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-5">Search Records by Doorno</h1>
        <form id="searchForm" method="POST">
            <div class="form-group">
                <label for="search">Doorno:</label>
                <input type="text" id="search" name="search" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-secondary">Search</button>
        </form>

        <h2 class="mt-5">Search Results</h2>
        <?php if ($result && $result->num_rows > 0): ?>
            <table class='table table-striped' id="recordsTable">
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
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php $details = json_decode($row['details'], true); ?>
                        <?php foreach ($details as $detail): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['Doorno']) ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['address']) ?></td>
                                <td><?= $detail['no'] ?></td>
                                <td><?= date('d.m.Y', strtotime($detail['date'])) ?></td>
                                <td><?= $detail['time'] ?></td>
                                <td><?= $detail['amount'] ?></td>
                                <td><?= $detail['type'] ?></td>
                                <td><?= $detail['year'] ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm" onclick="editRecord(<?= $row['id'] ?>)">Edit</button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteRecord(<?= $row['id'] ?>, <?= $detail['no'] ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No records found.</p>
        <?php endif; ?>
    </div>

    <!-- Edit Modal -->
    <div class="modal" id="editModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Record</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" name="id" id="editId">
                        <div class="form-group">
                            <label for="editDoorno">Doorno:</label>
                            <input type="text" id="editDoorno" name="Doorno" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="editName">Name:</label>
                            <input type="text" id="editName" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="editAddress">Address:</label>
                            <textarea id="editAddress" name="address" class="form-control" required></textarea>
                        </div>
                        <div id="editDetailsContainer"></div>
                        <button type="button" class="btn btn-secondary" onclick="addDetail()">Add Another Detail</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        function editRecord(id) {
            // Load existing data for the record
            $.get("update.php", { id: id }, function(data) {
                const record = JSON.parse(data);
                $('#editId').val(record.id);
                $('#editDoorno').val(record.Doorno);
                $('#editName').val(record.name);
                $('#editAddress').val(record.address);
                
                // Load details
                const details = JSON.parse(record.details);
                $('#editDetailsContainer').empty();
                details.forEach(detail => {
                    const detailDiv = document.createElement('div');
                    detailDiv.className = 'detail';
                    detailDiv.innerHTML = `
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
                                <option value="water" ${detail.type === 'water' ? 'selected' : ''}>Water</option>
                                <option value="current" ${detail.type === 'current' ? 'selected' : ''}>Current</option>
                                <option value="House" ${detail.type === 'House' ? 'selected' : ''}>House</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Year:</label>
                            <input type="number" name="year[]" class="form-control" value="${detail.year}" required>
                        </div>
                        <hr>
                    `;
                    $('#editDetailsContainer').append(detailDiv);
                });
                $('#editModal').modal('show');
            });
        }

        function deleteRecord(id, no) {
            if (confirm("Are you sure you want to delete this record?")) {
                $.post("update.php", { action: 'delete', id: id, no: no }, function(response) {
                    alert("Record deleted successfully!");
                    location.reload(); // Reload the page to reflect changes
                });
            }
        }

        $('#editForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: "update.php",
                data: $(this).serialize(),
                success: function(response) {
                    alert("Record updated successfully!");
                    $('#editModal').modal('hide');
                    location.reload();
                },
                error: function() {
                    alert("Error updating record.");
                }
            });
        });

        function addDetail() {
            const detailDiv = document.createElement('div');
            detailDiv.className = 'detail';
            detailDiv.innerHTML = `
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
            `;
            $('#editDetailsContainer').append(detailDiv);
        }
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
