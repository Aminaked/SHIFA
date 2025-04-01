const urlParams = new URLSearchParams(window.location.search);
const params = {
  pharmacyId: urlParams.get('pharmacy_id'),
  pharmacyName: decodeURIComponent(urlParams.get('pharmacy_name')),
  phoneNumber:decodeURIComponent(urlParams.get('phone_number')),
  address: decodeURIComponent(urlParams.get('address')),
 

};
const displayBasicInfo = () => {
    elements.brandName.textContent = params.brandName;
    elements.pharmacyName.textContent = params.pharmacyName;
    elements.address.textContent = params.address;
   
  };