const backBtn = document.getElementById("backBtn");
const virtualBtn = document.getElementById("virtual");
const syncBtn =document.getElementById("sync");
const editBtn = document.getElementById("editBtn");
const startBtn = document.getElementById('startBtn');

const partyTitle = document.getElementById("partyName");
const footerButtons = document.querySelectorAll(".ftbt");

let syncSelected = false;
let virtualSelected = false;

let buttonSelected = null;

footerButtons.forEach((button) => {
  button.addEventListener("click", () => {
    if (buttonSelected && buttonSelected !== button) {
      buttonSelected.classList.remove("bordered");
      button.classList.add("bordered");
      buttonSelected = button;
    }else if (!buttonSelected) {
      button.classList.add("bordered");
      buttonSelected = button;
    }

  });
});

editBtn.addEventListener("click", () => {
    const nameInput = document.createElement("input");
    nameInput.type = "text";
    nameInput.classList.add("fluid");
    partyTitle.replaceWith(nameInput);
    nameInput.focus();

    nameInput.addEventListener("keydown", (e) =>{
        if(e.key === "Enter"){
            const name = nameInput.value;
            partyTitle.innerText = name;
            nameInput.replaceWith(partyTitle);
        }
    })

    nameInput.addEventListener("focusout", () =>{
        const name = nameInput.value;
        partyTitle.innerText = name;
        nameInput.replaceWith(partyTitle);
    })
})

backBtn.addEventListener("click", () =>{
    window.location.href = "../../homePage/home.html";
})

virtualBtn.addEventListener("click", () =>{
    virtualSelected = true;
    syncSelected = false;
});

syncBtn.addEventListener("click", () =>{
    syncSelected = true;
    virtualSelected = false;
})

startBtn.addEventListener("click", async () =>{
    let modo;
    if(virtualSelected){
        modo = "virtual";
    }else if(syncSelected){
        modo = "seguimiento";
    }

    let action = await fetch("/php/scripts/game/lobby/crearPartida.php", {
        method: "POST",
        // Cantidad de jugadores por defecto
        body: JSON.stringify({nombre: partyTitle.textContent, cantidadJugadores: 5, modo: modo})
    }).then((response) => {
        return response.json();
    });

    if (action.status !== "success") {
        alert("Error al configurar la Partida");
        console.log(action.ErrMessage, action.ErrDetails);
    }

    action = await fetch("/php/scripts/game/lobby/joinLobby.php", {
        method: "POST",
        body: JSON.stringify({codigo: action.result})
    }).then((response) => {
        return response.json();
    });

    if (action.status == "success") {
        window.location.href = "/lobby/hostLobby/lobby.html";
    } else {
        alert("Error al unirse a la sala de espera");
        console.log(action.ErrMessage, action.ErrDetails);
    }
})