const username = document.querySelector("input#username");
const contraseña = document.querySelector("input#password");
const mantener = document.querySelector("input#checkBox");
const login = document.querySelector("form");

login.addEventListener("submit", async function ($e) {
    $e.preventDefault();
    acceder = await fetch("/php/scripts/auth/login.php", {
        method: "POST",
        body: JSON.stringify({username: username.value, contraseña: contraseña.value, mantener: mantener.checked})
    }).then(function (response) {
        return response.json();
    });

    if (acceder.status == "success") {
        alert("Sesion Iniciada como " + username.value);
        window.location.href = "/";
    } else {
        alert(acceder.ErrMessage);
    }
});

//VALIDATION
const loginForm = document.querySelector("form");
const usernameInput = document.getElementById("username");
const passwordInput = document.getElementById("password");
const stayLoggedIn = document.getElementById("checkBox");
const loginError = document.getElementById("loginError");

loginForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    loginError.style.display = "none";
    loginError.textContent = "";

    const username = usernameInput.value.trim();
    const password = passwordInput.value.trim();
    const mantener = stayLoggedIn.checked;

    // --- Validation ---
    if (!username || username.length < 3) {
        errorContainer.textContent = "Username must be at least 3 characters long.";
        errorContainer.style.display = "block";
        return;
    }

    if (!password || password.length > 8 && password.length < 100) {
        errorContainer.textContent = "Password must be at least 8 characters long.";
        errorContainer.style.display = "block";
        return;
    }
    
    if (!username || !password) {
        loginError.textContent = "Please enter both username and password.";
        loginError.style.display = "block";
        return;
    }

    try {
        const response = await fetch("/php/scripts/auth/login.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ username, contraseña: password, mantener })
        });

        const data = await response.json();

        if (data.status === "success") {
            window.location.href = "/homePage/home.html";
        } else {
            loginError.textContent = data.ErrDetails || "Invalid username or password.";
            loginError.style.display = "block";
        }
    } catch (err) {
        console.error(err);
        loginError.textContent = "Server error. Please try again later.";
        loginError.style.display = "block";
    }
});
