
// Minimal, robust attendance counter
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
});