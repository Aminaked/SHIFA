
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/styles/ClientProfile.css">
    <title>SHIFA-Client Profile</title>
    
</head>
<body>
    <header>
        Profile
    </header>

    <section >
        
        <!-- Here, PHP will fetch and insert the client data -->
        <?php include('../controllers/CLprofile.php'); ?>
    </section>
    

   

    <footer>
        <p>&copy; 2025 Medication Finder</p>
    </footer>
</body>
</html>
