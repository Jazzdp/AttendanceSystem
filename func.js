document.addEventListener('DOMContentLoaded', () => {
  // Table initialization
  const table = document.querySelector('.table-wrapper table');
  if (table) {
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
  }

  // Form handling
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

    
 
    // === Handle Form Submission ===
    document.getElementById("studentForm").addEventListener("submit", function(event) {
      event.preventDefault();

      const id = document.getElementById("studentId").value.trim();
      const lastName = document.getElementById("lastName").value.trim();
      const firstName = document.getElementById("firstName").value.trim();
      const email = document.getElementById("email").value.trim();

      const idError = document.getElementById("idError");
      const lastError = document.getElementById("lastError");
      const firstError = document.getElementById("firstError");
      const emailError = document.getElementById("emailError");

      idError.textContent = "";
      lastError.textContent = "";
      firstError.textContent = "";
      emailError.textContent = "";

      let valid = true;
      const idPattern = /^[0-9]+$/;
      const namePattern = /^[A-Za-zÀ-ÿ\s'-]+$/;
      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      if (id === "" || !idPattern.test(id)) {
        idError.textContent = "Student ID must contain only numbers.";
        valid = false;
      }
      if (lastName === "" || !namePattern.test(lastName)) {
        lastError.textContent = "Last name must contain only letters.";
        valid = false;
      }
      if (firstName === "" || !namePattern.test(firstName)) {
        firstError.textContent = "First name must contain only letters.";
        valid = false;
      }
      if (email === "" || !emailPattern.test(email)) {
        emailError.textContent = "Please enter a valid email address.";
        valid = false;
      }

      if (!valid) return;

      // === Create a new row in the attendance table ===
      const tableBody = document.querySelector("#attendanceTable tbody");
      const newRow = document.createElement("tr");

      // Create cells
      newRow.innerHTML = `
        <td class="LastName">${lastName}</td>
        <td class=FirstName>${firstName}</td>
        ${Array(6).fill('<td><input type="checkbox" class="attendance"></td>').join('')}
        ${Array(6).fill('<td><input type="checkbox" class="participation"></td>').join('')}
        <td class="absCount"></td>
        <td class="parCount"></td>
        <td class="message-col"></td>
      `;

      // Add to table
      tableBody.appendChild(newRow);

      // Attach checkbox listeners for new row
      newRow.querySelectorAll("input[type='checkbox']").forEach(cb => {
        cb.addEventListener("change", updateRow);
      alert(`Student "${firstName} ${lastName}" added successfully!`);
      this.reset();
    });
});
});
