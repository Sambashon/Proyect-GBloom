const curtain = document.querySelector("#curtain");
const xOut = document.querySelectorAll("#xOut");
const pauseBtn = document.getElementById("pauseBtn");
const statsBtn = document.getElementById("statsBtn");

const pauseMenu = document.getElementById("pausedMenu");
const statsMenu = document.getElementById("statsMenu");

const playBtn = document.getElementById("PLAYBtn");
const settingsBtn = document.getElementById("SETTINGSBtn");
const leaveBtn = document.getElementById("LEAVEBtn");

function closeMenus(button) {
    const parentMenu = button.closest(".buttonMenu");
    if (parentMenu) {
        parentMenu.classList.add("d-none");
    }
}

function showCurtain() {
    curtain.style.zIndex = "1";
    curtain.style.backgroundColor = "rgba(0, 0, 0, 0.3)"
}

function hideCurtain() {
    curtain.style.backgroundColor = "rgba(0, 0, 0, 0)"
    curtain.style.zIndex = "-1";
}

xOut.forEach(button => {
    button.addEventListener("click", () => {
        hideCurtain();
        closeMenus(button);
    });
});

pauseBtn.addEventListener("click", () => {
    showCurtain()
    pauseMenu.classList.remove("d-none");

    leaveMenu.classList.add("d-none");
    statsMenu.classList.add("d-none");
});

statsBtn.addEventListener("click", () => {
    showCurtain()
    statsMenu.classList.remove("d-none");

    leaveMenu.classList.add("d-none");
    pauseMenu.classList.add("d-none");
});

playBtn.addEventListener("click",() => {
    hideCurtain();
    closeMenus(playBtn);
})

settingsBtn.addEventListener("click", () =>{
    alert("Feature not yet implemented.")
})

leaveBtn.addEventListener("click", () =>{
    window.location.href = "../../homePage/home.html"
})