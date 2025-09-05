const footerMenu = document.querySelector(".footerMenu");
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
profileBtn.addEventListener("click", () => togglePanel("Profile", profileSection));
