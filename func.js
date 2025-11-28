// Exo1 - Vanilla JavaScript for attendance tracking
document.addEventListener('DOMContentLoaded', () => { // Ensure DOM is fully loaded
    const table = document.querySelector('#attendanceTable'); // Attendance table
    if (table) {
        const allRows = Array.from(table.querySelectorAll('tbody tr')); // All student rows
        const sessions = 6; // Total number of sessions
        const firstSessionIndex = 2;// Index of first session presence checkbox

        function updateRow(row) {
            const cells = row.cells; // HTMLCollection of cells in the row
            const absIndex = firstSessionIndex + sessions * 2; // Absences cell index
            const partIndex = absIndex + 1; // Participation cell index
            const messageIndex = partIndex + 1; // Message cell index
            let absences = 0; 
            let participation = 0;

            for (let s = 0; s < sessions; s++) {
                const pCell = cells[firstSessionIndex + s * 2]; // Presence cell
                const paCell = cells[firstSessionIndex + s * 2 + 1]; // Participation cell
                const p = pCell ? pCell.querySelector('input[type="checkbox"]') : null;// Presence checkbox
                const pa = paCell ? paCell.querySelector('input[type="checkbox"]') : null; // Participation checkbox
                if (p && !p.checked) absences++; // Count absence
                if (pa && pa.checked) participation++; // Count participation
            }

            if (cells[absIndex]) cells[absIndex].textContent = String(absences);// Update absences cell
            if (cells[partIndex]) cells[partIndex].textContent = String(participation); // Update participation cell

            let message = ''; // Message to display
            let rowColor = ''; // Row background color

            if (absences < 3) {
                rowColor = '#3fbe78b9'; // Green for good attendance
                message = participation >= sessions * 0.7 
                    ? 'Good attendance – Excellent participation'  // Excellent participation
                    : 'Good attendance – You need to participate more'; // Low participation
            } else if (absences >= 3 && absences <= 4) {
                rowColor = '#e5d08aff'; // Yellow for moderate attendance
                message = participation >= sessions * 0.5 
                    ? 'Warning – attendance low – Good participation' // Good participation
                    : 'Warning – attendance low – You need to participate more'; // Low participation
            } else {
                rowColor = '#ff02136d'; // Red for poor attendance
                message = participation >= sessions * 0.5 
                    ? 'Excluded – too many absences – Good participation'   // Good participation
                    : 'Excluded – too many absences – You need to participate more'; // Low participation
            }

            row.style.backgroundColor = rowColor; // Set row background color
            if (cells[messageIndex]) { // Update message cell
                cells[messageIndex].textContent = message; // Set message
            }
        }

        allRows.forEach(r => updateRow(r)); // Initial update for all rows

        table.addEventListener('change', (e) => {
            if (e.target && e.target.type === 'checkbox') {
                const row = e.target.closest('tr'); // Get the row of the changed checkbox
                if (row) updateRow(row); // Update that row
            }
        });

        window.updateRow = updateRow; // Expose function globally
    }

    //  Add Student handler
    const studentForm = document.getElementById("studentForm"); // Student addition form
    if (studentForm) {
        studentForm.addEventListener("submit", function(event) { // On form submission
            event.preventDefault(); // Prevent default form submission

            const id = document.getElementById("studentId").value.trim(); //  trim Student ID
            const lastName = document.getElementById("lastName").value.trim(); // Last name
            const firstName = document.getElementById("firstName").value.trim();
            const email = document.getElementById("email").value.trim();

            const idError = document.getElementById("idError"); // Error message elements
            const lastError = document.getElementById("lastError");
            const firstError = document.getElementById("firstError");
            const emailError = document.getElementById("emailError");

            idError.textContent = ""; // Clear previous errors
            lastError.textContent = "";
            firstError.textContent = "";
            emailError.textContent = "";

            let valid = true; // Validation flag
            const idPattern = /^[0-9]+$/; // Student ID: numbers only
            const namePattern = /^[A-Za-zÀ-ÿ\s'-]+$/; // Names: letters, spaces, hyphens, apostrophes
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Simple email regex

            if (id === "" || !idPattern.test(id)) {
                idError.textContent = "Student ID must contain only numbers."; // Set error message
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

            const tableBody = document.querySelector("#attendanceTable tbody"); // Table body
            if (!tableBody) return;

            const newRow = document.createElement("tr"); // Create new row

            const tdLast = document.createElement("td"); // Last name cell
            tdLast.textContent = lastName //set text for last name
            newRow.appendChild(tdLast); //add td to table 

            const tdFirst = document.createElement("td"); // First name cell
            tdFirst.textContent = firstName;
            newRow.appendChild(tdFirst);

            for (let s = 0; s < 6; s++) {
                const tdP = document.createElement("td");  //sessions cells 
                const inpP = document.createElement("input");
                inpP.type = "checkbox";
                tdP.appendChild(inpP);// Presence checkbox
                newRow.appendChild(tdP);// Add presence cell

                const tdPa = document.createElement("td");
                const inpPa = document.createElement("input");
                inpPa.type = "checkbox";
                tdPa.appendChild(inpPa); // Participation checkbox
                newRow.appendChild(tdPa); // Add participation cell
            }

            const tdAbs = document.createElement("td");
            tdAbs.textContent = ""; // Absences number cell
            newRow.appendChild(tdAbs);

            const tdPart = document.createElement("td");
            tdPart.textContent = ""; // Participation number cell
            newRow.appendChild(tdPart);

            const tdMsg = document.createElement("td"); // Message cell
            tdMsg.className = "message-col";
            tdMsg.textContent = "";
            newRow.appendChild(tdMsg);

            tableBody.appendChild(newRow); // Append new row to table body

            if (typeof window.updateRow === "function") {
                window.updateRow(newRow); // Update the new row
            }

            const confirmation = document.getElementById('confirmationMessage'); // Confirmation message element
            if (confirmation) {
                confirmation.textContent = `Student "${firstName} ${lastName}" added successfully!`; // Set confirmation text
                confirmation.style.display = 'block'; // Show confirmation
                setTimeout(() => { // Hide after 3 seconds
                    confirmation.style.display = 'none';
                }, 3000);
            }

            this.reset(); // Reset form fields
        });
    }
});

// jQuery features (Exercises 4-7)
$(document).ready(function() { // Ensure DOM is fully loaded
    function displayAbsencesChart() { // Display absences chart
        const sessions = 6;
        const firstSessionIndex = 2;
        const absencesPerSession = Array(sessions).fill(0); // Initialize absences count

        $('#attendanceTable tbody tr').each(function() { // Iterate over each student row
            for (let s = 0; s < sessions; s++) {
                const pCell = $(this).find('td').eq(firstSessionIndex + s * 2); // Presence cell
                const checkbox = pCell.find('input[type="checkbox"]');// Presence checkbox
                if (checkbox.length && !checkbox.is(':checked')) { // If absent
                    absencesPerSession[s]++; // Increment absence count for that session
                }
            } 
        });

        if (window.absencesChart) window.absencesChart.destroy(); // Destroy existing chart if any

        const ctx = document.getElementById('myChart').getContext('2d'); // Chart context
        window.absencesChart = new Chart(ctx, { // Create new chart
            type: 'bar',// Bar chart
            data: {
                labels: Array.from({ length: sessions }, (_, i) => `Session ${i + 1}`), // Session labels
                datasets: [{
                    label: 'Absences per Session', // Dataset label
                    data: absencesPerSession,
                    backgroundColor: ['#5421bcff','#ff56b3','#16a52eff','#b4db4fff','#8f4f16ff','#4285f4'],
                    borderColor: ['#5421bcff','#ff56b3','#16a52eff','#b4db4fff','#8f4f16ff','#4285f4'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true, // Responsive chart
                maintainAspectRatio: true, // Maintain aspect ratio
                scales: {
                    y: {
                        beginAtZero: true, // Y-axis starts at zero
                        ticks: { stepSize: 1 } // Y-axis step size
                    }
                }
            }
        });
    }

    $('#showReport').on('click', function() { // Show report button click
        if ($('#reportSection').is(':visible')) { // If report is already visible
            $('#reportSection').slideUp(); // Hide report
            return; 
        }

        const totalStudents = $('#attendanceTable tbody tr').length; // Total number of students
        let presentCount = 0; // Students with good attendance
        let participatedCount = 0; // Students with participation

        $('#attendanceTable tbody tr').each(function() { // Iterate over each student row
            const absences = parseInt($(this).find('td').eq(14).text()) || 0; // Absences count
            const participation = parseInt($(this).find('td').eq(15).text()) || 0; // Participation count

            if (absences < 6) presentCount++; // Count good attendance
            if (participation > 0) participatedCount++; // Count participation
        });
// Generate report HTML
        const reportHTML = `
            <h3>Attendance Report</h3>
            <div class="report-item"><strong>Total Students:</strong> ${totalStudents}</div>
            <div class="report-item"><strong>Students with Good Attendance:</strong> ${presentCount}</div>
            <div class="report-item"><strong>Students with Participation:</strong> ${participatedCount}</div>
            <canvas id="myChart" style="width:100%;max-width:700px"></canvas>
        `;

        $('#reportSection').html(reportHTML).slideDown(function() { // Show report section
            displayAbsencesChart(); // Display absences chart
        });
    });

    $('#attendanceTable tbody').on('mouseenter', 'tr', function() { // Row hover effect
        $(this).addClass('row-highlight'); // Add highlight class
    }).on('mouseleave', 'tr', function() { // Remove highlight on mouse leave
        $(this).removeClass('row-highlight'); // Remove highlight class
    }); 
 
    $('#attendanceTable tbody').on('click', 'tr', function(event) { // Row click event
        if (!$(event.target).is('input[type="checkbox"], label')) { // Ignore clicks on checkboxes/labels
            const lastName = $(this).find('td').eq(0).text(); // Get last name
            const firstName = $(this).find('td').eq(1).text();
            const absences = $(this).find('td').eq(14).text();
            alert(`Student: ${firstName} ${lastName}\nAbsences: ${absences}`); // Show alert with info
        }
    });

    $('#highlightExcellent').on('click', function() { // Highlight excellent attendance
        $('#attendanceTable tbody tr').each(function() { // Iterate over each student row
            const absences = parseInt($(this).find('td').eq(14).text()) || 0; // Absences count
            if (absences < 3) {
                $(this).fadeOut(1000).fadeIn(1000).fadeOut(1000).fadeIn(1000); // Flash effect
                this.origColor = this.style.backgroundColor; // Store original color
                this.style.backgroundColor = '#513dbdff'; // Change to highlight color
            }
        });
    });

    $('#resetColors').on('click', function() { // Reset row colors
        $('#attendanceTable tbody tr').each(function() { // Iterate over each student row
            if (window.updateRow) window.updateRow(this); // Reapply original coloring logic
        });
    });

    $('#searchBox').on('keyup', function() { // Search box keyup event
        const searchValue = $(this).val().toLowerCase(); // Get search value
        $('#attendanceTable tbody tr').each(function() { // Iterate over each student row
            const lastName = $(this).find('td').eq(0).text().toLowerCase(); // Get last name ignore case
            const firstName = $(this).find('td').eq(1).text().toLowerCase(); // Get first name ignore case
            if (lastName.includes(searchValue) || firstName.includes(searchValue)) $(this).show(); // Show matching rows
            else $(this).hide(); // Hide non-matching rows
        });
    });
 
    $('#sortByAbsences').on('click', function() { //sort by absences
        const tbody = $('#attendanceTable tbody'); //  pass Table body
        const rows = tbody.find('tr').get(); // Get all rows
        rows.sort((a, b) => { // Sort function
            const aAbs = parseInt($(a).find('td').eq(14).text()) || 0; // Absences for row a
            const bAbs = parseInt($(b).find('td').eq(14).text()) || 0; // Absences for row b
            return aAbs - bAbs; // Ascending order
        });
        $.each(rows, (_, row) => tbody.append(row)); // Re-append sorted rows
        $('#sortMessage').text('Currently sorted by absences (ascending)').show(); // Update sort message
    });

    $('#sortByParticipation').on('click', function() { // Sort by participation
        const tbody = $('#attendanceTable tbody'); //pass Table body
        const rows = tbody.find('tr').get(); // Get all rows
        rows.sort((a, b) => { // Sort function
            const aPar = parseInt($(a).find('td').eq(15).text()) || 0; // Participation for row a
            const bPar = parseInt($(b).find('td').eq(15).text()) || 0; // Participation for row b
            return bPar - aPar; // Descending order
        });
        $.each(rows, (_, row) => tbody.append(row)); // Re-append sorted rows
        $('#sortMessage').text('Currently sorted by participation (descending)').show(); // Update sort message
    });
});
