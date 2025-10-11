const contentArea = document.getElementById("contentArea");
const params = new URLSearchParams(window.location.search);
const code = params.get("codigo");
const waitModal = new bootstrap.Modal(document.querySelector("#waitModal"));
waitModal._config.backdrop = 'static';
waitModal._config.keyboard = false;

// Botón de cerrar
document.querySelector("#xOut").addEventListener("click", () => {
    window.location.href = "/";
});

async function verificarCodigo() {
    if (!code) {
        mostrarError("Enlace inválido o incompleto.");
        return;
    }

    try {
        const response = await fetch(`/php/scripts/auth/verificarCodigoAcceso.php?codigo=${encodeURIComponent(code)}`);
        const data = await response.json();

        if (data.status === "success") {
            mostrarFormulario();
        } else {
            mostrarError(data.ErrMessage || "Este enlace ya no es válido o ha expirado.");
        }
    } catch (err) {
        console.error(err);
        mostrarError("Error de conexión con el servidor. Inténtelo más tarde.");
    }
}

function mostrarFormulario() {
    contentArea.innerHTML = `
        <h1 class="text-outline responsive-title text-center">Nueva Contraseña</h1>
        <div id="resetError" class="text-outline" style="color: #f1c232; margin-bottom: 0.5rem; display: none;"></div>
        <form id="resetForm">
            <div class="form-group">
                <label for="newPassword" class="responsive-subtitle text-outline">Nueva contraseña</label>
                <div class="custom-row">
                    <input type="password" id="newPassword" class="custom-input responsive-text passwordInputs" required minlength="8">
                </div>
            </div>
            <div class="form-group">
                <label for="confirmPassword" class="responsive-subtitle text-outline">Confirmar contraseña</label>
                <div class="custom-row">
                    <input type="password" id="confirmPassword" class="custom-input responsive-text passwordInputs" required minlength="8">
                </div>
            </div>
            <div class="form-footer">
                <input type="submit" value="Cambiar contraseña" class="custom-button right" data-bs-toggle="modal" data-bs-target="#waitModal">
            </div>
        </form>
    `;

    // Activa los ojitos
    if (typeof initializePasswordEyes === "function") {
        initializePasswordEyes();
    }

    const form = document.getElementById("resetForm");
    const errorBox = document.getElementById("resetError");

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        errorBox.style.display = "none";

        const pass1 = document.getElementById("newPassword").value.trim();
        const pass2 = document.getElementById("confirmPassword").value.trim();

        if (pass1.length < 8) {
            await setTimeout(() => {waitModal.hide()}, 1000);
            mostrarErrorForm("La contraseña debe tener al menos 8 caracteres.", errorBox);
            return;
        }
        if (pass1 !== pass2) {
            await setTimeout(() => {waitModal.hide()}, 1000);
            mostrarErrorForm("Las contraseñas no coinciden.", errorBox);
            return;
        }

        try {
            const response = await fetch(`/php/scripts/auth/cambiarContraseña.php?codigo=${encodeURIComponent(code)}`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({contraseña: pass1 })
            });
            const data = await response.json();

            if (data.status === "success") {
                await setTimeout(() => {waitModal.hide()}, 1000);
                contentArea.innerHTML = `
                    <h1 class="text-outline responsive-title text-center">Contraseña actualizada</h1>
                    <p class="responsive-text text-outline">Tu contraseña ha sido cambiada correctamente.</p>
                    <button class="custom-button mt-3" onclick="window.location.href='/'">Ir al inicio</button>
                `;
            } else {
                await setTimeout(() => {waitModal.hide()}, 1000);
                mostrarErrorForm(data.ErrMessage || "No fue posible cambiar la contraseña.", errorBox);
                console.log(data.origin);
            }
        } catch (err) {
            await setTimeout(() => {waitModal.hide()}, 1000);
            console.error(err);
            mostrarErrorForm("Error del servidor. Inténtelo más tarde.", errorBox);
        }
    });
}

function mostrarErrorForm(msg, box) {
    box.textContent = msg;
    box.style.display = "block";
}

function mostrarError(msg) {
    contentArea.innerHTML = `
        <h1 class="text-outline responsive-title text-center">No fue posible continuar</h1>
        <p class="responsive-text text-outline">${msg}</p>
        <button class="custom-button mt-3" onclick="window.location.href='/'">Volver al inicio</button>
    `;
}

// Iniciar verificación
verificarCodigo();
