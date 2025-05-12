<?php

require_once 'connection.php';
require_once 'session.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pharmacy_id = $_SESSION['user_id'];
 $conn = getDatabaseConnection();
$sql = "SELECT pharmacy_name, pharmacy_liscense_number, phone_number, email, profile_photo FROM pharmacy WHERE pharmacy_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pharmacy_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    $profilePhoto = !empty($row["profile_photo"]) ? "../uploads/" . $row["profile_photo"] : "../public/images/client.jpg";

    echo '
    <section class="profile-settings">
        <div class="settings-container">
            <div class="sidebar">
                <img src="' . htmlspecialchars($profilePhoto) . '" class="profile-pic" alt="Pharmacy Logo">
                <h2>' . htmlspecialchars($row['pharmacy_name']) . '</h2>
                <button class="change-photo-btn">Change Logo</button>
            </div>

            <div class="settings-form">
                <h2>Pharmacy Settings</h2>

                <div class="tab-buttons">
                    <button onclick="showTab(\'infoTab\')" class="tab-btn active">Pharmacy Info</button>
                    <button onclick="showTab(\'passwordTab\')" class="tab-btn">Password</button>
                </div>

                <div id="infoTab" class="tab-content active">
                    <form id="updateForm">
                        <label>Pharmacy Name</label>
                        <input type="text" name="pharmacy_name" id="pharmacy_name" value="' . htmlspecialchars($row['pharmacy_name']) . '" readonly>

                        <label>License Number</label>
                        <input type="text" name="pharmacy_liscense_number" id="pharmacy_liscense_number" value="' . htmlspecialchars($row['pharmacy_liscense_number']) . '" readonly>

                        <label>Email</label>
                        <input type="email" name="email" id="email" value="' . htmlspecialchars($row['email']) . '" readonly>

                        <label>Phone Number</label>
                        <input type="text" name="phone_number" id="phone_number" value="' . htmlspecialchars($row['phone_number']) . '" readonly>

                        <button type="button" id="editBtn" class="update-btn">Update</button>
                        <button type="submit" id="saveBtn" class="update-btn" style="display:none;">Update</button>
                        <button type="button" id="cancelBtn" class="cancel-btn" style="display:none;">Cancel</button>

                        <p id="message" style="color:green; margin-top:10px;"></p>
                    </form>
                </div>

                <div id="passwordTab" class="tab-content">
                    <form id="passwordForm">
                        <label>Old Password</label>
                        <input type="password" name="old_password" required>

                        <label>New Password</label>
                        <input type="password" name="new_password" required>

                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required>

                        <button type="submit" class="update-btn">Change Password</button>
                        <p id="passwordMessage" style="color:green; margin-top:10px;"></p>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script>
        const editBtn = document.getElementById("editBtn");
        const saveBtn = document.getElementById("saveBtn");
        const cancelBtn = document.getElementById("cancelBtn");
        const inputs = document.querySelectorAll("#updateForm input");

        editBtn.onclick = () => {
            inputs.forEach(input => input.removeAttribute("readonly"));
            editBtn.style.display = "none";
            saveBtn.style.display = "inline-block";
            cancelBtn.style.display = "inline-block";
        };
        cancelBtn.onclick = () => {
            window.location.reload();
        };

        document.getElementById("updateForm").onsubmit = function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            fetch("../controllers/updatePharmacyProfile.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.text())
            .then(msg => {
                document.getElementById("message").innerHTML = msg;
                inputs.forEach(input => input.setAttribute("readonly", true));
                editBtn.style.display = "inline-block";
                saveBtn.style.display = "none";
                cancelBtn.style.display = "none";
            });
        };

        document.getElementById("passwordForm").onsubmit = function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch("../controllers/updatePharmacyPassword.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.text())
            .then(msg => {
                document.getElementById("passwordMessage").innerHTML = msg;
                this.reset();
            });
        };

        function showTab(tabId) {
            document.querySelectorAll(".tab-content").forEach(tab => {
                tab.classList.remove("active");
            });
            document.querySelectorAll(".tab-btn").forEach(btn => {
                btn.classList.remove("active");
            });
            document.getElementById(tabId).classList.add("active");
            event.target.classList.add("active");
        }

        // Change Logo
        document.querySelector(".change-photo-btn").addEventListener("click", function () {
            const input = document.createElement("input");
            input.type = "file";
            input.accept = "image/*";
            input.style.display = "none";

            input.addEventListener("change", function () {
                const file = input.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append("photo", file);

                fetch("../controllers/uploadPharmacyPhoto.php", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.text())
                .then(msg => {
                    alert(msg); 
                    document.querySelector(".profile-pic").src = "../uploads/" + file.name + "?t=" + new Date().getTime();
                })
                .catch(err => {
                    alert("Error uploading photo.");
                    console.error(err);
                });
            });

            document.body.appendChild(input); 
            input.click();
        });
    </script>
    ';
} else {
    echo "No pharmacy data found.";
}

$stmt->close();
$conn->close();
?>