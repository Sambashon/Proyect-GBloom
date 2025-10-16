let codigo;
let nombre;
let host;
let cantidadMaxima;
let jugadores;
let jugadoresA;
let comienza;

const backBtn = document.getElementById("backBtn");
const joinBtn = document.getElementById("joinBtn");
const startBtn = document.getElementById("startBtn");
const playersList = document.querySelectorAll("section.col");

document.querySelector("#copyLinkBtn").addEventListener("click", async () => {
    const inviteLink = window.location.origin + "/game/join?codigo=" + codigo;
    
    try {
        await navigator.clipboard.writeText(inviteLink);
        const btn = document.querySelector("#copyLinkBtn");
        const oldText = btn.textContent;
        btn.textContent = "Copied!";
        setTimeout(() => btn.textContent = oldText, 2000);
    } catch (err) {
        alert("Failed to copy link");
    }
});

document.addEventListener("DOMContentLoaded", async () => {
    await getLobbyInfo();
    jugadoresA = jugadores;
    document.querySelector("#gameTitle").textContent = nombre;
    document.querySelector("#gamecode").textContent = codigo;
    document.querySelector("#cantidadJugadores").textContent = `${jugadores.length}/${cantidadMaxima} Players`;

    drawPlayers(playersList, jugadoresA, jugadores);

    setInterval(async () => {
        jugadoresA = jugadores;
        await getLobbyInfo();
        if (jugadores.length != jugadoresA.length) {
            drawPlayers(playersList, jugadoresA, jugadores);
            document.querySelector("#cantidadJugadores").textContent = `${jugadores.length}/${cantidadMaxima} Players`;
        }
    }, 1000);
});

backBtn.addEventListener("click", async () =>{
    let action = await fetch("/php/scripts/game/lobby/leaveLobby.php", {method: "POST"}).then(function (response) {
        return response.json();
    });

    if (action.status == "success") {
        window.location.href = "/homePage/home.html"
    } else {
        alert("Ocurrio un Error al tratar de salir de la sala de espera");
    }
})

if(startBtn){
    startBtn.addEventListener("click", async () => {
        let action = await fetch("/php/scripts/game/lobby/iniciarPartida.php", {method: "POST"}).then(function (response) {
            return response.json();
        });

        if (action.status == "success") {
            window.location.href = "/game/game.html";
        } else {
            alert("Ocurrio un Error al tratar de iniciar partida");
        }
    })
}

async function joinMatch() {
    let action = await fetch("/php/scripts/game/lobby/joinLobby.php", {method: "POST"}).then(function (response) {
        return response.json();
    });

    if (action.state == "success") {
        action = await fetch("/php/scripts/game/lobby/iniciarPartida.php", {method: "POST"}).then(function (response) {
            return response.json();
        });
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
        cantidadMaxima = result.cantidadJugadores;
        comienza = result.comienza;
        nombre = result.nombre;
        host = result.host;
    } else {
        window.location.href = "/homePage/home.html"
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
            <img src="/Resources/Icons/defautlprofilepic.png" alt="profilepicture" height="100" width="100">
            </div>
                        <p>${player.username}</p>`;
        } else {
            column.innerHTML = `<div class="profileContainer">
            </div>
                        <p>...</p>`;
        }
    }
}

