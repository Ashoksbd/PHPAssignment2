<?php
// manage_shoes.php

require_once 'dbinit.php';

// Initialize variables
$errors = [];
$success = "";

$shoeName = $shoeDescription = $quantityAvailable = $price = $size = "";

// Define allowed sizes
$allowedSizes = ['6', '7', '8', '9', '10', '11', '12'];

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Determine action based on hidden 'action' field
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'add') {
        // Handle Add Shoe
        $shoeName = sanitize_input($_POST['shoeName']);
        $shoeDescription = sanitize_input($_POST['shoeDescription']);
        $quantityAvailable = sanitize_input($_POST['quantityAvailable']);
        $price = sanitize_input($_POST['price']);
        $size = sanitize_input($_POST['size']);

        // Validate inputs
        if (empty($shoeName)) {
            $errors['shoeName'] = "Shoe Name is required.";
        } elseif (strlen($shoeName) > 255) {
            $errors['shoeName'] = "Shoe Name must not exceed 255 characters.";
        }

        if (empty($shoeDescription)) {
            $errors['shoeDescription'] = "Shoe Description is required.";
        }

        if (empty($quantityAvailable)) {
            $errors['quantityAvailable'] = "Quantity Available is required.";
        } elseif (!filter_var($quantityAvailable, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]])) {
            $errors['quantityAvailable'] = "Valid Quantity Available is required.";
        }

        if (empty($price)) {
            $errors['price'] = "Price is required.";
        } elseif (!filter_var($price, FILTER_VALIDATE_FLOAT) || floatval($price) < 0) {
            $errors['price'] = "Valid Price is required.";
        }

        if (empty($size)) {
            $errors['size'] = "Size is required.";
        } elseif (!in_array($size, $allowedSizes)) {
            $errors['size'] = "Invalid Size selected.";
        }

        // If no errors, proceed to add shoe
        if (empty($errors)) {
            $stmt = $conn->prepare("INSERT INTO `shoes` (`ShoeName`, `ShoeDescription`, `QuantityAvailable`, `Price`, `ProductAddedBy`, `Size`) VALUES (?, ?, ?, ?, 'Ashok', ?)");
            if ($stmt === false) {
                error_log("Prepare failed: " . $conn->error);
                $errors['general'] = "An unexpected error occurred. Please try again later.";
            } else {
                // Corrected bind_param with 5 variables
                $stmt->bind_param("ssids", $shoeName, $shoeDescription, $quantityAvailable, $price, $size);

                if ($stmt->execute()) {
                    $success = "New shoe added successfully.";
                    // Reset form fields
                    $shoeName = $shoeDescription = $quantityAvailable = $price = $size = "";
                } else {
                    error_log("Execute failed: " . $stmt->error);
                    $errors['general'] = "An unexpected error occurred. Please try again later.";
                }

                $stmt->close();
            }
        }
    } elseif ($action === 'update') {
        // Handle Update Shoe
        $shoeID = isset($_POST['shoeID']) ? intval($_POST['shoeID']) : 0;
        $shoeName = sanitize_input($_POST['shoeName']);
        $shoeDescription = sanitize_input($_POST['shoeDescription']);
        $quantityAvailable = sanitize_input($_POST['quantityAvailable']);
        $price = sanitize_input($_POST['price']);
        $size = sanitize_input($_POST['size']);

        // Validate inputs
        if (empty($shoeName)) {
            $errors['shoeName'] = "Shoe Name is required.";
        } elseif (strlen($shoeName) > 255) {
            $errors['shoeName'] = "Shoe Name must not exceed 255 characters.";
        }

        if (empty($shoeDescription)) {
            $errors['shoeDescription'] = "Shoe Description is required.";
        }

        if (empty($quantityAvailable)) {
            $errors['quantityAvailable'] = "Quantity Available is required.";
        } elseif (!filter_var($quantityAvailable, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]])) {
            $errors['quantityAvailable'] = "Valid Quantity Available is required.";
        }

        if (empty($price)) {
            $errors['price'] = "Price is required.";
        } elseif (!filter_var($price, FILTER_VALIDATE_FLOAT) || floatval($price) < 0) {
            $errors['price'] = "Valid Price is required.";
        }

        if (empty($size)) {
            $errors['size'] = "Size is required.";
        } elseif (!in_array($size, $allowedSizes)) {
            $errors['size'] = "Invalid Size selected.";
        }

        
        if (empty($errors) && $shoeID > 0) {
            $stmt = $conn->prepare("UPDATE `shoes` SET `ShoeName`=?, `ShoeDescription`=?, `QuantityAvailable`=?, `Price`=?, `Size`=? WHERE `ShoeID`=?");
            if ($stmt === false) {
                error_log("Prepare failed: " . $conn->error);
                $errors['general'] = "An unexpected error occurred. Please try again later.";
            } else {
                $stmt->bind_param("ssidsi", $shoeName, $shoeDescription, $quantityAvailable, $price, $size, $shoeID);

                if ($stmt->execute()) {
                    $success = "Shoe updated successfully.";
                } else {
                    error_log("Execute failed: " . $stmt->error);
                    $errors['general'] = "An unexpected error occurred. Please try again later.";
                }

                $stmt->close();
            }
        }
    }
}


$editShoe = null;
if (isset($_GET['edit'])) {
    $editID = intval($_GET['edit']);
    if ($editID > 0) {
        $stmt = $conn->prepare("SELECT * FROM `shoes` WHERE `ShoeID`=?");
        if ($stmt) {
            $stmt->bind_param("i", $editID);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $editShoe = $result->fetch_assoc();
            }
            $stmt->close();
        }
    }
}


$shoes = [];
$sql = "SELECT * FROM `shoes` ORDER BY `ShoeID` DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $shoes[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Shoes - Pokhara Shoe House</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .table-responsive { max-height: 600px; }
    </style>
</head>
<body>
   
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="manage_shoes.php">Admin Portal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="manage_shoes.php">Manage Shoes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout (Ashok)</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5">
        <h2>Manage Shoes</h2>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($errors['general']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        
        <div class="card mb-4">
            <div class="card-header">
                Add New Shoe
            </div>
            <div class="card-body">
                <form method="POST" action="manage_shoes.php" novalidate>
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="shoeName" class="form-label">Shoe Name</label>
                        <input type="text" class="form-control <?php echo isset($errors['shoeName']) ? 'is-invalid' : ''; ?>" id="shoeName" name="shoeName" placeholder="Enter shoe name"
                               value="<?php echo htmlspecialchars($shoeName); ?>">
                        <?php if (isset($errors['shoeName'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['shoeName']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="shoeDescription" class="form-label">Shoe Description</label>
                        <textarea class="form-control <?php echo isset($errors['shoeDescription']) ? 'is-invalid' : ''; ?>" id="shoeDescription" name="shoeDescription" rows="3" 
                                  placeholder="Enter shoe description"><?php echo htmlspecialchars($shoeDescription); ?></textarea>
                        <?php if (isset($errors['shoeDescription'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['shoeDescription']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="quantityAvailable" class="form-label">Quantity Available</label>
                        <input type="number" class="form-control <?php echo isset($errors['quantityAvailable']) ? 'is-invalid' : ''; ?>" id="quantityAvailable" name="quantityAvailable" 
                               placeholder="Enter quantity available" value="<?php echo htmlspecialchars($quantityAvailable); ?>">
                        <?php if (isset($errors['quantityAvailable'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['quantityAvailable']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price ($)</label>
                        <input type="number" step="0.01" class="form-control <?php echo isset($errors['price']) ? 'is-invalid' : ''; ?>" id="price" name="price" 
                               placeholder="Enter price" value="<?php echo htmlspecialchars($price); ?>">
                        <?php if (isset($errors['price'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['price']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="size" class="form-label">Size</label>
                        <select class="form-select <?php echo isset($errors['size']) ? 'is-invalid' : ''; ?>" id="size" name="size">
                            <option value="">Select size</option>
                            <?php
                            foreach ($allowedSizes as $s) {
                                echo '<option value="' . htmlspecialchars($s) . '"' . ($size == $s ? ' selected' : '') . '>' . htmlspecialchars($s) . '</option>';
                            }
                            ?>
                        </select>
                        <?php if (isset($errors['size'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['size']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Shoe</button>
                </form>
            </div>
        </div>

        <!---- Shoes Table ---->
        <div class="card">
            <div class="card-header">
                All Shoes
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Shoe ID</th>
                            <th>Shoe Name</th>
                            <th>Description</th>
                            <th>Quantity Available</th>
                            <th>Price ($)</th>
                            <th>Size</th>
                            <th>Product Added By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($shoes)): ?>
                            <?php foreach($shoes as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['ShoeID']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ShoeName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ShoeDescription']); ?></td>
                                    <td><?php echo htmlspecialchars($row['QuantityAvailable']); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($row['Price'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars($row['Size']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ProductAddedBy']); ?></td>
                                    <td>
                                        <!-- Update Button Triggers Modal -->
                                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updateModal<?php echo $row['ShoeID']; ?>">
                                            Update
                                        </button>
                                        
                                        <!-- Delete Form -->
                                        <form method="POST" action="manage_shoes.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this shoe?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="shoeID" value="<?php echo htmlspecialchars($row['ShoeID']); ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>

                                        <!-- Update Modal -->
                                        <div class="modal fade" id="updateModal<?php echo $row['ShoeID']; ?>" tabindex="-1" aria-labelledby="updateModalLabel<?php echo $row['ShoeID']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="updateModalLabel<?php echo $row['ShoeID']; ?>">Update Shoe ID: <?php echo htmlspecialchars($row['ShoeID']); ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form method="POST" action="manage_shoes.php" novalidate>
                                                            <input type="hidden" name="action" value="update">
                                                            <input type="hidden" name="shoeID" value="<?php echo htmlspecialchars($row['ShoeID']); ?>">
                                                            <div class="mb-3">
                                                                <label for="shoeName<?php echo $row['ShoeID']; ?>" class="form-label">Shoe Name</label>
                                                                <input type="text" class="form-control <?php echo isset($errors['shoeName']) ? 'is-invalid' : ''; ?>" id="shoeName<?php echo $row['ShoeID']; ?>" name="shoeName" placeholder="Enter shoe name"
                                                                       value="<?php echo isset($shoeName) && $action === 'update' && $shoeID == $row['ShoeID'] ? htmlspecialchars($shoeName) : htmlspecialchars($row['ShoeName']); ?>">
                                                                <?php if (isset($errors['shoeName']) && $action === 'update' && $shoeID == $row['ShoeID']): ?>
                                                                    <div class="invalid-feedback">
                                                                        <?php echo htmlspecialchars($errors['shoeName']); ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="shoeDescription<?php echo $row['ShoeID']; ?>" class="form-label">Shoe Description</label>
                                                                <textarea class="form-control <?php echo isset($errors['shoeDescription']) ? 'is-invalid' : ''; ?>" id="shoeDescription<?php echo $row['ShoeID']; ?>" name="shoeDescription" rows="3" 
                                                                          placeholder="Enter shoe description"><?php echo isset($shoeDescription) && $action === 'update' && $shoeID == $row['ShoeID'] ? htmlspecialchars($shoeDescription) : htmlspecialchars($row['ShoeDescription']); ?></textarea>
                                                                <?php if (isset($errors['shoeDescription']) && $action === 'update' && $shoeID == $row['ShoeID']): ?>
                                                                    <div class="invalid-feedback">
                                                                        <?php echo htmlspecialchars($errors['shoeDescription']); ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="quantityAvailable<?php echo $row['ShoeID']; ?>" class="form-label">Quantity Available</label>
                                                                <input type="number" class="form-control <?php echo isset($errors['quantityAvailable']) ? 'is-invalid' : ''; ?>" id="quantityAvailable<?php echo $row['ShoeID']; ?>" name="quantityAvailable" 
                                                                       placeholder="Enter quantity available" value="<?php echo isset($quantityAvailable) && $action === 'update' && $shoeID == $row['ShoeID'] ? htmlspecialchars($quantityAvailable) : htmlspecialchars($row['QuantityAvailable']); ?>">
                                                                <?php if (isset($errors['quantityAvailable']) && $action === 'update' && $shoeID == $row['ShoeID']): ?>
                                                                    <div class="invalid-feedback">
                                                                        <?php echo htmlspecialchars($errors['quantityAvailable']); ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="price<?php echo $row['ShoeID']; ?>" class="form-label">Price ($)</label>
                                                                <input type="number" step="0.01" class="form-control <?php echo isset($errors['price']) ? 'is-invalid' : ''; ?>" id="price<?php echo $row['ShoeID']; ?>" name="price" 
                                                                       placeholder="Enter price" value="<?php echo isset($price) && $action === 'update' && $shoeID == $row['ShoeID'] ? htmlspecialchars($price) : htmlspecialchars($row['Price']); ?>">
                                                                <?php if (isset($errors['price']) && $action === 'update' && $shoeID == $row['ShoeID']): ?>
                                                                    <div class="invalid-feedback">
                                                                        <?php echo htmlspecialchars($errors['price']); ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="size<?php echo $row['ShoeID']; ?>" class="form-label">Size</label>
                                                                <select class="form-select <?php echo isset($errors['size']) ? 'is-invalid' : ''; ?>" id="size<?php echo $row['ShoeID']; ?>" name="size">
                                                                    <option value="">Select size</option>
                                                                    <?php
                                                                    foreach ($allowedSizes as $s) {
                                                                        $selected = (isset($size) && $action === 'update' && $shoeID == $row['ShoeID'] ? $size : $row['Size']) == $s ? ' selected' : '';
                                                                        echo '<option value="' . htmlspecialchars($s) . '"' . $selected . '>' . htmlspecialchars($s) . '</option>';
                                                                    }
                                                                    ?>
                                                                </select>
                                                                <?php if (isset($errors['size']) && $action === 'update' && $shoeID == $row['ShoeID']): ?>
                                                                    <div class="invalid-feedback">
                                                                        <?php echo htmlspecialchars($errors['size']); ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <button type="submit" class="btn btn-primary">Update Shoe</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End of Update Modal -->
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No shoes found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3">
        &copy; <?php echo date("Y"); ?> Pokhara Shoe House. All rights reserved.
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// Close the database connection
$conn->close();
?>
