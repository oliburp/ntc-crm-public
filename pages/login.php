<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Inter&display=swap" rel="stylesheet" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <link rel="stylesheet" href="../css/main.css">
    <title>Login</title>
</head>

<body>
    <header>
        <h1>Nova Schola</h1>
        <div>Mabini Ave, Brgy. Sambat, Tanauan City<br>
            (043) 702-6867 inquiry@ntcbatangas.edu.ph</div>
    </header>
    <div id="loginBody">
        <img src="../images/login.png" alt="" id="loginBg">
        <div id="loginForm">
            <form action="../backend/login.php" method="POST">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" class="p-2 rounded-pill border-info" required>
                <br>
                <label for="password">Password:</label>
                <div>
                    <input type="password" id="password" name="password" class="p-2 rounded-pill border-info" required>
                    <div id="togglePassword" onclick="togglePassword()"><i class="fa-solid fa-eye-slash"></i></div>
                </div>
                <input type="submit" value="Login" class="button">
            </form>
        </div>
    </div>
</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
<script>
    document.querySelector("form").addEventListener("submit", function(event) {
        const username = document.getElementById("username").value;
        const password = document.getElementById("password").value;

        const usernamePattern = /^[A-Za-z0-9]{3,20}$/;
        if (!usernamePattern.test(username)) {
            event.preventDefault();
            swal("Invalid Username!", "Only 3-20 alphanumeric characters are allowed.", "error");
            return;
        }

        if (password.length < 3) {
            event.preventDefault();
            swal("Invalid Password!", "Password must be at least 8 characters long.", "error");
            return;
        }

        event.preventDefault(); // Prevent default form submission

        const formData = new FormData(event.target);
        fetch("../backend/login.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    window.location.href = data.redirect; // Redirect on success
                } else {
                    swal("Error!", data.message, "error");
                }
            })
            .catch(error => {
                swal("Error!", "Something went wrong. Please try again.", "error");
            });
    });

    function togglePassword() {
        var passwordInput = document.getElementById('password');
        var toggleButton = document.getElementById('togglePassword');

        if (passwordInput.type === 'password') {
            passwordInput.setAttribute('type', 'text');
            toggleButton.innerHTML = '<i class="fa-solid fa-eye"></i>';
        } else {
            passwordInput.setAttribute('type', 'password');
            toggleButton.innerHTML = '<i class="fa-solid fa-eye-slash"></i>';
        }
    }
</script>