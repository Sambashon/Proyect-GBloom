let changesSaved = false;
let username;
let email;
let aboutme;
let birthdate;
let password;

const usernameInput = document.querySelector("input#username");
const emailInput = document.querySelector("input#email");
const aboutmeInput = document.querySelector("textarea#about");
const passwordInput = document.querySelector("input#password");
const birthdayInput = document.getElementById("birthdate");
const save = document.querySelector("form");
const leaveBtn = document.querySelector("#leaveBtn");
const unsavedModalEl = document.querySelector("#unsavedModal");
const unsavedModal = new bootstrap.Modal(unsavedModalEl);



leaveBtn.addEventListener("click", () => {
    if (username == usernameInput.value && email == emailInput.value && password == passwordInput.value && aboutme == aboutmeInput.value || changesSaved) {
        console.log("No unsaved changes, proceed with leaving.");//ariel le meti un "|| changesSaved a la condicional de arriba"
        window.location.href = '../homePage/home.html';  
    } else {
        unsavedModal.show();
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

function showError(input, message) {
    const errorEl = document.querySelector(".error-message");
    errorEl.textContent = message;
}

function clearError(input) {
    const errorEl = document.querySelector(".error-message");
    if (errorEl) errorEl.remove();
}

function validateProfileForm() {
    let valid = true;

    if (usernameInput.value.trim().length < 4 || usernameInput.value.trim().length > 32) {
        showError(usernameInput, "Username must be between 4 and 32 characters long.");
        valid = false;
    } else {
        clearError(usernameInput);
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(emailInput.value.trim())) {
        showError(emailInput, "Please enter a valid email address.");
        valid = false;
    } else {
        clearError(emailInput);
    }
    /* se mueve esto a otra parte
    if (passwordInput.value.length < 8 || passwordInput.value.length > 100) {
        showError(passwordInput, "Password must be between 8 and 100 characters.");
        valid = false;
    } else {
        clearError(passwordInput);
    }
    */
    if (aboutmeInput.value.trim().length > 200) {
        showError(aboutmeInput, "Description cannot exceed 200 characters.");
        valid = false;
    } else {
        clearError(aboutmeInput);
    }

    return valid;
}

save.addEventListener("submit", async (e) =>{
    e.preventDefault();
    if(!validateProfileForm()){
        const errEl = document.querySelector(".error-message");
        errEl.textContent = "No errors";
        return;
    }
    try{
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
        requestUserData();
        alert("Changes saved!");
        changesSaved = true;
        const data = await action.json();
        if (data.state === "success") {
            await requestUserData();
            alert("Changes saved!");
            changesSaved = true;
        } else {
            alert("Error saving changes: " + (data.message || "Unknown error"));
        }
    } catch (err) {
        alert("Network error. Please try again later.");
    }
});

async function requestUserData() {
    const response = await fetch("/php/scripts/utilities/credentials.php").then(function (response) {
        return response.json();
    });

    if (response.state == "success") {
        const usuario = response.result;
        username = usuario.username;
        email = usuario.correo;
        aboutme = usuario.descripcion;
        password = usuario.contraseña;
        birthdate = usuario.fechaNacimiento;

        usernameInput.value = username;
        emailInput.value = email;
        passwordInput.value = "";
        birthdayInput.value = birthdate;
        aboutmeInput.value = aboutme;

    }
}

document.addEventListener("DOMContentLoaded", requestUserData);