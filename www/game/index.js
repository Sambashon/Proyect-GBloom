let radio = new Sounds();
let listener = new InputListener(document.querySelector("canvas"), document.querySelectorAll(".INVENTORY>section"), radio);
let dino;
let utlimoSlot;
let turnoActual;
let faseActual;
let isTurnoTirarDado;
let turnoTerminado;
let dado;
let puntos;
let overlay;
let tablero;
let inventario = new Inventario();
let estado;
let colocar = true;
let empezar = false;
let username;
let recinto;
let overlayModel;

const dadoImg = document.querySelector(".DICE-CONTAINER>section>img");

async function setup(map, canvas) {
  dino = ENGINE.spawnDino("rojo", "");
  dino.position = [0, -100, 0];
  dino.rotation[1] = Matrix3D.convertToRad(-90);
  overlayModel = map.getModelById("overlay");

  await empezarTurno(map);

  document.addEventListener("click", () =>{
    ENGINE.map.draw(ENGINE.jgl, ENGINE.delta, ENGINE.width, ENGINE.height);
  })

  document.addEventListener("touchend", () =>{
    ENGINE.map.draw(ENGINE.jgl, ENGINE.delta, ENGINE.width, ENGINE.height);
  })


  document.querySelector("#loading").remove();
  playMusic();
}

async function update(map, dt) {
  if (listener.mouse.focus.slice(0, 4) == "slot") {
    utlimoSlot = listener.mouse.focus;
  }

  listener.mouse.world = listener.mouseToWorld(map.matViewProj, map.cameras[0]);
  listener.dragDinosaurio(dino, map, inventario.getDinosaurioById(dino.id) > 0);
  recinto = tablero.fijarDinosaurioRecinto(dino);
  if (listener.mouse.colocado) {
    console.log(recinto, colocar, recinto && colocar);
    if (recinto && colocar) {
      colocar = false;
      if (await tablero.agregarDinosaurio(dino, recinto)) {
        //DRAW = true;
        ENGINE.moveOverlayToLast(overlay);
        inventario.quitarDinosaurio(utlimoSlot);
        inventario.actualizarSlots(listener.slots);
        radio.playEffect("place");
        listener.mouse.onDinosaurio = false;
        dino.position = [0, -100, 0];
        await terminarTurno();
        isTurnoTerminadoI = setInterval(isTurnoTerminado, 2000, map);
        //DRAW = false;
      } else {
        //DRAW = true;
        colocar = true;
        dino.position = [0, -100, 0];
        ENGINE.map.draw(ENGINE.jgl, ENGINE.delta, ENGINE.width, ENGINE.height);
      }
    } else {
      colocar = true;
      //DRAW = true;
      dino.position = [0, -100, 0];
      ENGINE.map.draw(ENGINE.jgl, ENGINE.delta, ENGINE.width, ENGINE.height);
    }
  }
}

init();

function playMusic() {
  document.addEventListener('click', function handler() {
    radio.playSoundtrack("juiceMix");
    document.removeEventListener('click', handler);
  });
}

async function empezarTurno(map) {
  let solicitud = await executeScript("getters/getEstadoTurno.php");
  
  if (solicitud.result) {
    estado = solicitud.result;
  }

  solicitud = await executeScript("getters/getInventario.php");
  
  if (solicitud.success) {
    inventario.importarInventario(solicitud.result);
    inventario.actualizarSlots(listener.slots);
  } else {
    alert(solicitud.ErrMessage);
  }

  solicitud = await executeScript("getters/isTurnoTirarDado.php");
  if (solicitud.success) {
    isTurnoTirarDado = solicitud.result;
  }

  solicitud = await executeScript("getters/getDado.php");
  if (solicitud.success) {
    dado = solicitud.result;
    if (isTurnoTirarDado) {
      dadoImg.src = "";
    } else {
      dadoImg.src = `/Resources/dado/${dado}.svg`;
    }
  }

  ENGINE.map.objects = ENGINE.map.objects.filter(obj => obj.id !== "fichas" && obj.id !== "overlay");
  tablero = new Tablero(inventario);

  solicitud = await executeScript("getters/getTablero.php");

  if (solicitud.result) {
    tablero.importarTablero(solicitud.result);
  } else if (!solicitud.success) {
    alert(solicitud.ErrMessage);
  }

  await executeScript("getters/contarPuntos.php");

  let jugadores = await executeScript("getters/getJugadores.php");

  if (jugadores.result) {
    jugadores = jugadores.result;
  } else if (!jugadores.success) {
    alert(jugadores.ErrMessage);
  }

  drawPlayers(document.querySelector("table.leaderboard-table>tbody"), jugadores);

  if (estado == "turnoTerminado") {
    isTurnoTerminadoI = setInterval(isTurnoTerminado, 2000, map);
  }

  overlay = ENGINE.jgl.newObject({id: "overlay", model: overlayModel.model, position: [0, 10, 3], size: [2, 2, 2]});
  map.push(overlay);
}

async function terminarTurno() {
  const action = await executeScript("partida/terminarTurno.php", true);
  if (!action.success) {
    alert("No se pudo terminar turno");
  }
}

async function isTurnoTerminado(map) {
  let action = await executeScript("getters/isTurnoTerminado.php");
  if (!action.success) {
    alert("No se pudo determinar turno Terminado");
  } else {
    if (action.result && !empezar) {
      action = await executeScript("partida/pasarTurno.php", true);

      action = await executeScript("getters/isTurnoTirarDado.php");
      isTirarDado = action.result;
      empezar = true;

      colocar = true;
    } else if (empezar) {
      action = await executeScript("getters/getEstadoTurno.php");
      if (action?.result == "jugandoTurno") {
        await empezarTurno(map);
        clearInterval(isTurnoTerminadoI);
        empezar = false;
      }
    }
  }
}

function drawPlayers(list, players) {
    list.innerHTML = "";
    let leaderboard = players.sort((a, b) => b.puntos - a.puntos);

    leaderboard.forEach(player => {
      if (player.username == username) {
        puntos = player.puntos;
        list.innerHTML += `<tr><td>You</td><td>${player.puntos} pts</td></tr>`;
      } else {
        list.innerHTML += `<tr><td>${player.username}</td><td>${player.puntos} pts</td></tr>`;
      }
    });
}