const form = document.querySelector("form");
const errorContainer = document.getElementById("registerError");

form.addEventListener("submit", async (e) => {
    e.preventDefault();
    errorContainer.style.display = "none";
    errorContainer.textContent = "";

    const username = document.querySelector("#username").value.trim();
    const email = document.querySelector("#email").value.trim();
    const password = document.querySelector("#password").value;
    const tosChecked = document.querySelector("#checkBox").checked;

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

    if (!password || password.length < 8 && password.length > 100) {
        errorContainer.textContent = "Password must be at least 8 and 100 characters long.";
        errorContainer.style.display = "block";
        return;
    }

    if (!tosChecked) {
        errorContainer.textContent = "You must agree to the terms and conditions.";
        errorContainer.style.display = "block";
        return;
    }

    try {
        const response = await fetch("../php/scripts/auth/register.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                username,
                correo: email,
                contraseña: password,
            })
        });

        const data = await response.json();

        if (data.state === "success") {
            alert("Registration successful!");
            window.location.href = "/auth/login.html";
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