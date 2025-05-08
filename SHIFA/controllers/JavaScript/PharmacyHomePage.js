
function toggleMenu(event) {
    event.preventDefault();
    const menu = document.getElementById("menuDropdown");
    menu.style.display = menu.style.display === "block" ? "none" : "block";
  }
  
  // Close menu when clicking outside
  document.addEventListener("click", function (event) {
    const menu = document.getElementById("menuDropdown");
    const userIcon = document.querySelector(".login");
  
    if (menu && userIcon) {
      if (!userIcon.contains(event.target) && !menu.contains(event.target)) {
        menu.style.display = "none";
      }
    }
  });