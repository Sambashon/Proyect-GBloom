class InputListener {
    mouse = {
        position: {x: null, y: null},
        world: {x: null, y: null, z: null},
        pressing: false,
        onDinosaurio: false,
        on: "",
        focus: "canvas"
    }

    canvas;
    slots;
    radio;

    constructor(canvas, slots, radio) {
        this.canvas = canvas;
        this.slots = slots;
        this.radio = radio;

        canvas.addEventListener("mousedown", (evt) => {
            this.mouse.pressing = true;
        });

        canvas.addEventListener("mouseup", (evt) => {
            this.mouse.pressing = false;
        });

        canvas.addEventListener("mousemove", (evt) => {
            this.rect = canvas.getBoundingClientRect();
            const dpr = window.devicePixelRatio || 1;
            this.mouse.position.x = (evt.clientX - this.rect.left) * dpr;
            this.mouse.position.y = (evt.clientY - this.rect.top)  * dpr;
        });

        canvas.addEventListener("mouseleave", () => {
            this.mouse.pressing = false;
            this.mouse.on = "";
        });

        canvas.addEventListener("mouseenter", () => {
            this.mouse.on = "canvas";
        });

        canvas.addEventListener("click", () => {
            if (this.mouse.focus == "dinosaurio") {
                this.mouse.focus = "canvas";
                this.radio.playEffect("place");
            } else if (this.mouse.onDinosaurio && this.mouse.focus == "canvas") {
                this.mouse.focus = "dinosaurio";
                this.radio.playEffect("place");
            } else {
                this.mouse.focus = "canvas";
            }
        });

        for (let i = 0; i < slots.length; i++) {
            slots[i].addEventListener("click", () => {
                this.mouse.focus = "slot" + i;
            });
        }
    }
    
    // Detecta si el mouse está sobre un dinoasaurio
    onDinosaurio(dinosaurio, mouseMundo) {
        const offset = 10;

        if (!dinosaurio || !mouseMundo) {
            return undefined;
        }

        const distancia = this.sub(mouseMundo, dinosaurio.position);
        if (Math.abs(distancia[0]) <= offset && Math.abs(distancia[1]) <= offset && Math.abs(distancia[2]) <= offset) {       
            this.mouse.onDinosaurio = true;
        }else {
            this.mouse.onDinosaurio = false;
        }
    }

    // Mueve al dinosaurio si el mouse está presionado y sobre el canvas o un slot
    dragDinosaurio(dinosaurio, map, move) {
        let posMundo;
        switch (this.mouse.focus) {
            case "slot0":
                dinosaurio.model = map.getModelById("amarillo").model;
                dinosaurio.id = "amarillo";
                dinosaurio.position = [0, -100, 0];
                
                if (move) {
                    posMundo = this.mouseToWorld(map.matViewProj, map.cameras[0]);
                    dinosaurio.position = posMundo || dinosaurio.position;
                }
                break;
            case "slot1":
                dinosaurio.model = map.getModelById("azul").model;
                dinosaurio.id = "azul";
                dinosaurio.position = [0, -100, 0];
                
                if (move) {
                    posMundo = this.mouseToWorld(map.matViewProj, map.cameras[0]);
                    dinosaurio.position = posMundo || dinosaurio.position;
                }
                break;
            case "slot2": 
                dinosaurio.model = map.getModelById("rojo").model;
                dinosaurio.id = "rojo";
                dinosaurio.position = [0, -100, 0];
                
                if (move) {
                    posMundo = this.mouseToWorld(map.matViewProj, map.cameras[0]);
                    dinosaurio.position = posMundo || dinosaurio.position;
                }
                break;
            case "slot3":
                dinosaurio.model = map.getModelById("morado").model;
                dinosaurio.id = "morado";
                dinosaurio.position = [0, -100, 0];
                
                if (move) {
                    posMundo = this.mouseToWorld(map.matViewProj, map.cameras[0]);
                    dinosaurio.position = posMundo || dinosaurio.position;
                }
                break;
            case "slot4":
                dinosaurio.model = map.getModelById("verde").model;
                dinosaurio.id = "verde";
                dinosaurio.position = [0, -100, 0];
                
                if (move) {
                    posMundo = this.mouseToWorld(map.matViewProj, map.cameras[0]);
                    dinosaurio.position = posMundo || dinosaurio.position;
                }
                break;
            case "slot5":
                dinosaurio.model = map.getModelById("naranja").model;
                dinosaurio.id = "naranja";
                dinosaurio.position = [0, -100, 0];
                
                if (move) {
                    posMundo = this.mouseToWorld(map.matViewProj, map.cameras[0]);
                    dinosaurio.position = posMundo || dinosaurio.position;
                }
                break;
            case "canvas":
                this.onDinosaurio(dinosaurio, this.mouse.world);
                return "colocado";
            default:
                if (move) {
                    posMundo = this.mouseToWorld(map.matViewProj, map.cameras[0]);
                    dinosaurio.position = posMundo || dinosaurio.position;
                }
                break;
        }

    }

    // Métodos para obtener la posición del mouse en relación al mapa

    dot(a, b) {
        return a[0]*b[0] + a[1]*b[1] + a[2]*b[2];
    }

    sub(a, b) {
        return [
            a[0] - b[0],
            a[1] - b[1],
            a[2] - b[2]
        ];
    }

    normalize(v) {
        const len = Math.hypot(v[0], v[1], v[2]);
        return len > 0 ? [v[0]/len, v[1]/len, v[2]/len] : [0,0,0];
    }

    divideW(v) {
        return v.map((c, i) => i < 3 ? c/v[3] : 1);
    }

    normalizeScreenPos(x, y, width, height) {
        const ndcX = 1 - (2 * x / width);
        const ndcY = 1 - (2 * y / height);
        return [ndcX, ndcY];
    }

    intersectsPlane(origen, dir, planoNormal, planoPunto) {
        const denom = this.dot(planoNormal, dir);
        if (Math.abs(denom) < 1e-6) return [0, 0, 0];
        const t = this.dot(this.sub(planoPunto, origen), planoNormal) / denom;
        if (t < 0) return [0, 0, 0];
        return [
            origen[0] + dir[0]*t,
            origen[1] + dir[1]*t,
            origen[2] + dir[2]*t,
        ];
    }

    mouseToWorld(matViewProj, camera) {
        let ndcX, ndcY;
        try {
        [ndcX, ndcY] = this.normalizeScreenPos(
            this.mouse.position.x,
            this.mouse.position.y,
            this.rect.width,
            this.rect.height
        );
        }
        catch {
            return null;
        }

        const clipNear = [ndcX, ndcY, 0, 1];
        const clipFar = [ndcX, ndcY, 1, 1];
        const invW = Matrix3D.matTRS(camera.position[0], camera.position[1], camera.position[2], [0, 0, 0], camera.rotation, 1, 1, 1);
        const invVP = Matrix3D.invertMatrix(matViewProj);
        let worldFar = Matrix3D.multiplyMatrixVector(invW, this.divideW(Matrix3D.multiplyMatrixVector(invVP, clipFar)));
        let worldNear = Matrix3D.multiplyMatrixVector(invW, this.divideW(Matrix3D.multiplyMatrixVector(invVP, clipNear)));
        let rayDirection = this.normalize(this.sub(worldNear, worldFar));
        let final = this.intersectsPlane(worldNear, rayDirection, [0,1,0], [0,15,0]);
        
        return final;
    }

}