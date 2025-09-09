const username = document.querySelector("input#username");
const contraseña = document.querySelector("input#password");
const mantener = document.querySelector("input#checkBox");
const login = document.querySelector("form");

login.addEventListener("submit", async function ($e) {
    $e.preventDefault();
    acceder = await fetch("/php/scripts/auth/login.php", {
        method: "POST",
        body: JSON.stringify({username: username.value, contraseña: contraseña.value, mantener: mantener.value})
    }).then(function (response) {
        return response.json();
    });

    if (acceder.state == "success") {
        alert("Sesion Iniciada");
    } else {
        alert("Sesion no iniciada");
        console.log(acceder);
    }
});