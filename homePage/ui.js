const settingsbtn = document.getElementById("SETTINGSbtn");
const playbtn = document.getElementById("PLAYbtn");
const profilebtn = document.getElementById("PROFILEbtn");
const footer = document.querySelector("footer");

const footerbody = document.createElement("section");
footerbody.classList.add("footerbody");
const footerheader = document.createElement("header");
footerheader.classList.add("footerheader");
const footertitle = document.createElement("h2");
footertitle.classList.add("footertitle");

function createLine(){
    const line = document.createElement("hr");
    return line;
}

const state = {
    volume: 100,
    settingsOpen: false,
    profileOpen: false,
    playOpen: false,
    selectedGraphics: null,
    currentPanel: null
};


function createFooterStructure(title) {
    footerbody.innerHTML = "";
    footerbody.appendChild(footerheader);
    footerheader.appendChild(footertitle);
    footertitle.innerText = title;
    
    if (!footer.contains(footerbody)) {
        footer.appendChild(footerbody);
    }
}

function showPanel(panelName, contentBuilder) {
    if (state.currentPanel === panelName && footer.classList.contains("visible")) {
        closePanel();
        return;
    }
    
    state.currentPanel = panelName;
    footer.classList.add("visible");
    footer.classList.remove("hiding");
    createFooterStructure(panelName);
    
    if (contentBuilder) {
        contentBuilder();
    }
}

function closePanel() {
    footer.classList.remove("visible");
    footer.classList.add("hiding");
    footerbody.innerHTML = "";
    state.currentPanel = null;
    state.settingsOpen = false;
    state.playOpen = false;
    state.profileOpen = false;
}

function createRow() {
    const row = document.createElement("div");
    row.classList.add("row");
    return row;
}

function createVolumeControl() {
    const volumeRow = createRow();
    
    const volumeText = document.createElement("span");
    volumeText.innerText = "Volume:";
    
    const volRange = document.createElement("input");
    Object.assign(volRange, {
        type: "range",
        min: 0,
        max: 100,
        value: state.volume,
        step: 1
    });
    volRange.classList.add("volSlider");
    
    const volValue = document.createElement("span");
    volValue.innerText = state.volume;
    
    volRange.addEventListener("input", () => {
        state.volume = parseInt(volRange.value);
        volValue.innerText = state.volume;
    });
    
    volumeRow.append(volumeText, volRange, volValue);
    return volumeRow;
}

function createGraphicsDropdown() {
    const graphicsRow = createRow();
    
    const graphicsSpan = document.createElement("span");
    graphicsSpan.innerText = "Graphics:";
    
    const dropdown = document.createElement("div");
    dropdown.classList.add("dropdown");
    
    const button = document.createElement("button");
    button.classList.add("btn", "btn-secondary", "dropdown-toggle", "customDropdown");
    Object.assign(button, {
        type: "button",
        id: "graphicsDropdownButton"
    });
    button.setAttribute("data-bs-toggle", "dropdown");
    button.setAttribute("aria-expanded", "false");
    button.innerText = state.selectedGraphics || "Select";
    
    const menu = document.createElement("ul");
    menu.classList.add("dropdown-menu", "customdropdownmenu");
    menu.setAttribute("aria-labelledby", "graphicsDropdownButton");
    
    const options = ["Low", "Medium", "High"];
    
    options.forEach(optionText => {
        const li = document.createElement("li");
        const a = document.createElement("a");
        a.classList.add("dropdown-item");
        a.href = "#";
        a.innerText = optionText;
        
        a.addEventListener("click", e => {
            e.preventDefault();
            button.innerText = optionText;
            state.selectedGraphics = optionText;
        });
        
        li.appendChild(a);
        menu.appendChild(li);
    });
    const line = createLine();
    dropdown.append(button, menu);
    graphicsRow.append(graphicsSpan, dropdown);
    return graphicsRow;
}

function createtopHalf(){
    const topContainer = document.createElement("section");
    topContainer.classList.add("playSection");

    const codeInput = document.createElement("input");
    codeInput.type = "text";
    codeInput.placeholder = "Insert Game Code";

    const joinBtn = document.createElement("button");
    joinBtn.classList.add("circle-button");
    joinBtn.innerText = "Join";

    const orLabel = document.createElement("p");
    orLabel.innerText = "or";

    topContainer.append(codeInput, joinBtn, orLabel);
    return topContainer;
}

function createbottomHalf(){
    const bottomContainer = document.createElement("section");
    bottomContainer.classList.add("playSection");

    const hostBtn = document.createElement("button");
    hostBtn.classList.add("circle-button");
    hostBtn.innerText = "Host a game";

    bottomContainer.appendChild(hostBtn);
    return bottomContainer;
}

function buildSettingsContent() {
    const volumeControl = createVolumeControl();
    const graphicsDropdown = createGraphicsDropdown();
    
    const line = createLine();
    footerbody.append(volumeControl, line, graphicsDropdown);
    state.settingsOpen = true;
}

function buildPlayContent(){
    const top = createtopHalf();
    const bottom = createbottomHalf();
    const line = createLine();

    footerbody.append(top, line, bottom);
    state.playOpen = true;
}

function buildProfileContent() {
    const profileInfo = document.createElement("article");
    profileInfo.classList.add("profile-info");
    profileInfo.innerHTML = "<p>Profile information will go here, player stats, everything</p>";
    footerbody.appendChild(profileInfo);
    state.profileOpen = true;
}

settingsbtn.addEventListener("click", () => {
    showPanel("Settings", buildSettingsContent);
});

profilebtn.addEventListener("click", () => {
    showPanel("Profile", buildProfileContent);
});
playbtn.addEventListener("click", () => {
    showPanel("Join a Game", buildPlayContent);
});


document.addEventListener("click", (e) => {
    if (state.currentPanel && 
        !footer.contains(e.target) && 
        !settingsbtn.contains(e.target) && 
        !profilebtn.contains(e.target)) {
        closePanel();
    }
});