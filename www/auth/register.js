/*
const username = document.querySelector("input#username");
const contraseña = document.querySelector("input#password");
const confirmContraseña = document.querySelector("input#confirmpassword");
const terminos = document.querySelector("input#checkBox");
const correo = document.querySelector("input#email");
const fechaNacimiento = document.querySelector("input#birthdate");
const register = document.querySelector("form");

register.addEventListener("submit", async function ($e) {
    $e.preventDefault();
    if (confirmContraseña.value == contraseña.value) {
        if (terminos.checked) {
            acceder = await fetch("/php/scripts/auth/register.php", {
                method: "POST",
                body: JSON.stringify({username: username.value, contraseña: contraseña.value, correo: correo.value, fechaNacimiento: fechaNacimiento.value})
            }).then(function (response) {
                return response.json();
            });

            if (acceder.state == "success") {
                alert("Cuenta registrada exitosamente!!!");
                window.location.href = "/";
            } else {
                alert(acceder.ErrMessage);
            }
        } else {
            alert("Debes aceptar los terminos y condiciones");
        }
    } else {
        alert("Las contraseñas deben coincidir");
    }
    
});
*/
const form = document.querySelector("form");
const errorContainer = document.getElementById("registerError");

form.addEventListener("submit", async (e) => {
    e.preventDefault();
    errorContainer.style.display = "none";
    errorContainer.textContent = "";

    const username = document.querySelector("#username").value.trim();
    const email = document.querySelector("#email").value.trim();
    const password = document.querySelector("#password").value;
    const confirmPassword = document.querySelector("#confirmpassword").value;
    const birthdate = document.querySelector("#birthdate").value;
    const tosChecked = document.querySelector("#checkBox").checked;

    // --- Validation ---
    if (!username || username.length < 3) {
        errorContainer.textContent = "Username must be at least 3 characters long.";
        errorContainer.style.display = "block";
        return;
    }

    if (!email || !/^\S+@\S+\.\S+$/.test(email)) {
        errorContainer.textContent = "Please enter a valid email address.";
        errorContainer.style.display = "block";
        return;
    }

    if (!password || password.length < 6) {
        errorContainer.textContent = "Password must be at least 6 characters long.";
        errorContainer.style.display = "block";
        return;
    }

    if (password !== confirmPassword) {
        errorContainer.textContent = "Passwords do not match.";
        errorContainer.style.display = "block";
        return;
    }

    if (!birthdate) {
        errorContainer.textContent = "Please select your birthdate.";
        errorContainer.style.display = "block";
        return;
    }

    if (!tosChecked) {
        errorContainer.textContent = "You must agree to the terms and conditions.";
        errorContainer.style.display = "block";
        return;
    }

    // --- Send data to PHP ---
    try {
        const response = await fetch("../php/scripts/auth/register.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                username,
                correo: email,
                contraseña: password,
                fechaNacimiento: birthdate
            })
        });

        const data = await response.json();

        if (data.state === "success") {
            alert("Registration successful!");
            window.location.href = "../login.html";
        } else {
            errorContainer.textContent = data.ErrMessage || "Registration failed.";
            errorContainer.style.display = "block";
        }
    } catch (err) {
        console.error(err);
        errorContainer.textContent = "An error occurred. Please try again.";
        errorContainer.style.display = "block";
    }
});
