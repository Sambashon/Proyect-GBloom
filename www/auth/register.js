const form = document.querySelector("form");
const errorContainer = document.getElementById("registerError");
const modalContent = document.querySelector("#exampleModal");
let resendActivationBtn;;

let username;
let email;
let password;
let tosChecked;

form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const modalInstance = bootstrap.Modal.getInstance(modalContent);
    
    if (modalInstance) {
        modalInstance._config.backdrop = 'static';
        modalInstance._config.keyboard = false;
    }   

    errorContainer.style.display = "none";
    errorContainer.textContent = "";

    username = document.querySelector("#username").value.trim();
    email = document.querySelector("#email").value.trim();
    password = document.querySelector("#password").value;
    tosChecked = document.querySelector("#checkBox").checked;

    if (!username || username.length < 3) {
        await setTimeout(() => {modalInstance.hide()}, 1000);
        errorContainer.textContent = "Username must be at least 3 characters long.";
        errorContainer.style.display = "block";
        return;
    }

    if (!email || !/^\S+@\S+\.\S+$/.test(email)) {
        await setTimeout(() => {modalInstance.hide()}, 1000);
        errorContainer.textContent = "Please enter a valid email address.";
        errorContainer.style.display = "block";
        return;
    }

    if (!password || password.length < 8 && password.length > 100) {
        await setTimeout(() => {modalInstance.hide()}, 1000);
        errorContainer.textContent = "Password must be at least 8 and 100 characters long.";
        errorContainer.style.display = "block";
        return;
    }

    if (!tosChecked) {
        await setTimeout(() => {modalInstance.hide()}, 1000);
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

        if (data.status === "success") {
            modalContent.innerHTML = `<div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header centered-modal-header">
            <h1 class="modal-title responsive-title text-outline" id="activationModalLabel">Activa tu cuenta</h1>
          </div>

          <div class="modal-body">
            <section class="modal-group">
              <p class="responsive-text text-outline">
                ¡Gracias por registrarte! Te hemos enviado un correo con un enlace para activar tu cuenta.
                Por favor revisa tu bandeja de entrada o la carpeta de spam.
              </p>
              <p id="activationEmail" class="responsive-subtitle text-outline activation-email" style="display:none;"></p>
            </section>

            <section class="modal-group">
              <div id="resendMessage" class="responsive-subtitle text-outline" style="display:none;"></div>
            </section>

            <section class="modal-group custom-row" style="gap:0.75rem; justify-content:center;">
              <button class="custom-button" id="resendActivationBtn">Reenviar correo</button>
              <button class="custom-button" data-bs-dismiss="modal">Cerrar</button>
            </section>

            <section class="modal-footer" style="padding-top: 8px;"></section>
          </div>
          </div>
        </div>`;

            resendActivationBtn = document.querySelector("button#resendActivationBtn");

            resendActivationBtn.addEventListener("click", async () => {
                response = await fetch(`/php/scripts/auth/reenviar.php`, {
                    method: "POST",
                    body: JSON.stringify({username: username})
                });
                const data = await response.json();

                if (data.status === "success") {
                } else {
                    alert(data.ErrMessage || "No fue posible reenviar el correo.", errorBox);
                    console.log(data.origin);
                }
            });
                    if (modalInstance) {
                modalInstance._config.backdrop = true;
                modalInstance._config.keyboard = true;
            }
        
        } else {
            // Mejorar el output de errDetails
            await setTimeout(() => {modalInstance.hide()}, 1000);
            if (data.status === 404) {
                errorContainer.textContent = data.ErrMessage || "Registration failed.";
            } else {
                alert(data.ErrMessage);
                errorContainer.textContent = data.ErrDetails || "Registration failed.";
            }
            errorContainer.style.display = "block";
        }

    } catch (err) {
        console.error(err);
        await setTimeout(() => {modalInstance.hide()}, 1000);
        errorContainer.textContent = "An error occurred. Please try again.";
        errorContainer.style.display = "block";
    }
});