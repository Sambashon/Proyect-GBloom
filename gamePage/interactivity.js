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

xOut.forEach(button => {
    button.addEventListener("click", () => {
        closeMenus(button);
    });
});

pauseBtn.addEventListener("click", () => {
    pauseMenu.classList.remove("d-none");
    statsMenu.classList.add("d-none");
});

statsBtn.addEventListener("click", () => {
    statsMenu.classList.remove("d-none");
    pauseMenu.classList.add("d-none");
});

playBtn.addEventListener("click",() => {
    closeMenus(playBtn);
})

settingsBtn.addEventListener("click", () =>{
    alert("Feature not yet implemented.")
})

leaveBtn.addEventListener("click", () =>{
    window.location.href = "../homePage/home.html"
})