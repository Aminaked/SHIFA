<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Integration Form</title>
</head>
<body>
    <h2>Pharmacy API Integration Form</h2>
    <form action="../controllers/Store_API_Form_Data.php" method="POST">
        <label for="pharmacy_name">Pharmacy Name</label>
        <input type="text" id="pharmacy_name" name="pharmacy_name" required><br><br>

        <label for="api_url">API URL:</label>
        <input type="url" id="api_url" name="api_url" required><br><br>

        <label for="api_key">API Key:</label>
        <input type="text" id="api_key" name="api_key"required ><br><br>

        <label for="data_format">Data Format (JSON/XML):</label>
        <input type="text" id="data_format" name="data_format" ><br><br>

        <label for="auth_method">Authentication Method:</label>
        <input type="text" id="auth_method" name="auth_method" ><br><br>

        <label for="update_frequency">Update Frequency (e.g., daily, hourly):</label>
        <input type="text" id="update_frequency" name="update_frequency" ><br><br>

        <label for="documentation_url">API Documentation URL:</label>
        <input type="url" id="documentation_url" name="documentation_url"><br><br>

        <button type="submit">Submit API Details</button>
    </form>
</body>
</html>
