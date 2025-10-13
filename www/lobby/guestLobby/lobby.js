let codigo;
let nombre;
let host;
let jugadores;
let comienza;

const backBtn = document.getElementById("backBtn");
const joinBtn = document.getElementById("joinBtn");
const startBtn = document.getElementById("startBtn");
const playersList = document.querySelectorAll("section.col");

document.addEventListener("DOMContentLoaded", async () => {
    await getLobbyInfo();
    jugadoresA = jugadores;
    document.querySelector("#gameTitle").textContent = nombre;

    drawPlayers(playersList, jugadoresA, jugadores);

    setInterval(async () => {
        jugadoresA = jugadores;
        await getLobbyInfo();
        if (jugadores.length != jugadoresA.length) {
            drawPlayers(playersList, jugadoresA, jugadores);
        }

        if (comienza) {
            window.location.href = "../../game/gameGuest/game.html";
        }
    }, 1000);
});

backBtn.addEventListener("click", async () =>{
    let action = await fetch("/php/scripts/game/lobby/leaveLobby.php", {method: "POST"}).then(function (response) {
        return response.json();
    });

    if (action.status == "success") {
        window.location.href = "../../homePage/home.html"
    } else {
        alert("Ocurrio un Error al tratar de salir de la sala de espera");
    }
})

if(joinBtn){
    joinBtn.addEventListener("click", () =>{
        joinMatch();
    })
}

async function joinMatch() {
    let action = await fetch("/php/scripts/game/lobby/joinLobby.php", {method: "POST"}).then(function (response) {
        return response.json();
    });

    if (action.status == "success") {
        joinBtn.remove();
    } else {
        alert(action.ErrMessage);
    }
}

async function getLobbyInfo() {
    let info = await fetch("/php/scripts/game/lobby/lobbyInfo.php", {method: "POST"}).then((response) => {
        return response.json();
    });

    if (info.status == "success") {
        const result = info.result;
        codigo = result.codigo;
        jugadores = result.jugadores;
        comienza = result.comienza;
        nombre = result.nombre;
        host = result.host;
    } else {
        alert("Ocurrio un Error al tratar de obtener los detalles de la sala de espera");
    }
}

function drawPlayers(list, playersA, players) {
    if (players.length > playersA.length) {
        counter = players.length;
    } else {
        counter = playersA.length;
    }

    for (let i = 0; i < counter; i++) {
        const column = list[i];
        const player = players[i];

        if (players[i]) {
            column.innerHTML = `<div class="profileContainer">
            <img src="../../Resources/Icons/defautlprofilepic.png" alt="profilepicture" height="100" width="100">
            </div>
                        <p>${player.username}</p>`;
        } else {
            column.innerHTML = `<div class="profileContainer">
            </div>
                        <p>...</p>`;
        }
    }
}