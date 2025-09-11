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

const save = document.querySelector("form");
const leaveBtn = document.querySelector("#leaveBtn");
const unsavedModalEl = document.querySelector("#unsavedModal");
const unsavedModal = new bootstrap.Modal(unsavedModalEl);

save.addEventListener("submit", (e) =>{
    e.preventDefault();
    const action = fetch("/php/scripts/auth/modify.php", {
        method: "POST",
        body: JSON.stringify({username: usernameInput.value, correo: emailInput.value, contraseña: passwordInput.value, descripcion: aboutmeInput.value})
    });
    requestUserData();
    alert("Changes saved!");
    changesSaved = true;
})
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
        birthdate = new Date(usuario.fechaNacimiento);

        usernameInput.value = username;
        emailInput.value = email;
        passwordInput.value = password;
        aboutmeInput.value = aboutme;

    }
}

document.addEventListener("DOMContentLoaded", requestUserData);