class Inventario {
    amarillo;
    azul;
    rojo;
    morado;
    verde;
    naranja;

    constructor () {
        this.amarillo = 2;
        this.azul = 2;
        this.rojo = 2;
        this.morado = 2;
        this.verde = 2;
        this.naranja = 2;
    }

    importarInventario(inventario) {
        for (let i = 0; i < inventario.length; i++) {
            const slot = inventario[i];
            switch (slot.dinosaurioId) {
                case "amarillo":
                    this.amarillo = slot.cantidad;
                    break;
                case "azul":
                    this.azul = slot.cantidad;
                    break;
                case "rojo":
                    this.rojo = slot.cantidad;
                    break;
                case "morado":
                    this.morado = slot.cantidad;
                    break;
                case "verde":
                    this.verde = slot.cantidad;
                    break;
                case "naranja":
                    this.naranja = slot.cantidad;
                    break;
            }
        }
    }

    setInventario(inventario) {
        this.amarillo = inventario.amarillo;
        this.azul = inventario.azul;
        this.rojo = inventario.rojo;
        this.morado = inventario.morado;
        this.verde = inventario.verde;
        this.naranja = inventario.naranja;
    }

    getInventario() {
        return {amarillo: this.amarillo, azul: this.azul, rojo: this.rojo, morado: this.morado, verde: this.verde, naranja: this.naranja};
    }

    getDinosaurioById(id) {
        switch (id) {
            case "amarillo":
                return this.amarillo;
            case "azul":
                return this.azul;
            case "rojo":
                return this.rojo;
            case "morado":
                return this.morado;
            case "verde":
                return this.verde;
            case "naranja":
                return this.naranja;
        }
    }

    quitarDinosaurio(slot) {
        switch (slot) {
            case "slot0":
                if (this.amarillo > 0) this.amarillo -= 1;
                break;
            case "slot1":
                if (this.azul > 0) this.azul -= 1;
                break;
            case "slot2":
                if (this.rojo > 0) this.rojo -= 1;
                break;
            case "slot3":
                if (this.morado > 0) this.morado -= 1;
                break;
            case "slot4":
                if (this.verde > 0) this.verde -= 1;
                break;
            case "slot5":
                if (this.naranja > 0) this.naranja -= 1;
                break;
        }
    }

    // Actualiza el contador de los slots
    actualizarSlots (slots) {
        for (let i = 0; i < slots.length; i++) {
            const span = slots[i].querySelector("span");
            switch (i) {
                case 0:
                    span.textContent = this.amarillo + "X";
                    break;
                case 1:
                    span.textContent = this.azul + "X";
                    break;
                case 2:
                    span.textContent = this.rojo + "X";
                    break;
                case 3:
                    span.textContent = this.morado + "X";
                    break;
                case 4:
                    span.textContent = this.verde + "X";
                    break;
                case 5:
                    span.textContent = this.naranja + "X";
                    break;
            }
        }
    }
}