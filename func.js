// Exo1 - Vanilla JavaScript for attendance tracking
document.addEventListener('DOMContentLoaded', () => {
    const table = document.querySelector('#attendanceTable');
    if (table) {
        const allRows = Array.from(table.querySelectorAll('tbody tr'));
        const sessions = 6;
        const firstSessionIndex = 2;

        function updateRow(row) {
            const cells = row.cells;
            const absIndex = firstSessionIndex + sessions * 2;
            const partIndex = absIndex + 1;
            const messageIndex = partIndex + 1;
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

            let message = '';
            let rowColor = '';

            if (absences < 3) {
                rowColor = '#3fbe78b9';
                if (participation >= sessions * 0.7) {
                    message = 'Good attendance – Excellent participation';
                } else {
                    message = 'Good attendance – You need to participate more';
                }
            } else if (absences >= 3 && absences <= 4) {
                rowColor = '#e5d08aff';
                if (participation >= sessions * 0.5) {
                    message = 'Warning – attendance low – Good participation';
                } else {
                    message = 'Warning – attendance low – You need to participate more';
                }
            } else {
                rowColor = '#ff02136d';
                if (participation >= sessions * 0.5) {
                    message = 'Excluded – too many absences – Good participation';
                } else {
                    message = 'Excluded – too many absences – You need to participate more';
                }
            }

            row.style.backgroundColor = rowColor;
            if (cells[messageIndex]) {
                cells[messageIndex].textContent = message;
            }
        }

        allRows.forEach(r => updateRow(r));

        table.addEventListener('change', (e) => {
            if (e.target && e.target.type === 'checkbox') {
                const row = e.target.closest('tr');
                if (row) updateRow(row);
            }
        });

        // Store updateRow function globally for use in other functions
        window.updateRow = updateRow;
    }

    /* Exercise 2: Form Validation
    const studentForm = document.getElementById("studentForm");
    if (studentForm) {
        studentForm.addEventListener("submit", function(event) {
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

            // Exercise 3: Add student to table
            const tableBody = document.querySelector("#attendanceTable tbody");
            const newRow = document.createElement("tr");

            newRow.innerHTML = `
                <td>${lastName}</td>
                <td>${firstName}</td>
                ${Array(6).fill('<td><input type="checkbox"></td><td><input type="checkbox"></td>').join('')}
                <td></td>
                <td></td>
                <td class="message-col"></td>
            `;

            tableBody.appendChild(newRow);
            window.updateRow(newRow);

            // Show confirmation
            const confirmation = document.getElementById('confirmationMessage');
            confirmation.textContent = `Student "${firstName} ${lastName}" added successfully!`;
            confirmation.style.display = 'block';
            setTimeout(() => {
                confirmation.style.display = 'none';
            }, 3000);

            this.reset();
        });
    }*/
}); 

// jQuery features (Exercises 4-7)
$(document).ready(function() {
    
    // Chart function
    function displayAbsencesChart() {
        const sessions = 6;
        const firstSessionIndex = 2;
        const absencesPerSession = Array(sessions).fill(0);

        // Count absences for each session
        $('#attendanceTable tbody tr').each(function() {
            for (let s = 0; s < sessions; s++) {
                const pCell = $(this).find('td').eq(firstSessionIndex + s * 2);
                const checkbox = pCell.find('input[type="checkbox"]');
                if (checkbox.length && !checkbox.is(':checked')) {
                    absencesPerSession[s]++;
                }
            }
        });

        // Destroy existing chart if it exists
        if (window.absencesChart) {
            window.absencesChart.destroy();
        }

        // Create bar chart
        const ctx = document.getElementById('myChart').getContext('2d');
        window.absencesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Array.from({ length: sessions }, (_, i) => `Session ${i + 1}`),
                datasets: [{
                    label: 'Absences per Session',
                    data: absencesPerSession,
                    backgroundColor: ['#5421bcff','#ff56b3','#16a52eff','#b4db4fff','#8f4f16ff','#4285f4'],
                    borderColor: ['#5421bcff','#ff56b3','#16a52eff','#b4db4fff','#8f4f16ff','#4285f4'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Exercise 4: Show Report with Chart
    $('#showReport').on('click', function() {
        if ($('#reportSection').is(':visible')) {
            $('#reportSection').slideUp();
            return;
        }

        const totalStudents = $('#attendanceTable tbody tr').length;
        let presentCount = 0;
        let participatedCount = 0;

        $('#attendanceTable tbody tr').each(function() {
            const absCell = $(this).find('td').eq(14);
            const partCell = $(this).find('td').eq(15);
            const absences = parseInt(absCell.text()) || 0;
            const participation = parseInt(partCell.text()) || 0;

            if (absences < 6) presentCount++;
            if (participation > 0) participatedCount++;
        });

        const reportHTML = `
            <h3>Attendance Report</h3>
            <div class="report-item"><strong>Total Students:</strong> ${totalStudents}</div>
            <div class="report-item"><strong>Students with Good Attendance:</strong> ${presentCount}</div>
            <div class="report-item"><strong>Students with Participation:</strong> ${participatedCount}</div>
            <canvas id="myChart" style="width:100%;max-width:700px"></canvas>
        `;

        $('#reportSection').html(reportHTML).slideDown(function() {
            displayAbsencesChart();
        });
    });

    // Exercise 5: Hover highlight
    $('#attendanceTable tbody').on('mouseenter', 'tr', function() {
        $(this).addClass('row-highlight');
    }).on('mouseleave', 'tr', function() {
        $(this).removeClass('row-highlight');
    });

    // Exercise 5: Click to show student info
    $('#attendanceTable tbody').on('click', 'tr', function(event) {
        if (!$(event.target).is('input[type="checkbox"], label')) {
            const lastName = $(this).find('td').eq(0).text();
            const firstName = $(this).find('td').eq(1).text();
            const absences = $(this).find('td').eq(14).text();
            alert(`Student: ${firstName} ${lastName}\nAbsences: ${absences}`);
        }
    });

    // Exercise 6: Highlight Excellent Students
    $('#highlightExcellent').on('click', function() {
        $('#attendanceTable tbody tr').each(function() {
            const absCell = $(this).find('td').eq(14);
            const absences = parseInt(absCell.text()) || 0;
            if (absences < 3) {
                $(this).fadeOut(1000).fadeIn(1000).fadeOut(1000).fadeIn(1000);
                this.origColor = this.style.backgroundColor;
                this.style.backgroundColor = '#513dbdff';
            }
        });
    });

    // Exercise 6: Reset Colors
    $('#resetColors').on('click', function() {
        $('#attendanceTable tbody tr').each(function() {
            if (window.updateRow) {
                window.updateRow(this);
            }
        });
    });

    // Exercise 7: Search functionality
    $('#searchBox').on('keyup', function() {
        const searchValue = $(this).val().toLowerCase();

        $('#attendanceTable tbody tr').each(function() {
            const lastName = $(this).find('td').eq(0).text().toLowerCase();
            const firstName = $(this).find('td').eq(1).text().toLowerCase();
            
            if (lastName.indexOf(searchValue) > -1 || firstName.indexOf(searchValue) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Exercise 7: Sort by Absences
    $('#sortByAbsences').on('click', function() {
        const tbody = $('#attendanceTable tbody');
        const rows = tbody.find('tr').get();

        rows.sort(function(a, b) {
            const aAbs = parseInt($(a).find('td').eq(14).text()) || 0;
            const bAbs = parseInt($(b).find('td').eq(14).text()) || 0;
            return aAbs - bAbs;
        });

        $.each(rows, function(index, row) {
            tbody.append(row);
        });

        $('#sortMessage').text('Currently sorted by absences (ascending)').show();
    });

    // Exercise 7: Sort by Participation
    $('#sortByParticipation').on('click', function() {
        const tbody = $('#attendanceTable tbody');
        const rows = tbody.find('tr').get();

        rows.sort(function(a, b) {
            const aPar = parseInt($(a).find('td').eq(15).text()) || 0;
            const bPar = parseInt($(b).find('td').eq(15).text()) || 0;
            return bPar - aPar;
        });

        $.each(rows, function(index, row) {
            tbody.append(row);
        });

        $('#sortMessage').text('Currently sorted by participation (descending)').show();
    });
});