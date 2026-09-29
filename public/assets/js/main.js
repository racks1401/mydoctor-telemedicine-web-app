const toggleBtn = document.getElementById('toggle');
const cont = document.querySelector('.cont');
const doctorForm = document.getElementById('doctorForm');
const patientForm = document.getElementById('patientForm');
const userType = document.getElementById('usertype');

toggleBtn.addEventListener('click', () => {
  cont.classList.toggle('s--signup');
});

function switchForm() {
  const doctorInputs = doctorForm.querySelectorAll('input');

  if (userType.value === 'doctor') {
    doctorForm.style.display = 'grid';
    doctorInputs.forEach(input => input.required = true);
  } else {
    doctorForm.style.display = 'none';
    doctorInputs.forEach(input => input.required = false);
  }
}

// Ensure it runs after DOM is fully loaded
document.addEventListener('DOMContentLoaded', switchForm);
userType.addEventListener('change', switchForm);


