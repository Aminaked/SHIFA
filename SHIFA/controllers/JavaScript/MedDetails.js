const urlParams = new URLSearchParams(window.location.search);
    const pharmacyId = urlParams.get('pharmacy_id');
    const brandName = decodeURIComponent(urlParams.get('brand_name'));
    const genericName = decodeURIComponent(urlParams.get('generic_name'));
    const stock = urlParams.get('stock');
    const distance = urlParams.get('distance');
    const pharmacyName = decodeURIComponent(urlParams.get('pharmacy_name'));
    const address = decodeURIComponent(urlParams.get('address'));
    const ph_latitude = urlParams.get('latitude');
    const ph_longitude = urlParams.get('longitude');

    document.getElementById('brand-name').textContent = brandName;
    document.getElementById('pharmacy-name').textContent = pharmacyName;
    document.getElementById('address').textContent = address;
    document.getElementById('distance').textContent = distance;
    document.getElementById('stock').textContent = stock;
    document.getElementById('generic-name').textContent = genericName;
    document.getElementById('locate-button').addEventListener('click', () => {
       
        window.location.href = `../views/Map.php?ph_latitude=${ph_latitude}&ph_longitude=${ph_longitude}`;
      });