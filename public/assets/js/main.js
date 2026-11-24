const toggleBtn = document.getElementById('toggle');
const cont = document.querySelector('.cont');
const doctorForm = document.getElementById('doctorForm');
const patientForm = document.getElementById('patientForm');
const userType = document.getElementById('usertype');

toggleBtn.addEventListener('click', () => {
  cont.classList.toggle('s--signup');
});

function switchForm() {
  // Only fields marked data-required become mandatory for doctors;
  // "About Me" and "Working Time" stay optional.
  const doctorInputs = doctorForm.querySelectorAll('input[data-required]');
  const isDoctor = userType.value === 'doctor';

  doctorForm.style.display = isDoctor ? 'grid' : 'none';
  doctorInputs.forEach(input => input.required = isDoctor);
}

// Ensure it runs after DOM is fully loaded
document.addEventListener('DOMContentLoaded', switchForm);
userType.addEventListener('change', switchForm);


