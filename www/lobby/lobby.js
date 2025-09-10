const backBtn = document.getElementById("backBtn");
const joinBtn = document.getElementById("joinBtn");
const startBtn = document.getElementById("startBtn");

backBtn.addEventListener("click", () =>{
    window.location.href = "../../homePage/home.html"
})

if(joinBtn){
    joinBtn.addEventListener("click", () =>{
        joinMatch();
    })
}

if(startBtn){
    startBtn.addEventListener("click", () =>{
        window.location.href = "../../game/gameGuest/game.html";
    })
}

async function joinMatch() {
    let accion = await fetch("/php/scripts/game/joinLobby.php", {method: "POST"}).then(function (response) {
        return response.json();
    });

    if (accion.state == "success") {
        accion = await fetch("/php/scripts/game/iniciarPartida.php", {method: "POST"}).then(function (response) {
            return response.json();
        });

        if (accion.state == "success") {
            alert("Partida Unida e Iniciada");
            window.location.href = "/game/gameGuest/game.html";
        } else {
            alert(accion.ErrMessage);
        }
    } else {
        alert(accion.ErrMessage);
    }
}

