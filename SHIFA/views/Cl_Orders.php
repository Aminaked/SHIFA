<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Orders</title>
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
        .cancel-button {
            padding: 5px 10px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .cancel-button:disabled {
            background-color: #aaa;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <h1>Your Orders</h1>
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Pharmacy Name</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Order Date</th>
                <th>Status</th>
                <th>Due Date</th>
                <th>Pharmacy Notes</th>
                <th>Cancel Order</th>
            </tr>
        </thead>
        <tbody id="ordersBody">
            <!-- Orders will be populated here by JavaScript -->
        </tbody>
    </table>

    <script src="../controllers/JavaScript/Display_orders.js"></script>
</body>
</html>
