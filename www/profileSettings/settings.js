
let username;
let email;
let aboutme;
let birthdate;

const usernameInput = document.querySelector("input#username");
const emailInput = document.querySelector("input#email");
const aboutmeInput = document.querySelector("textarea#about");
const passwordInput = document.querySelector("input#password");
const birthdayInput = document.getElementById("birthdate");
const changePassBtn = document.querySelector("button#changePass");
const save = document.querySelector("form");
const deleteAccount = document.querySelector("button#deleteAccBtn");
const leaveBtn = document.querySelector("#leaveBtn");
const unsavedModalEl = document.querySelector("#unsavedModal");
const unsavedModal = new bootstrap.Modal(unsavedModalEl);
const deleteModal = new bootstrap.Modal(document.querySelector("#deleteModal"));

leaveBtn.addEventListener("click", () => {
    if (username == usernameInput.value && birthdate == birthdayInput.value && aboutme == aboutmeInput.value) {
        window.location.href = '../homePage/home.html';
    } else {
        unsavedModal.show();
    }
});

deleteAccount.addEventListener("click", async () => {
    const action = await fetch("/php/scripts/auth/delete.php", {method: "POST"});
    const data = await action.json();
    
    const deleteModalEl = document.querySelector("#deleteModal");

    deleteModalEl.innerHTML = `<div class="modal-dialog modal-dialog-centered">
            <img src="/Resources/Icons/cargando.gif" width="100px" style="margin: auto;"></img>
        </div>`;
    deleteModal._config.backdrop = 'static';
    deleteModal._config.keyboard = false;
    
    if (data.status == "success") {
        await setTimeout(() => {deleteModal.hide()}, 1000);
        window.location.href = '../homePage/home.html';
    } else {
        await setTimeout(() => {deleteModal.hide()}, 1000);
        deleteModal._config.backdrop = true;
        deleteModal._config.keyboard = true;
        deleteModalEl.innerHTML = `<div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header centered-modal-header">
                <h1 class="modal-title responsive-text text-outline">Are you sure you want to delete your account?</h1>
            </div>
            <div class="modal-footer">
                <button type="button" class="custom-button dark-red" id="deleteAccBtn">YES</button>
                <button type="button" class="custom-button" data-bs-dismiss="modal">NO</button>
            </div>    
            </div>
        </div>`;
        alert(data.ErrMessage);
    }
});

changePassBtn.addEventListener("click", async () => {
    const action = await fetch("/php/scripts/auth/recuperarContraseña.php", {
        method: "POST",
        body: JSON.stringify({correo: email})
    });
    const data = await action.json();
    
    if (data.status == "success") {
        window.location.href = '../homePage/home.html';
    } else {
        alert(data.ErrMessage);
    }
});

const form = document.querySelector("form");
const inputs = form.querySelectorAll("input, textarea");
    inputs.forEach(input => {
    const parent = input.parentElement;
    const editBtn = parent.querySelector("button");
    input.addEventListener("keydown", (e) =>{
        if(e.key === "Enter"){
            e.preventDefault();
        }
    })
    if(!editBtn) return;
    editBtn.addEventListener("click", (e) =>{
        e.preventDefault();
        input.focus();
    })
});

const modalleaveBtn = document.querySelector("#modalleaveBtn");
modalleaveBtn.addEventListener("click", () =>{
    window.location.href = '../homePage/home.html';
})
const logoutBtn = document.querySelector("#logoutBtn");
logoutBtn.addEventListener("click", () =>{
    const action = fetch("/php/scripts/auth/logout.php");
    window.location.href = '../homePage/home.html';
})

function showError(message) {
    const errorEl = document.querySelector(".error-message");
    errorEl.textContent = message;
}

function clearError(input) {
    const errorEl = document.querySelector(".error-message");
    errorEl.textContent = "";
}

function validateProfileForm() {
    let valid = true;

    if (usernameInput.value.trim().length < 4 || usernameInput.value.trim().length > 32) {
        showError("Username must be between 4 and 32 characters long.");
        valid = false;
    } else if (valid) {
        clearError(usernameInput);
    }
    
    if (aboutmeInput.value.trim().length > 200) {
        showError("Description cannot exceed 200 characters.");
        valid = false;
    } else if (valid) {
        clearError(aboutmeInput);
    }

    return valid;
}

save.addEventListener("submit", async (e) =>{
    e.preventDefault();
    if(!validateProfileForm()){
        const errEl = document.querySelector(".error-message");
        return;
    }
    try{
        /*
        if (passwordInput.value != "") {
            const action = await fetch("/php/scripts/auth/modify.php", {
            method: "POST",
            body: JSON.stringify({
                    username: usernameInput.value,
                    correo: emailInput.value,
                    contraseña: passwordInput.value,
                    fechaNacimiento: birthdayInput.value,
                    descripcion: aboutmeInput.value
                })
            });
        } else {*/
        const action = await fetch("/php/scripts/auth/modify.php", {
        method: "POST",
        body: JSON.stringify({
                username: usernameInput.value,
                fechaNacimiento: birthdayInput.value,
                descripcion: aboutmeInput.value
            })
        });
        //}

        const data = await action.json();
        if (data.status === "success") {
            await requestUserData();
            alert("Changes saved!");
        } else {
            alert("Error saving changes: " + (data.ErrDetails || "Unknown error"));
        }
    } catch (err) {
        alert("Network error. Please try again later.");
    }
});

async function requestUserData() {
    const response = await fetch("/php/scripts/utilities/credentials.php").then(function (response) {
        return response.json();
    });

    if (response.status == "success") {
        const usuario = response.result;
        username = usuario.username;
        email = usuario.correo;
        aboutme = usuario.descripcion;
        birthdate = usuario.fechaNacimiento;

        usernameInput.value = username;
        //emailInput.value = email;
        birthdayInput.value = birthdate;
        aboutmeInput.value = aboutme;
    }
}

document.addEventListener("DOMContentLoaded", requestUserData);