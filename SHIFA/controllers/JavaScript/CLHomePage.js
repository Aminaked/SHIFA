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

if (navigator.geolocation) {
  navigator.geolocation.getCurrentPosition(function(position) {
   
    var userLat = position.coords.latitude;
    var userLon = position.coords.longitude;

    
    var latInput = document.createElement('input');
    latInput.type = 'hidden';
    latInput.name = 'user_lat';
    latInput.value = userLat;
    document.querySelector('form').appendChild(latInput);

    var lonInput = document.createElement('input');
    lonInput.type = 'hidden';
    lonInput.name = 'user_lon';
    lonInput.value = userLon;
    document.querySelector('form').appendChild(lonInput);
  }, function(error) {
    alert('Error: ' + error.message);
  });
} else {
  alert('Geolocation is not supported by this browser.');
}

