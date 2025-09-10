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