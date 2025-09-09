const username = document.querySelector("input#username");
const contraseña = document.querySelector("input#password");
const confirmContraseña = document.querySelector("input#conPassword");
const terminos = document.querySelector("input#checkBox");
const correo = document.querySelector("input#email");
const fechaNacimiento = document.querySelector("input#fechaNacimiento");
const register = document.querySelector("form");

register.addEventListener("submit", async function ($e) {
    $e.preventDefault();
    if (confirmContraseña.value == contraseña.value && terminos.value) {
        acceder = await fetch("/php/scripts/auth/register.php", {
            method: "POST",
            body: JSON.stringify({username: username.value, contraseña: contraseña.value, correo: correo.value, fechaNacimiento: fechaNacimiento.value})
        }).then(function (response) {
            return response.json();
        });

        if (acceder.state == "success") {
            alert("Sesion Iniciada");
        } else {
            alert("Sesion no iniciada");
            console.log(acceder);
        }
    } else {
        alert("Las contraseñas deben coincidir y debes aceptar los terminos y condiciones");
    }
    
});