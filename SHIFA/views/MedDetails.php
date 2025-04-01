<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/styles/MedDetails.css">
    <title>Document</title>
</head>
<body>
<div class="details">
    <h2 id="brand-name"></h2>
    <p><strong>Pharmacy:</strong> 
           <span id="pharmacy-name" class="pharmacy-link"></span>
      </p>
    <p><strong>Address:</strong> <span id="address"></span></p>
    <p><strong>Distance:</strong> <span id="distance"></span></p>
    <p><strong>Stock:</strong> <span id="stock"></span></p>
    <p><strong>Generic Name:</strong> <span id="generic-name"></span></p>
    <div id="loading-spinner" class="hidden">
            <div class="spinner"></div>
            <span>Loading medication details from FDA...</span>
        </div>
    <h3>Uses</h3>
  <p id="indications">Loading...</p>
  
  <h3>Dosage</h3>
  <p id="dosage">Loading...</p>
  
  <h3>Warnings</h3>
  <p id="warnings">Loading...</p>
  
  <h3>Contraindications</h3>
  <p id="contraindications">Loading...</p>
    <button id="locate-button" class="locate-button">Locate Pharmacy</button>
  </div>
  <script src="../controllers/JavaScript/MedDetails.js" ></script> 
</body>
</html>