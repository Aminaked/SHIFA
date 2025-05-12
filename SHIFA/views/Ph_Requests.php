<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Pharmacy Requests</title>
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
    <h1>Requests</h1>
    <table>
        <thead>
            <tr>
                <th>Client Name</th>
                <th>Phone Number</th>
                <th>Medication Name</th>
                <th>Quantity</th>
                <th>Client Notes</th>
                <th>Request Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="requestsBody">
            <!-- Requests will be populated here by JavaScript -->
        </tbody>
    </table>

    <script src="../controllers/JavaScript/Ph_Display_Requests.js"></script>
</body>
</html>
