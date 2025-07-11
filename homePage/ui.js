
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


const state = {
    volume: 100,
    settingsOpen: false,
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
    createFooterStructure(panelName);
    
    if (contentBuilder) {
        contentBuilder();
    }
}

function closePanel() {
    footer.classList.remove("visible");
    footerbody.innerHTML = "";
    state.currentPanel = null;
    state.settingsOpen = false;
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
    
    dropdown.append(button, menu);
    graphicsRow.append(graphicsSpan, dropdown);
    return graphicsRow;
}

function buildSettingsContent() {
    const volumeControl = createVolumeControl();
    const graphicsDropdown = createGraphicsDropdown();
    
    footerbody.append(volumeControl, graphicsDropdown);
    state.settingsOpen = true;
}

function buildProfileContent() {
    const profileInfo = document.createElement("div");
    profileInfo.classList.add("profile-info");
    profileInfo.innerHTML = "<p>Profile information will go here</p>";
    footerbody.appendChild(profileInfo);
}

settingsbtn.addEventListener("click", () => {
    showPanel("Settings", buildSettingsContent);
});

profilebtn.addEventListener("click", () => {
    showPanel("Profile", buildProfileContent);
});


document.addEventListener("click", (e) => {
    if (state.currentPanel && 
        !footer.contains(e.target) && 
        !settingsbtn.contains(e.target) && 
        !profilebtn.contains(e.target)) {
        closePanel();
    }
});