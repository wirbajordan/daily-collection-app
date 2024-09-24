/* js for delete notification*/

    document.addEventListener('DOMContentLoaded', () => {
    const deleteButtons = document.querySelectorAll('button[name="delete_notification"]');

    deleteButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            const confirmDelete = confirm("Are you sure you want to delete this notification?");
            if (!confirmDelete) {
                event.preventDefault(); // Prevent form submission if not confirmed
            }
        });
    });
});

/*js for displaying contributor and collectors list*/
function showList(type) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `admin_get_users_list.php?type=${type}`, true);
    xhr.onload = function() {
        document.getElementById('listContainer').innerHTML = this.responseText;
        document.getElementById('closeButton').style.display = 'inline'; // Show the close button
    }
    xhr.send();
}

function closeList() {
    document.getElementById('listContainer').innerHTML = ''; // Clear the list
    document.getElementById('closeButton').style.display = ''; // Hide the close button
}


