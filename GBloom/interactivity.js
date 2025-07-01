//defino los botones
const profileBtn = document.getElementById("PROFILE");
const settingBtn = document.getElementById("SETTINGS");
const muteBtn = document.getElementById("MUTE");
const PROFILE = document.querySelector(".drop-down");
const SETTINGS = document.querySelector(".side-bar");

const background = document.querySelector(".background"); // landing
const playBtn = document.querySelector(".play");//landing;

const leaveBtn = document.getElementById("LEAVE");
const statsBtn = document.getElementById("STATS");
const centerDiv = document.querySelector(".center-section");

if(playBtn){
    playBtn.addEventListener("click", () =>{
        setTimeout(() => {
            window.open("../GamePage/game.html", "_self");
        }, 250)
    });
}

if(leaveBtn){
    leaveBtn.addEventListener("click", () =>{
        window.open("../LandingPage/landing.html", "_self");
    });
}

if(statsBtn){
    centerDiv.classList.toggle("hidden");
    statsBtn.addEventListener("click", () =>{
        centerDiv.classList.toggle("hidden");
    });
}

profileBtn.addEventListener("click", () =>{
    PROFILE.classList.toggle('visible');
    if(background){ //asi no me saltan errores en la página de juego, porque este fondo, no existe aún esto es algo provisorio
        background.classList.toggle("visible");
    }
});

settingBtn.addEventListener("click", () =>{
    SETTINGS.classList.toggle("visible");
    if(background){
        background.classList.toggle("visible");
    }
});

muteBtn.addEventListener("click", () =>{
    //funcionalidad de muteo
});