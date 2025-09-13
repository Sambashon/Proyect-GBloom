let radio = new Sounds();
let listener = new InputListener(document.querySelector("canvas"), document.querySelectorAll(".INVENTORY>section"), radio);
let dino;
let utlimoSlot;
let overlay;
let tablero;
let inventario = new Inventario();
let state;
let recinto;

async function setup(map, canvas) {
  dino = ENGINE.spawnDino("rojo", "");
  dino.position = [0, -100, 0];
  dino.rotation[1] = Matrix3D.convertToRad(-90);

  /* El overlay es un plano que tiene una textura con areas transparentes (png). El motor 3d implementado todavía
  no maneja este tipo de objetos de forma particular a otros (como sería apropiado),
  es por eso que para evitar que el renderizado de los dinosaurios lleve sopresas, siempre hay que mantener estos 
  objetos últimos en el orden de la fila de dibujado. */

  let overlayModel = map.getModelById("overlay");
  overlay = ENGINE.jgl.newObject({id: "overlay", model: overlayModel.model, position: [0, 10, 3], size: [2, 2, 2]});
  map.push(overlay);

  // Set Up Tablero e Inventario
  let solicitud = await fetch("/php/scripts/game/getInventario.php").then(function (response) {
    return response.json();
  });
  
  if (solicitud.state == "success") {
    inventario.importarInventario(solicitud.result);
    inventario.actualizarSlots(listener.slots);
  } else {
    alert(solicitud.ErrMessage);
  }

  tablero = new Tablero(inventario);
}

function update(map, dt) {
  if (listener.mouse.focus.slice(0, 4) == "slot") {
    utlimoSlot = listener.mouse.focus;
  }

  listener.mouse.world = listener.mouseToWorld(map.matViewProj, map.cameras[0]);
  state = listener.dragDinosaurio(dino, map, inventario.getDinosaurioById(dino.id) > 0);
  recinto = tablero.fijarDinosaurioRecinto(dino);
  if (state === "colocado") {  
    if (recinto) {
      if (tablero.agregarDinosaurio(dino, recinto)) {
        dino = ENGINE.spawnDino("rojo", "rojo");
        ENGINE.moveOverlayToLast(overlay);
        inventario.quitarDinosaurio(utlimoSlot);
        inventario.actualizarSlots(listener.slots);
        radio.playEffect("place");
        dino.position = [0, -100, 0];
        dino.rotation[1] = Matrix3D.convertToRad(-90);
      } else {
        dino.position = [0, -100, 0];
      }
    } else {
      dino.position = [0, -100, 0];
    }
  }
}

init();