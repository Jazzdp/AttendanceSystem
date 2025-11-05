document.addEventListener('DOMContentLoaded', () => {
  const table = document.querySelector('.table-wrapper table');
  if (!table) return;

  // Find header rows (first two tr elements)
  const allRows = Array.from(table.querySelectorAll('tr'));
  if (allRows.length < 3) return; // need at least 2 header rows + 1 data row

  const headerCount = 2;
  const headerRow2 = allRows[1];

  // Count 'P' cells in second header row to determine sessions
  const sessions = Array.from(headerRow2.cells).filter(c => c.textContent.trim().toLowerCase() === 'p').length;

  const firstSessionIndex = 2; // Last, First name are at 0 and 1

  function updateRow(row) {
    const cells = row.cells;
    const absIndex = firstSessionIndex + sessions * 2;
    const partIndex = absIndex + 1;
    const messageIndex = partIndex + 1; // Message column after participation
    let absences = 0;
    let participation = 0;

    for (let s = 0; s < sessions; s++) {
      const pCell = cells[firstSessionIndex + s * 2];
      const paCell = cells[firstSessionIndex + s * 2 + 1];
      const p = pCell ? pCell.querySelector('input[type="checkbox"]') : null;
      const pa = paCell ? paCell.querySelector('input[type="checkbox"]') : null;
      if (p && !p.checked) absences++;
      if (pa && pa.checked) participation++;
    }

    if (cells[absIndex]) cells[absIndex].textContent = String(absences);
    if (cells[partIndex]) cells[partIndex].textContent = String(participation);

    // Apply row color and message based on absences
    let message = '';
    let rowColor = '';

    if (absences < 3) {
      rowColor = '#d4edda'; // Light green
      if (participation >= sessions * 0.7) {
        message = 'Good attendance – Excellent participation';
      } else {
        message = 'Good attendance – You need to participate more';
      }
    } else if (absences >= 3 && absences <= 4) {
      rowColor = '#fff3cd'; // Light yellow
      if (participation >= sessions * 0.5) {
        message = 'Warning – attendance low – Good participation';
      } else {
        message = 'Warning – attendance low – You need to participate more';
      }
    } else { // 5 or more absences
      rowColor = '#f8d7da'; // Light red
      if (participation >= sessions * 0.5) {
        message = 'Excluded – too many absences – Good participation';
      } else {
        message = 'Excluded – too many absences – You need to participate more';
      }
    }

    // Apply background color to the row
    row.style.backgroundColor = rowColor;

    // Set message in the message column
    if (cells[messageIndex]) {
      cells[messageIndex].textContent = message;
    }
  }

  // initialize all data rows
  const dataRows = allRows.slice(headerCount);
  dataRows.forEach(r => updateRow(r));

  // delegate change events from checkboxes to update their row
  table.addEventListener('change', (e) => {
    if (e.target && e.target.type === 'checkbox') {
      const row = e.target.closest('tr');
      if (row) updateRow(row);
    }
  });
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('studentForm');
    const studentId = document.getElementById('studentId');
    const lastName = document.getElementById('lastName');
    const firstName = document.getElementById('firstName');
    const email = document.getElementById('email');

    // Error message elements
    const studentIdError = document.getElementById('studentIdError');
    const lastNameError = document.getElementById('lastNameError');
    const firstNameError = document.getElementById('firstNameError');
    const emailError = document.getElementById('emailError');

    // Validation functions
    function validateStudentId() {
        const value = studentId.value.trim();
        if (value === '') {
            studentIdError.textContent = 'Student ID cannot be empty.';
            studentId.classList.add('invalid');
            return false;
        }
        if (!/^\d+$/.test(value)) {
            studentIdError.textContent = 'Student ID must contain only numbers.';
            studentId.classList.add('invalid');
            return false;
        }
        studentIdError.textContent = '';
        studentId.classList.remove('invalid');
        return true;
    }

    function validateLastName() {
        const value = lastName.value.trim();
        if (value === '') {
            lastNameError.textContent = 'Last Name cannot be empty.';
            lastName.classList.add('invalid');
            return false;
        }
        if (!/^[a-zA-Z\s]+$/.test(value)) {
            lastNameError.textContent = 'Last Name must contain only letters.';
            lastName.classList.add('invalid');
            return false;
        }
        lastNameError.textContent = '';
        lastName.classList.remove('invalid');
        return true;
    }

    function validateFirstName() {
        const value = firstName.value.trim();
        if (value === '') {
            firstNameError.textContent = 'First Name cannot be empty.';
            firstName.classList.add('invalid');
            return false;
        }
        if (!/^[a-zA-Z\s]+$/.test(value)) {
            firstNameError.textContent = 'First Name must contain only letters.';
            firstName.classList.add('invalid');
            return false;
        }
        firstNameError.textContent = '';
        firstName.classList.remove('invalid');
        return true;
    }

    function validateEmail() {
        const value = email.value.trim();
        if (value === '') {
            emailError.textContent = 'Email cannot be empty.';
            email.classList.add('invalid');
            return false;
        }
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            emailError.textContent = 'Email must be in valid format (e.g., name@example.com).';
            email.classList.add('invalid');
            return false;
        }
        emailError.textContent = '';
        email.classList.remove('invalid');
        return true;
    }
     studentId.addEventListener('blur', validateStudentId);
    lastName.addEventListener('blur', validateLastName);
    firstName.addEventListener('blur', validateFirstName);
    email.addEventListener('blur', validateEmail);

    // Form submission
    form.addEventListener('submit', (e) => {
        e.preventDefault();

        // Validate all fields
        const isStudentIdValid = validateStudentId();
        const isLastNameValid = validateLastName();
        const isFirstNameValid = validateFirstName();
        const isEmailValid = validateEmail();

        // Check if all validations passed
        if (isStudentIdValid && isLastNameValid && isFirstNameValid && isEmailValid) {
            alert('Form submitted successfully!');
            // Here you would normally submit the form data
            // form.submit(); // Uncomment to actually submit
            form.reset(); // Reset form after successful validation
        } else {
            alert('Please fix the errors before submitting.');
        }
    });
});
});
