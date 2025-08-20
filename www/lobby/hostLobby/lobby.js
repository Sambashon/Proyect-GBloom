const startBtn = document.getElementById("startBtn");
startBtn.addEventListener("click", () =>{
    window.location = "../../game/gameHost/game.html"
})

const leaveBtn = document.getElementById("LEAVEBtn");
leaveBtn.addEventListener("click", () =>{
    window.location = "../../homePage/home.html";
})
const endSession = document.getElementById("endSession");
endSession.addEventListener("click", () =>{
    alert("Under construction...");
    window.location = "../../homePage/home.html";
})