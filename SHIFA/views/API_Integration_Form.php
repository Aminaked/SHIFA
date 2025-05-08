<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link
    href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css"
    rel="stylesheet"
  />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
  />
  <link rel="stylesheet" href="../public/styles/API_Integration_Form.css" />
  <title>API Integration Form</title>
</head>
<body>
    
    
<header>
    <div class="logo"><i class="fa-solid fa-pills"></i> SHIFA <span>Online</span>
</div>
     
      <div class="header-buttons">
      <a href="../views/aboutus.php"> <button class="chat-btn">About Us</button></a>
        <a href="../views/AppUsers.php" class="login"> LOG IN <i class="fa-solid fa-user"></i></a>
      </div>
    </header>
    
  <div class="form-wrapper">
    <h2>Pharmacy API Integration Form</h2>
    <form action="../controllers/Store_API_Form_Data.php" method="POST">
      <label for="pharmacy_name">Pharmacy Name</label>
      <input type="text" id="pharmacy_name" name="pharmacy_name" required />

      <label for="api_url">API URL:</label>
      <input type="url" id="api_url" name="api_url" required />

      <label for="api_key">API Key:</label>
      <input type="text" id="api_key" name="api_key" required />

      <label for="data_format">Data Format (JSON/XML):</label>
      <input type="text" id="data_format" name="data_format" />

      <label for="auth_method">Authentication Method:</label>
      <input type="text" id="auth_method" name="auth_method" />

      <label for="update_frequency">Update Frequency (e.g., daily, hourly):</label>
      <input type="text" id="update_frequency" name="update_frequency" />

      <label for="documentation_url">API Documentation URL:</label>
      <input type="url" id="documentation_url" name="documentation_url" />

      <button type="submit">Submit API Details</button>
    </form>
  </div>
</body>
</html>