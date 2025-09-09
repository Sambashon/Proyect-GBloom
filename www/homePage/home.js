//-----------MAINPAGE
const footerMenu = document.querySelector(".footer-menu");
const settingsBtn = document.getElementById("SETTINGSbtn");
const playBtn = document.getElementById("PLAYbtn");
const profileBtn = document.getElementById("PROFILEbtn");

const settingsSection = document.getElementById("SETTINGS");
const playSection = document.getElementById("PLAY");
const profileSection = document.getElementById("PROFILE");

const state = {
    currentPanel: null,
    volume: 100,
    selectedGraphics: null
}

const volumeSlider = document.getElementById("volSlider");
const volValue = document.getElementById("volValue");
volumeSlider.addEventListener("input", (e) =>{
    const volume = e.target.value;
    volValue.textContent = volume;
})


function closePanels(){
    [settingsSection, playSection, profileSection].forEach(section => {
        section.classList.add("hidden");
    });
    state.currentPanel = null;
}
function togglePanel(panelName, panelElement) {
    console.log("panelToggled");
    footerMenu.classList.add("visible");

    if (state.currentPanel === panelName) {
        console.log(panelName);
        footerMenu.classList.remove("visible");
        closePanels();
        return;
    }
    closePanels();
    panelElement.classList.remove("hidden");
    state.currentPanel = panelName;
}
settingsBtn.addEventListener("click", () => togglePanel("Settings", settingsSection));
playBtn.addEventListener("click", () => togglePanel("Play", playSection));
//IF USER LOGGED THEN
//profileBtn.addEventListener("click", () => togglePanel("Profile", profileSection));


const profileSettingsBtn = document.getElementById("profileSettingsBtn");
profileSettingsBtn.addEventListener("click", () =>{
    window.location.href = '../profileSettings/settings.html';
})
//-------SETTINGS SECTION
//dropdowns
document.querySelectorAll(".dropdown-item").forEach(item => {
  item.addEventListener("click", () => {
    const dropdown = item.closest(".dropdown");
    const button = dropdown.querySelector(".dropdown-toggle");
    button.innerText = item.innerText;
    console.log("Selected:", item.innerText);
  });
});

//----------------PLAY SECTION
const joinBtn = document.getElementById("joinBtn");
const hostBtn = document.getElementById("hostBtn");

joinBtn.addEventListener("click", () =>{
    window.location.href = '../lobby/guestLobby/lobby.html';
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