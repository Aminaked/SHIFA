<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservations</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
            text-align: left;
        }
        .status-pending {
            color: orange;
        }
        .status-confirmed {
            color: green;
        }
        .status-cancelled {
            color: red;
        }
    </style>
</head>
<body>
    <h1>Your Reservations</h1>
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Pharmacy Name</th>
                <th>Reservation Date</th>
                <th>Status</th>
                <th>Due Date</th>
                <th>Pharmacy Notes</th>
            </tr>
        </thead>
        <tbody id="reservationsBody">
            <!-- Reservations will be populated here by JavaScript -->
        </tbody>
    </table>

    <script src="../controllers/JavaScript/Display_Reservations.js"></script>
</body>
</html>
