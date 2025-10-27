//-----------MAINPAGE
let username;
let email;
let birthdate;
let aboutme;
let language;
let gameres;
let wins;
let played;

language = localStorage.getItem("language") || "ENGLISH";
gameres = localStorage.getItem("gameres") || "MEDIUM";

const languageDropdown = document.querySelectorAll(".dropdown-item#language");
const gameresDropdown = document.querySelectorAll(".dropdown-item#gameres");

const footerMenu = document.querySelector(".footer-menu");
const settingsBtn = document.getElementById("SETTINGSbtn");
const playBtn = document.getElementById("PLAYbtn");
const profileBtn = document.getElementById("PROFILEbtn");

const settingsSection = document.getElementById("SETTINGS");
const playSection = document.getElementById("PLAY");
const profileSection = document.getElementById("PROFILE");

const state = {
    currentPanel: null
}

const sfxSlider = document.getElementById("sfxSlider");
const sfxValue = document.getElementById("sfxValue");
sfxSlider.value = localStorage.getItem("sfx") || "100";
sfxValue.textContent = localStorage.getItem("sfx") || "100";
sfxSlider.addEventListener("input", (e) =>{
    const volume = e.target.value;
    sfxValue.textContent = volume;
    localStorage.setItem("sfx", volume);
})

const mscSlider = document.getElementById("mscSlider");
const mscValue = document.getElementById("mscValue");
mscSlider.value = localStorage.getItem("music") || "100";
mscValue.textContent = localStorage.getItem("music") || "100";
mscSlider.addEventListener("input", (e) =>{
    const volume = e.target.value;
    mscValue.textContent = volume;
    localStorage.setItem("music", volume);
})

function closePanels(){
    [settingsSection, playSection, profileSection].forEach(section => {
        section.classList.add("hidden");
    });
    state.currentPanel = null;
}
function togglePanel(panelName, panelElement) {
    footerMenu.classList.add("visible");

    if (state.currentPanel === panelName) {
        footerMenu.classList.remove("visible");
        closePanels();
        return;
    }
    closePanels();
    panelElement.classList.remove("hidden");
    state.currentPanel = panelName;
}

//--------PROFILE SECTION
const profileSettingsBtn = document.getElementById("profileSettingsBtn");
profileSettingsBtn.addEventListener("click", () =>{
    window.location.href = '../profileSettings/settings.html';
})
//-------SETTINGS SECTION
//dropdowns
languageDropdown.forEach(item => {
    const dropdown = item.closest(".dropdown");
    const button = dropdown.querySelector(".dropdown-toggle");
    button.innerText = language;

    item.addEventListener("click", () => {
        button.innerText = item.innerText;
        language = item.innerText;
        localStorage.setItem("language", language);
    });
});

gameresDropdown.forEach(item => {
    const dropdown = item.closest(".dropdown");
    const button = dropdown.querySelector(".dropdown-toggle");
    button.innerText = gameres;

    item.addEventListener("click", () => {
        button.innerText = item.innerText;
        gameres = item.innerText;
        localStorage.setItem("gameres", gameres);
    });
});

//----------------PLAY SECTION
const joinBtn = document.getElementById("joinBtn");
const hostBtn = document.getElementById("hostBtn");

joinBtn.addEventListener("click", () =>{
    connectLobby(document.querySelector("input#gamecode").value);
})
hostBtn.addEventListener("click" ,() =>{
    window.location.href = '../lobby/hostLobby/config.html';
})
//-------------MODAL
const loginBtn = document.getElementById("loginBtn");
const registerBtn = document.getElementById("registerBtn");

loginBtn.addEventListener("click", () =>{
    window.location.href = '../auth/login.html';
})
registerBtn.addEventListener("click", () =>{
    window.location.href = '../auth/register.html'
})
//-----------SWIPE FUNCTIONALITY
let touchStartY = 0;
let touchEndY = 0;
let touchStartX = 0;
let touchEndX = 0;

document.addEventListener("touchstart", (e) => {
    touchStartY = e.changedTouches[0].screenY;
    touchStartX = e.changedTouches[0].screenX;
});

document.addEventListener("touchend", (e) => {
    touchEndY = e.changedTouches[0].screenY;
    touchEndX = e.changedTouches[0].screenX;

    handleSwipe();
});

function handleSwipe() {
    const deltaY = touchEndY - touchStartY;
    const deltaX = touchEndX - touchStartX;

    if (footerMenu.classList.contains("visible") && Math.abs(deltaY) > Math.abs(deltaX) && deltaY > 50) {
        console.log("Swipe down detected!");
        footerMenu.classList.remove("visible");
        closePanels();
    }
}

async function connectLobby(code) {
    const solicitud = await fetch("/php/scripts/game/lobby/verificarCodigo.php", {
        method: "POST",
        body: JSON.stringify({codigo: code})
    }).then(function (response) {
        return response.json();
    });

    if (solicitud.status == "success") {
        window.location.href = "/lobby/guestLobby/lobby.html";
    } else {
        alert(solicitud.ErrMessage);
    }
    
}

async function requestUserData() {
    const response = await fetch("/php/scripts/utilities/credentials.php").then(function (response) {
        return response.json();
    });

    settingsBtn.addEventListener("click", () => togglePanel("Settings", settingsSection));

    if (response.status == "success") {
        profileBtn.removeAttribute('data-bs-toggle');
        profileBtn.removeAttribute('data-bs-target');

        playBtn.removeAttribute('data-bs-toggle');
        playBtn.removeAttribute('data-bs-target');

        const usuario = response.result;
        username = usuario.username;
        email = usuario.correo;
        aboutme = usuario.descripcion;
        birthdate = new Date(usuario.fechaNacimiento);
        wins = usuario.victorias;
        played = usuario.jugadas;

        const today = new Date();

        document.querySelector("span#playername").textContent = username;
        document.querySelector("#age").textContent = today.getFullYear() - birthdate.getFullYear();
        document.querySelector("h2#aboutme").textContent = aboutme;

        document.querySelector("#wins").textContent = "Wins: " + wins;
        document.querySelector("#played").textContent = "Played: " + played;

        profileBtn.addEventListener("click", () => togglePanel("Profile", profileSection));
        playBtn.addEventListener("click", () => togglePanel("Play", playSection));
    }
}

document.addEventListener("DOMContentLoaded", requestUserData);