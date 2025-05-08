function toggleMenu(event) {
  event.preventDefault(); 
  var menu = document.getElementById("menuDropdown");
  menu.style.display = menu.style.display === "block" ? "none" : "block";
}

document.addEventListener("click", function (event) {
  var menu = document.getElementById("menuDropdown");
  var userIcon = document.querySelector(".login");
  if (!userIcon.contains(event.target) && !menu.contains(event.target)) {
    menu.style.display = "none";
  }
});
