fetch("fetch_data.php")
  .then(res => res.json())
  .then(d => {
    
    // Update the numbers
    document.getElementById('students').innerText = d.students;
    document.getElementById('subjects').innerText = d.subjects;
    document.getElementById('passRate').innerText = d.pass_rate + "%";

    // Bar Chart
    new Chart(document.getElementById('barChart'), {
      type: "bar",
      data: {
        labels: ["Students", "Subjects"],
        datasets: [{
          label: "Count",
          data: [d.students, d.subjects],
          backgroundColor: [
            'rgba(106, 17, 203, 0.7)',
            'rgba(37, 117, 252, 0.7)'
          ],
          borderColor: [
            'rgba(106, 17, 203, 1)',
            'rgba(37, 117, 252, 1)'
          ],
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

    // Pie Chart
    new Chart(document.getElementById('pieChart'), {
      type: "pie",
      data: {
        labels: ["Pass", "Fail"],
        datasets: [{
          data: [d.pass_rate, 100 - d.pass_rate],
          backgroundColor: [
            'rgba(40, 167, 69, 0.7)',
            'rgba(220, 53, 69, 0.7)'
          ],
          borderColor: [
            'rgba(40, 167, 69, 1)',
            'rgba(220, 53, 69, 1)'
          ],
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true
      }
    });

  })
  .catch(error => {
    console.error('Error fetching data:', error);
    document.getElementById('students').innerText = '0';
    document.getElementById('subjects').innerText = '0';
    document.getElementById('passRate').innerText = '0%';
  });