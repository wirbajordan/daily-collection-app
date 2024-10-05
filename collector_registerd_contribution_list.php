<body>
    <h1>Register Contribution</h1>
    <button id="contributionBtn">View Registered Contributions</button>

    <div id="contributionModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Your Registered Contributions</h2>
            <table id="contributionTable">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Registered Contribution</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Contribution data will be populated here -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('contributionBtn').onclick = function() {
            const modal = document.getElementById('contributionModal');
            modal.style.display = 'block';

            // Fetch registered contributions for the logged-in collector
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({ fetch_contributions: true })     
            })
            .then(response => response.json())
            .then(data => {
                const tableBody = document.querySelector('#contributionTable tbody');
                tableBody.innerHTML = ''; // Clear existing rows

                data.forEach(contribution => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${contribution.user_id}</td>
                        <td>${contribution.username}</td>
                        <td>${contribution.amount}</td>
                        <td>${contribution.date}</td>
                        <td>${contribution.register_contribution}</td>
                    `;
                    tableBody.appendChild(row);
                });
            })
            .catch(error => console.error('Error fetching contributions:', error));
        }

        // Close modal functionality
        document.querySelector('.close').onclick = function() {
            document.getElementById('contributionModal').style.display = 'none';
        }
        
        // Close modal when clicking outside of modal content
        window.onclick = function(event) {
            const modal = document.getElementById('contributionModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>