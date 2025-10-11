const username = document.querySelector("input#username");
const contraseña = document.querySelector("input#password");
let correo;
const mantener = document.querySelector("input#checkBox");
const login = document.querySelector("form");
const loginModalEl = document.querySelector("#loginModal");
const loginModal = new bootstrap.Modal(document.querySelector("#loginModal"));
loginModal._config.backdrop = 'static';
loginModal._config.keyboard = false;
loginModalEl.addEventListener("hidden.bs.modal", () => {
    loginModalEl.innerHTML = `<div class="modal-dialog modal-dialog-centered">
            <img src="/Resources/Icons/cargando.gif" width="100px" style="margin: auto;"></img>
        </div>`;
    
    loginModal._config.backdrop = 'static';
    loginModal._config.keyboard = false;
});

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
    }
});

document.querySelector("#recoveryLink").addEventListener("click", recuperarCuenta);

//VALIDATION
const loginForm = document.querySelector("form");
const usernameInput = document.getElementById("username");
const passwordInput = document.getElementById("password");
const stayLoggedIn = document.getElementById("checkBox");
const loginError = document.getElementById("loginError");
const errorContainer = document.querySelector("#loginError");

loginForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    loginError.style.display = "none";
    loginError.textContent = "";

    const username = usernameInput.value.trim();
    const password = passwordInput.value.trim();
    const mantener = stayLoggedIn.checked;

    // --- Validation ---
    if (!username || username.length < 3) {
        await setTimeout(() => {loginModal.hide()}, 1000);
        errorContainer.textContent = "Username must be at least 3 characters long.";
        errorContainer.style.display = "block";
        return;
    }

    if (!password || password.length > 8 && password.length < 100) {
        await setTimeout(() => {loginModal.hide()}, 1000);
        errorContainer.textContent = "Password must be at least 8 characters long.";
        errorContainer.style.display = "block";
        return;
    }
    
    if (!username || !password) {
        await setTimeout(() => {loginModal.hide()}, 1000);
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
        } else if (data.ErrDetails == "Active su cuenta antes de iniciar sesion") {
            loginModalEl.innerHTML = `<div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header centered-modal-header">
            <h1 class="modal-title responsive-title text-outline" id="activationModalLabel">Activa tu cuenta</h1>
          </div>

          <div class="modal-body">
            <section class="modal-group">
              <p class="responsive-text text-outline">
                Te hemos enviado un correo con un enlace para activar tu cuenta.
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

            sendActivationEmail();
            const resendActivationBtn = document.querySelector("button#resendActivationBtn");

            resendActivationBtn.addEventListener("click", sendActivationEmail);

            loginModal._config.backdrop = true;
            loginModal._config.keyboard = true;
        } else {
            await setTimeout(() => {loginModal.hide()}, 1000);
            loginError.textContent = data.ErrDetails || "Invalid username or password.";
            loginError.style.display = "block";
        }
    } catch (err) {
        await setTimeout(() => {loginModal.hide()}, 1000);
        console.error(err);
        loginError.textContent = "Server error. Please try again later.";
        loginError.style.display = "block";
    }
});

async function sendActivationEmail() {
    response = await fetch(`/php/scripts/auth/reenviar.php`, {
        method: "POST",
        body: JSON.stringify({username: username.value})
    });
    const data = await response.json();

    if (data.status === "success") {
    } else {
        alert(data.ErrMessage || "No fue posible reenviar el correo.", errorBox);
        console.log(data.origin);
    }
}

async function sendRecoveryEmail() {
    response = await fetch(`/php/scripts/auth/recuperarContraseña.php`, {
        method: "POST",
        body: JSON.stringify({correo: correo.value})
    });
    const data = await response.json();

    if (data.status === "success") {
    } else {
        alert(data.ErrMessage || "No fue posible reenviar el correo.", errorBox);
        console.log(data.origin);
    }
}

function recuperarCuenta() {
    loginModal._config.backdrop = true;
    loginModal._config.keyboard = true;

    loginModalEl.innerHTML = `<div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header centered-modal-header">
        <h1 class="modal-title responsive-title text-outline" id="recoveryModalLabel">Recuperar cuenta</h1>
      </div>
      <div class="modal-body">
        <section class="modal-group">
          <p class="responsive-text text-outline" style="text-align:center;">
            Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu cuenta.
          </p>
        </section>

        <section class="modal-group modal-column" style="gap:0.75rem;">
          <input type="email" id="recoveryEmail" class="custom-input responsive-text" placeholder="example@email.com">
        </section>

        <section class="modal-group" id="recoveryMessage" style="display:none;">
          <p class="responsive-subtitle text-outline"></p>
        </section>

        <section class="modal-group custom-row" style="gap:0.75rem; justify-content:center;">
          <button class="custom-button" id="sendRecoveryBtn">Enviar correo</button>
          <button class="custom-button" data-bs-dismiss="modal">Cerrar</button>
        </section>

        <section class="modal-footer" style="padding-top:8px;"></section>
      </div>
    </div>
  </div>`;

    correo = document.querySelector("#recoveryEmail");

    const resendRecoveryBtn = document.querySelector("button#sendRecoveryBtn");
    resendRecoveryBtn.addEventListener("click", sendRecoveryEmail);

    loginModal.show();
}