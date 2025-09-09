const backBtn = document.getElementById("backBtn");
const joinBtn = document.getElementById("joinBtn");
const startBtn = document.getElementById("startBtn");

backBtn.addEventListener("click", () =>{
    window.location.href = "../../homePage/home.html"
})

if(joinBtn){
    joinBtn.addEventListener("click", () =>{
        window.location.href = "../../game/gameGuest/game.html";
    })
}

if(startBtn){
    startBtn.addEventListener("click", () =>{
        window.location.href = "../../game/gameHuest/game.html";
    })
}

