// Define la clase GBloomEngine la cual es la implementacion del motor 3D en el juego para su uso simplificado
class GBloomEngine {
    constructor(context, setup, update) {
        this.context = context;
        this.setup = setup;
        this.update = update;
    }

    async init() {
      this.scene = await fetch(this.context + "/scene.json").then( function (response) {
      return response.json();
      });

      this.canvas = document.querySelector("canvas");
      this.width = innerWidth;
      this.height = innerHeight;
      this.canvas.width = this.width;
      this.canvas.height = this.height;

      if (!navigator.gpu) {
        let context = this.canvas.getContext("webgpu");
        let canvasFormat = navigator.gpu.getPreferredCanvasFormat();

        let adapter = await navigator.gpu.requestAdapter();
        const limits = adapter.limits;
        let device = await adapter.requestDevice({
            requiredLimits: {
                maxTextureDimension2D: limits.maxTextureDimension2D
            }
        });

        context.configure({
            device: device,
            format: canvasFormat
        });

        this.jgl = new JSCGL(device, context, canvasFormat, this.width, this.height);
        await this.jgl.init();

        this.map = new Scene(this.scene);
        await this.map.loadScene(this.jgl);

      } else {

        const gl = this.canvas.getContext("webgl2");

        JSCGL.setGL(gl);
        gl.disable(gl.CULL_FACE);
        this.jgl = new JSCGL(this.width, this.height);

        this.map = new Scene(this.scene);
        await this.map.loadScene(this.jgl);

        this.jgl.updateDimensions(this.width, this.height);
      }

      this.lastFrameTime = performance.now();
      
      await setup(this.map, this.canvas);
      requestAnimationFrame(this.frame.bind(this));
    }

    frame() {
      const thisFrameTime = performance.now();
      this.delta = (thisFrameTime - this.lastFrameTime) / 1000;
      this.lastFrameTime = thisFrameTime;

      if (DRAW) {
        update(this.map, this.delta);
        this.map.draw(this.jgl, this.delta, this.width, this.height);
      }

      requestAnimationFrame(this.frame.bind(this));
    }

    spawnDino(id, name) {
      let model = this.map.getModelById(id);
      let dinosaurus = this.jgl.newObject({id: name, model: model.model, position: [0, 0, 0], size: [0.8, 0.8, 0.8], rotation: [0, 0, 0]});
      this.map.push(dinosaurus);

      return this.map.objects.at(-1);
    }

    moveOverlayToLast(overlay) {
      this.map.objects.splice(-2, 1);
      this.map.push(overlay);
    }
}

// Aqui se definen variables globales usadas en todo el juego
let DRAW = false;
let ENGINE;
let BACKGROUND_COLOR = [0.643, 0.255, 0.255,1];

// Define una funcion para iniciar el motor 3D cuando el index termine de definir las funciones setup y update
function init() {
  document.addEventListener("DOMContentLoaded", function() {
    ENGINE = new GBloomEngine("/engine/gameScene", setup, update);
    ENGINE.init();
  });

  window.addEventListener('beforeunload', function() {
    DRAW = false;
  });
}
