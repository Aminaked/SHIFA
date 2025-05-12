<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Requests</title>
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
    </style>
</head>
<body>
    <h1>Your Requests</h1>
    <table>
        <thead>
            <tr>
                <th>Medication Name</th>
                <th>Pharmacy Name</th>
                <th>Quantity</th>
                <th>Request Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="requestsBody">
            <!-- Requests will be populated here by JavaScript -->
        </tbody>
    </table>

    <script src="../controllers/JavaScript/Display_Requests.js"></script>
</body>
</html>
