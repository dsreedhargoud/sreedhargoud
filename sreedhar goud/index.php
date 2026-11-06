<?php
require_once "config/db.php";

$page = $_GET['page'] ?? 'dashboard';
$message = "";

// Escape output safely
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';

    // Add customer
    if ($action === "add_customer") {
        $name = trim($_POST['customer_name']);
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']);
        $address = trim($_POST['address']);

        $stmt = $conn->prepare(
            "INSERT INTO Customer (customer_name, phone, email, address)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssss", $name, $phone, $email, $address);

        if ($stmt->execute()) {
            $message = "Customer added successfully!";
        } else {
            $message = "Error adding customer: " . $stmt->error;
        }
    }

    // Add hall
    if ($action === "add_hall") {
        $name = trim($_POST['hall_name']);
        $location = trim($_POST['location']);
        $capacity = (int)$_POST['capacity'];
        $type = trim($_POST['hall_type']);
        $price = (float)$_POST['price'];

        $stmt = $conn->prepare(
            "INSERT INTO Halls (hall_name, location, capacity, hall_type, price)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssisd", $name, $location, $capacity, $type, $price);

        if ($stmt->execute()) {
            $message = "Hall added successfully!";
        } else {
            $message = "Error adding hall: " . $stmt->error;
        }
    }

    // Add booking
    if ($action === "add_booking") {
        $customer_id = (int)$_POST['customer_id'];
        $hall_id = (int)$_POST['hall_id'];
        $booking_date = $_POST['booking_date'];
        $event_date = $_POST['event_date'];
        $event_type = trim($_POST['event_type']);
        $status = $_POST['booking_status'];

        $stmt = $conn->prepare(
            "INSERT INTO Booking
             (customer_id, hall_id, booking_date, event_date, event_type, booking_status)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "iissss",
            $customer_id,
            $hall_id,
            $booking_date,
            $event_date,
            $event_type,
            $status
        );

        if ($stmt->execute()) {
            $message = "Booking created successfully!";
        } else {
            $message = "Error creating booking: " . $stmt->error;
        }
    }

    // Add payment
    if ($action === "add_payment") {
        $booking_id = (int)$_POST['booking_id'];
        $amount = (float)$_POST['amount'];
        $payment_date = $_POST['payment_date'];
        $method = $_POST['payment_method'];
        $status = $_POST['payment_status'];

        $stmt = $conn->prepare(
            "INSERT INTO Payment
             (booking_id, amount, payment_date, payment_method, payment_status)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "idsss",
            $booking_id,
            $amount,
            $payment_date,
            $method,
            $status
        );

        if ($stmt->execute()) {
            $message = "Payment recorded successfully!";
        } else {
            $message = "Error recording payment: " . $stmt->error;
        }
    }
}

// Dashboard counts
$customer_count = $conn->query("SELECT COUNT(*) AS total FROM Customer")->fetch_assoc()['total'];
$hall_count = $conn->query("SELECT COUNT(*) AS total FROM Halls")->fetch_assoc()['total'];
$booking_count = $conn->query("SELECT COUNT(*) AS total FROM Booking")->fetch_assoc()['total'];
$payment_count = $conn->query("SELECT COUNT(*) AS total FROM Payment")->fetch_assoc()['total'];

// Dropdown data
$customers = $conn->query("SELECT customer_id, customer_name FROM Customer ORDER BY customer_name");
$halls = $conn->query("SELECT hall_id, hall_name FROM Halls ORDER BY hall_name");
$bookings_for_payment = $conn->query("SELECT booking_id FROM Booking ORDER BY booking_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hall Booking Management System</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="sidebar">
    <h2>Hall Booking</h2>
    <p class="sidebar-subtitle">Management System</p>

    <a href="index.php?page=dashboard">Dashboard</a>
    <a href="index.php?page=customers">Customers</a>
    <a href="index.php?page=halls">Halls</a>
    <a href="index.php?page=bookings">Bookings</a>
    <a href="index.php?page=payments">Payments</a>
</div>

<div class="main">

    <header>
        <div>
            <h1>Hall Booking Management</h1>
            <p>Manage customers, halls, bookings and payments.</p>
        </div>
        <span class="admin-label">Admin Panel</span>
    </header>

    <?php if ($message !== ""): ?>
        <div class="message"><?php echo e($message); ?></div>
    <?php endif; ?>

    <?php if ($page === "dashboard"): ?>

        <h2 class="section-title">Dashboard Overview</h2>

        <div class="stats">
            <div class="stat-card">
                <h3>Total Customers</h3>
                <p><?php echo e($customer_count); ?></p>
            </div>

            <div class="stat-card">
                <h3>Total Halls</h3>
                <p><?php echo e($hall_count); ?></p>
            </div>

            <div class="stat-card">
                <h3>Total Bookings</h3>
                <p><?php echo e($booking_count); ?></p>
            </div>

            <div class="stat-card">
                <h3>Total Payments</h3>
                <p><?php echo e($payment_count); ?></p>
            </div>
        </div>

        <div class="welcome-card">
            <h2>Welcome to Hall Booking Management System</h2>
            <p>
                Use the navigation menu to manage customer details,
                hall information, bookings and payment records.
            </p>
        </div>

    <?php elseif ($page === "customers"): ?>

        <h2 class="section-title">Customer Management</h2>

        <div class="form-card">
            <h3>Add New Customer</h3>

            <form method="POST">
                <input type="hidden" name="action" value="add_customer">

                <label>Customer Name</label>
                <input type="text" name="customer_name" required>

                <label>Phone Number</label>
                <input type="text" name="phone" maxlength="15" required>

                <label>Email</label>
                <input type="email" name="email">

                <label>Address</label>
                <textarea name="address" rows="3"></textarea>

                <button type="submit">Add Customer</button>
            </form>
        </div>

        <h3 class="table-heading">Customer Records</h3>

        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Address</th>
                </tr>

                <?php
                $result = $conn->query("SELECT * FROM Customer ORDER BY customer_id DESC");
                while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo e($row['customer_id']); ?></td>
                    <td><?php echo e($row['customer_name']); ?></td>
                    <td><?php echo e($row['phone']); ?></td>
                    <td><?php echo e($row['email']); ?></td>
                    <td><?php echo e($row['address']); ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

    <?php elseif ($page === "halls"): ?>

        <h2 class="section-title">Hall Management</h2>

        <div class="form-card">
            <h3>Add New Hall</h3>

            <form method="POST">
                <input type="hidden" name="action" value="add_hall">

                <label>Hall Name</label>
                <input type="text" name="hall_name" required>

                <label>Location</label>
                <input type="text" name="location">

                <label>Capacity</label>
                <input type="number" name="capacity" min="1" required>

                <label>Hall Type</label>
                <select name="hall_type" required>
                    <option value="">Select Type</option>
                    <option value="Marriage Hall">Marriage Hall</option>
                    <option value="Conference Hall">Conference Hall</option>
                    <option value="Party Hall">Party Hall</option>
                    <option value="Function Hall">Function Hall</option>
                </select>

                <label>Price (₹)</label>
                <input type="number" name="price" min="0" step="0.01" required>

                <button type="submit">Add Hall</button>
            </form>
        </div>

        <h3 class="table-heading">Hall Records</h3>

        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Hall Name</th>
                    <th>Location</th>
                    <th>Capacity</th>
                    <th>Type</th>
                    <th>Price (₹)</th>
                </tr>

                <?php
                $result = $conn->query("SELECT * FROM Halls ORDER BY hall_id DESC");
                while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo e($row['hall_id']); ?></td>
                    <td><?php echo e($row['hall_name']); ?></td>
                    <td><?php echo e($row['location']); ?></td>
                    <td><?php echo e($row['capacity']); ?></td>
                    <td><?php echo e($row['hall_type']); ?></td>
                    <td>₹<?php echo number_format((float)$row['price'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

    <?php elseif ($page === "bookings"): ?>

        <h2 class="section-title">Booking Management</h2>

        <div class="form-card">
            <h3>Create New Booking</h3>

            <form method="POST">
                <input type="hidden" name="action" value="add_booking">

                <label>Select Customer</label>
                <select name="customer_id" required>
                    <option value="">Select Customer</option>
                    <?php while ($c = $customers->fetch_assoc()): ?>
                        <option value="<?php echo e($c['customer_id']); ?>">
                            <?php echo e($c['customer_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label>Select Hall</label>
                <select name="hall_id" required>
                    <option value="">Select Hall</option>
                    <?php while ($h = $halls->fetch_assoc()): ?>
                        <option value="<?php echo e($h['hall_id']); ?>">
                            <?php echo e($h['hall_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label>Booking Date</label>
                <input type="date" name="booking_date" required>

                <label>Event Date</label>
                <input type="date" name="event_date" required>

                <label>Event Type</label>
                <select name="event_type" required>
                    <option value="">Select Event</option>
                    <option value="Marriage">Marriage</option>
                    <option value="Birthday">Birthday</option>
                    <option value="Conference">Conference</option>
                    <option value="Reception">Reception</option>
                    <option value="Other">Other</option>
                </select>

                <label>Booking Status</label>
                <select name="booking_status" required>
                    <option value="Pending">Pending</option>
                    <option value="Confirmed">Confirmed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>

                <button type="submit">Create Booking</button>
            </form>
        </div>

        <h3 class="table-heading">Booking Records</h3>

        <div class="table-container">
            <table>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer</th>
                    <th>Hall</th>
                    <th>Booking Date</th>
                    <th>Event Date</th>
                    <th>Event Type</th>
                    <th>Status</th>
                </tr>

                <?php
                $sql = "SELECT b.*, c.customer_name, h.hall_name
                        FROM Booking b
                        INNER JOIN Customer c ON b.customer_id = c.customer_id
                        INNER JOIN Halls h ON b.hall_id = h.hall_id
                        ORDER BY b.booking_id DESC";

                $result = $conn->query($sql);

                while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo e($row['booking_id']); ?></td>
                    <td><?php echo e($row['customer_name']); ?></td>
                    <td><?php echo e($row['hall_name']); ?></td>
                    <td><?php echo e($row['booking_date']); ?></td>
                    <td><?php echo e($row['event_date']); ?></td>
                    <td><?php echo e($row['event_type']); ?></td>
                    <td><?php echo e($row['booking_status']); ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

    <?php elseif ($page === "payments"): ?>

        <h2 class="section-title">Payment Management</h2>

        <div class="form-card">
            <h3>Record New Payment</h3>

            <form method="POST">
                <input type="hidden" name="action" value="add_payment">

                <label>Select Booking ID</label>
                <select name="booking_id" required>
                    <option value="">Select Booking</option>
                    <?php while ($b = $bookings_for_payment->fetch_assoc()): ?>
                        <option value="<?php echo e($b['booking_id']); ?>">
                            Booking #<?php echo e($b['booking_id']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label>Amount (₹)</label>
                <input type="number" name="amount" min="0.01" step="0.01" required>

                <label>Payment Date</label>
                <input type="date" name="payment_date" required>

                <label>Payment Method</label>
                <select name="payment_method" required>
                    <option value="">Select Method</option>
                    <option value="Cash">Cash</option>
                    <option value="UPI">UPI</option>
                    <option value="Credit Card">Credit Card</option>
                    <option value="Debit Card">Debit Card</option>
                    <option value="Net Banking">Net Banking</option>
                </select>

                <label>Payment Status</label>
                <select name="payment_status" required>
                    <option value="Pending">Pending</option>
                    <option value="Paid">Paid</option>
                    <option value="Failed">Failed</option>
                </select>

                <button type="submit">Save Payment</button>
            </form>
        </div>

        <h3 class="table-heading">Payment Records</h3>

        <div class="table-container">
            <table>
                <tr>
                    <th>Payment ID</th>
                    <th>Booking ID</th>
                    <th>Amount</th>
                    <th>Payment Date</th>
                    <th>Method</th>
                    <th>Status</th>
                </tr>

                <?php
                $result = $conn->query("SELECT * FROM Payment ORDER BY payment_id DESC");

                while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo e($row['payment_id']); ?></td>
                    <td><?php echo e($row['booking_id']); ?></td>
                    <td>₹<?php echo number_format((float)$row['amount'], 2); ?></td>
                    <td><?php echo e($row['payment_date']); ?></td>
                    <td><?php echo e($row['payment_method']); ?></td>
                    <td><?php echo e($row['payment_status']); ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

    <?php else: ?>

        <div class="welcome-card">
            <h2>Page Not Found</h2>
            <p>Please select a valid option from the navigation menu.</p>
            <a href="index.php" class="back-link">Return to Dashboard</a>
        </div>

    <?php endif; ?>

    <footer>
        Hall Booking Management System | Mini Project
    </footer>

</div>

</body>
</html>