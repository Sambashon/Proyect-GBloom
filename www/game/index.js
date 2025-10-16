let radio = new Sounds();
let listener = new InputListener(document.querySelector("canvas"), document.querySelectorAll(".INVENTORY>section"), radio);
let dino;
let utlimoSlot;
let turnoActual;
let faseActual;
let isTurnoTirarDado;
let dado;
let overlay;
let tablero;
let inventario = new Inventario();
let state;
let recinto;

async function setup(map, canvas) {
  dino = ENGINE.spawnDino("rojo", "");
  dino.position = [0, -100, 0];
  dino.rotation[1] = Matrix3D.convertToRad(-90);

  //await empezarTurno();

  /* El overlay es un plano que tiene una textura con areas transparentes (png). El motor 3d implementado todavía
  no maneja este tipo de objetos de forma particular a otros (como sería apropiado),
  es por eso que para evitar que el renderizado de los dinosaurios lleve sopresas, siempre hay que mantener estos 
  objetos últimos en el orden de la fila de dibujado. */

  let overlayModel = map.getModelById("overlay");
  overlay = ENGINE.jgl.newObject({id: "overlay", model: overlayModel.model, position: [0, 10, 3], size: [2, 2, 2]});
  map.push(overlay);

  // Set Up Tablero e Inventario
  let solicitud = await executeScript("getters/getInventario.php");
  
  if (solicitud.success) {
    inventario.importarInventario(solicitud.result);
    inventario.actualizarSlots(listener.slots);
  } else {
    alert(solicitud.ErrMessage);
  }

  tablero = new Tablero(inventario);

  document.querySelector("#loading").remove();
  playMusic();
}

async function update(map, dt) {
  if (listener.mouse.focus.slice(0, 4) == "slot") {
    utlimoSlot = listener.mouse.focus;
  }

  listener.mouse.world = listener.mouseToWorld(map.matViewProj, map.cameras[0]);
  state = listener.dragDinosaurio(dino, map, inventario.getDinosaurioById(dino.id) > 0);
  recinto = tablero.fijarDinosaurioRecinto(dino);
  if (state === "colocado") {  
    if (recinto) {
      if (tablero.agregarDinosaurio(dino, recinto)) {
        //DRAW = false;
        //action = await executeScript("partida/colocarDinosaurio.php", {dinosaurioId: dino.id, recinto: recinto});
        //if (!action.success) {
        //  alert(action.ErrMessage);
        //}

        //action = await executeScript("partida/colocarDinosaurioNulls.php", {dinosaurioId: dino.id, recinto: recinto});
        //if (!action.success) {
        //  alert(action.ErrMessage);
        //}
        dino = ENGINE.spawnDino("rojo", "rojo");
        ENGINE.moveOverlayToLast(overlay);
        inventario.quitarDinosaurio(utlimoSlot);
        inventario.actualizarSlots(listener.slots);
        radio.playEffect("place");
        dino.position = [0, -100, 0];
        dino.rotation[1] = Matrix3D.convertToRad(-90);
        //action = await executeScript("partida/terminarTurno.php", true);
        //DRAW = false;
      } else {
        dino.position = [0, -100, 0];
      }
    } else {
      dino.position = [0, -100, 0];
    }
  }
}

init();

async function executeScript(script, body) {
  let action;
  if (body) {
    action = await fetch("/php/scripts/game/" + script, {
      method: "POST",
      body: body
    }).then(function (response) {
      return response.json();
    });
  } else {
    action = await fetch("/php/scripts/game/" + script).then(function (response) {
      return response.json();
    });
  }

    if (action.result) {
      return {success: true, result: action.result.result};
    } else if (action.ErrMessage) {
      return {success: false, ErrMessage: action.ErrMessage}
    } else {
      return {success: true};
    }

}

function playMusic() {
  document.addEventListener('click', function handler() {
    radio.playSoundtrack("juiceMix");
    document.removeEventListener('click', handler);
  });
}