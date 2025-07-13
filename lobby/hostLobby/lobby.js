const backBtn = document.getElementById("backBtn");
const virtualBtn = document.getElementById("virtual");
const syncBtn =document.getElementById("sync");


backBtn.addEventListener("click", () =>{
    window.location.href = "../../homePage/home.html"
})
virtualBtn.addEventListener("click", () =>{
    window.location.href = "../../gamePage/game.html"
});

syncBtn.addEventListener("click", () =>{
    alert("Under construction...")
})