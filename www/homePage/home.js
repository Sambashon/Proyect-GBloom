const register = document.getElementById("register");
register.addEventListener("click", () =>{
    window.location = "../loginStuff/confirmation/confirmation.html";
})




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
    selectedGraphics: null,
    currentPanel: null
};

// Utility functions
function createLine() {
    return document.createElement("hr");
}

function createRow() {
    const row = document.createElement("div");
    row.classList.add("row");
    return row;
}

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
}

// Settings content builders
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

// Play content builders
function createGameSection(isTop = true) {
    const container = document.createElement("section");
    container.classList.add("playSection");

    if (isTop) {
        const codeInput = document.createElement("input");
        codeInput.type = "text";
        codeInput.placeholder = "Insert Game Code";

        const joinBtn = document.createElement("button");
        joinBtn.classList.add("circle-button");
        joinBtn.innerText = "join";
        
        joinBtn.addEventListener("click", () =>{
            window.location.href = "../lobby/guestLobby/lobby.html"
        });

        const orLabel = document.createElement("p");
        orLabel.innerText = "or";

        container.append(codeInput, joinBtn, orLabel);
    } else {
        const hostBtn = document.createElement("button");
        hostBtn.classList.add("circle-button");
        hostBtn.innerText = "Host a game";
        hostBtn.addEventListener("click", () =>{
            window.location.href = "../lobby/hostLobby/config.html";
        })
        container.appendChild(hostBtn);

        
    }
    
    return container;
}

// Profile content builders
function createAvatarRow() {
    const avatarRow = createRow();
    const profilePicture = document.createElement("img");
    profilePicture.classList.add("profilepic");
    profilePicture.src = "../Resources/Icons/defautlprofilepic.png";
    
    const playerName = document.createElement("span");
    playerName.innerText = "PlayerName";

    const profileSettingsbtn = document.createElement("button");
    profileSettingsbtn.classList.add("circle-button")
    const image = document.createElement("img");
    image.src = "../Resources/Icons/settings-svgrepo-com.svg";

    profileSettingsbtn.appendChild(image);
    profileSettingsbtn.setAttribute("id", "profileSettingsBtn")

    profileSettingsbtn.addEventListener("click", () =>{
        alert("Under construction...");
    })
    avatarRow.append(profilePicture, playerName, profileSettingsbtn);
    return avatarRow;
}

function createTabbedContent() {
    const article = document.createElement("article");
    
    // Nav tabs
    const nav = document.createElement("nav");
    const ul = document.createElement("ul");
    ul.classList.add("nav", "nav-tabs");
    ul.setAttribute("role", "tablist");

    const tabs = [
        { id: "general", label: "General", active: true, content: "General Stuff. Under construction" },
        { id: "pD", label: "Personal Data", active: false, content: "Personal Data stuff. Under construction" },
        { id: "stats", label: "Stats", active: false, content: "Under construction" }
    ];

    tabs.forEach(tab => {
        const li = document.createElement("li");
        li.classList.add("nav-item");
        li.setAttribute("role", "presentation");

        const button = document.createElement("button");
        button.classList.add("nav-link");
        if (tab.active) button.classList.add("active");
        button.id = `${tab.id}-tab`;
        Object.assign(button, {
            type: "button",
            role: "tab"
        });
        button.setAttribute("data-bs-toggle", "tab");
        button.setAttribute("data-bs-target", `#${tab.id}-tab-pane`);
        button.setAttribute("aria-controls", `${tab.id}-tab-pane`);
        button.setAttribute("aria-selected", tab.active.toString());
        button.innerText = tab.label;

        li.appendChild(button);
        ul.appendChild(li);
    });

    nav.appendChild(ul);

    // Tab content
    const tabContent = document.createElement("section");
    tabContent.classList.add("tab-content");
    tabContent.id = "myTabContent";

    tabs.forEach(tab => {
        const div = document.createElement("div");
        div.classList.add("tab-pane", "fade");
        if (tab.active) div.classList.add("show", "active");
        div.id = `${tab.id}-tab-pane`;
        div.setAttribute("role", "tabpanel");
        div.setAttribute("aria-labelledby", `${tab.id}-tab`);
        div.setAttribute("tabindex", "0");
        div.innerText = tab.content;

        tabContent.appendChild(div);
    });

    article.appendChild(nav);
    article.appendChild(tabContent);
    return article;
}

// Content builders
function buildSettingsContent() {
    const volumeControl = createVolumeControl();
    const graphicsDropdown = createGraphicsDropdown();
    const line = createLine();
    
    footerbody.append(volumeControl, line, graphicsDropdown);
}

function buildPlayContent() {
    const top = createGameSection(true);
    const bottom = createGameSection(false);
    const line = createLine();
    footerbody.append(top, line, bottom);
}

function buildProfileContent() {
    const line = createLine();
    const avatarRow = createAvatarRow();
    const tabbedContent = createTabbedContent();
    footerbody.append(avatarRow,line,tabbedContent);
}

// Event listeners
settingsbtn.addEventListener("click", () => {
    showPanel("Settings", buildSettingsContent);
});

profilebtn.addEventListener("click", () => {
    showPanel("", buildProfileContent); //footer header empty cuz i need to put avatar row in its place
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