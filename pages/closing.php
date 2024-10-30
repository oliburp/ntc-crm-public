</div>
</body>
<script>
    function logout() {
        // Use SweetAlert to ask for logout confirmation
        Swal.fire({
            title: 'Are you sure you want to logout?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, logout',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Make an AJAX request to the logout endpoint
                $.ajax({
                    url: '../backend/logout.php', // Adjust the path to your logout script
                    method: 'POST',
                    success: function(response) {
                        // Redirect to login page on successful logout
                        window.location.href = '../pages/login.php';
                    },
                    error: function(xhr, status, error) {
                        // Show an error message if logout fails
                        Swal.fire({
                            icon: 'error',
                            title: 'Logout Failed',
                            text: 'An error occurred while logging out. Please try again.'
                        });
                    }
                });
            }
        });
    }
</script>