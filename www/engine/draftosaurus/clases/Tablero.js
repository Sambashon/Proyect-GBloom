class Tablero {
    soledad;
    romance;
    igualdad;
    desigualdad;
    monarquia;
    tres;
    rio;

    tablero;

    constructor(tablero) {
        this.tablero = tablero;
        this.soledad = {dinosaurios: [], cupos: 1};
        this.romance = {dinosaurios: [], cupos: 6};
        this.igualdad = {dinosaurios: [], cupos: 6};
        this.desigualdad = {dinosaurios: [], cupos: 6};
        this.monarquia = {dinosaurios: [], cupos: 1};
        this.tres = {dinosaurios: [], cupos: 3};
        this.rio = {dinosaurios: [], cupos: 6};
    }

    getTablero() {
        return {soledad: this.soledad.dinosaurios, romance: this.romance.dinosaurios, igualdad: this.igualdad.dinosaurios, desigualdad: this.desigualdad.dinosaurios, monarquia: this.monarquia.dinosaurios, tres: this.tres.dinosaurios};
    }

    setTablero(tablero) {
        this.soledad = tablero.soledad;
        this.romance = tablero.romance;
        this.igualdad = tablero.igualdad;
        this.desigualdad = tablero.desigualdad;
        this.monarquia = tablero.monarquia;
        this.tres = tablero.tres;
        this.rio = tablero.rio;
    }

    importarTablero(tablero) {
        for (let i = 0; i < tablero.length; i++) {
            const recinto = tablero[i];
            const dinosaurio = ENGINE.spawnDino(recinto.dinosaurioId);
            dinosaurio.rotation[1] = Matrix3D.convertToRad(-90);
            this.colocarDinosaurioRecinto(dinosaurio, recinto.recinto);
            console.log(dinosaurio);
            switch (recinto.recinto) {
                case "soledad":
                    this.soledad.dinosaurios.push(dinosaurio);
                    break;
                case "romance":
                    this.romance.dinosaurios.push(dinosaurio);
                    break;
                case "igualdad":
                    this.igualdad.dinosaurios.push(dinosaurio);
                    break;
                case "desigualdad":
                    this.desigualdad.dinosaurios.push(dinosaurio);
                    break;
                case "monarquia":
                    this.monarquia.dinosaurios.push(dinosaurio);
                    break;
                case "tres":
                    this.tres.dinosaurios.push(dinosaurio);
                    break;
                case "rio":
                    this.rio.dinosaurios.push(dinosaurio);
                    break;
            }
        }
    }

    fijarDinosaurioRecinto(dinosaurio) {
        switch (true) {
            case (dinosaurio.position[0] >= -82 && dinosaurio.position[2] >= -84) && (dinosaurio.position[0] <= -25 && dinosaurio.position[2] <= -40):
                switch (this.igualdad.dinosaurios.length) {
                    case 0:
                        dinosaurio.position = [-78, 15, -60];
                        break;
                    case 1:
                        dinosaurio.position = [-70, 15, -60];
                        break;
                    case 2:
                        dinosaurio.position = [-59, 15, -60];
                        break;
                    case 3:
                        dinosaurio.position = [-49, 15, -60];
                        break;
                    case 4:
                        dinosaurio.position = [-38, 15, -60];
                        break;
                    case 5:
                        dinosaurio.position = [-29, 15, -60];
                        break;
                    default:
                        break;
                }
            return "igualdad";
            case (dinosaurio.position[0] >= -82 && dinosaurio.position[2] >= -21) && (dinosaurio.position[0] <= -39 && dinosaurio.position[2] <= 20):
                switch (this.tres.dinosaurios.length) {
                    case 0:
                        dinosaurio.position = [-70, 15, 8];
                        break;
                    case 1:
                        dinosaurio.position = [-62, 15, -7];
                        break;
                    case 2:
                        dinosaurio.position = [-53, 15, 9];
                        break;
                    default:
                        break;
                }
                return "tres";
            case (dinosaurio.position[0] >= -74 && dinosaurio.position[2] >= 37) && (dinosaurio.position[0] <= -30 && dinosaurio.position[2] <= 82):
                switch (this.romance.dinosaurios.length) {
                    case 0:
                        dinosaurio.position = [-62, 15, 68];
                        break;
                    case 1:
                        dinosaurio.position = [-52, 15, 68];
                        break;
                    case 2:
                        dinosaurio.position = [-42, 15, 68];
                        break;
                    case 3:
                        dinosaurio.position = [-62, 15, 48];
                        break;
                    case 4:
                        dinosaurio.position = [-52, 15, 48];
                        break;
                    case 5:
                        dinosaurio.position = [-42, 15, 48];
                        break;
                    default:
                        break;
                }
                return "romance";
            case (dinosaurio.position[0] >= 29 && dinosaurio.position[2] >= -74) && (dinosaurio.position[0] <= 58 && dinosaurio.position[2] <= -54):
                if (this.monarquia.dinosaurios.length < this.monarquia.cupos) {
                    dinosaurio.position = [44, 15, -62];
                } else {
                }
                return "monarquia";
            case (dinosaurio.position[0] >= 21 && dinosaurio.position[2] >= -16) && (dinosaurio.position[0] <= 82 && dinosaurio.position[2] <= 26):
                switch (this.desigualdad.dinosaurios.length) {
                    case 0:
                        dinosaurio.position = [24, 15, 8];
                        break;
                    case 1:
                        dinosaurio.position = [33, 15, 8];
                        break;
                    case 2:
                        dinosaurio.position = [45, 15, 8];
                        break;
                    case 3:
                        dinosaurio.position = [55, 15, 8];
                        break;
                    case 4:
                        dinosaurio.position = [66, 15, 8];
                        break;
                    case 5:
                        dinosaurio.position = [76, 15, 8];
                        break;
                    default:
                        break;
                }
                return "desigualdad";
            case (dinosaurio.position[0] >= 44 && dinosaurio.position[2] >= 34) && (dinosaurio.position[0] <= 85 && dinosaurio.position[2] <= 64):
                if (this.soledad.dinosaurios.length < this.soledad.cupos) {
                    dinosaurio.position = [66, 15, 52];
                } else {
                }
                return "soledad";
            case (dinosaurio.position[0] >= -18 && dinosaurio.position[2] >= 35) && (dinosaurio.position[0] <= 38 && dinosaurio.position[2] <= 96):
                switch (this.rio.dinosaurios.length) {
                    case 0:
                        dinosaurio.position = [-2, 15, 91];
                        break;
                    case 1:
                        dinosaurio.position = [13, 15, 91];
                        break;
                    case 2:
                        dinosaurio.position = [26, 15, 91];
                        break;
                    case 3:
                        dinosaurio.position = [-2, 15, 71];
                        break;
                    case 4:
                        dinosaurio.position = [13, 15, 71];
                        break;
                    case 5:
                        dinosaurio.position = [26, 15, 71];
                        break;
                    default:
                        break;
                }
                return "rio";
            default:
            break;
        }
    }

    agregarDinosaurio(dinosaurio, recinto) {
        if (this.tablero.getDinosaurioById(dinosaurio.id) <= 0) return false;

        switch (recinto) {
            case "igualdad":
                if (this.igualdad.dinosaurios.length < this.igualdad.cupos) {
                    if (this.igualdad.dinosaurios.length > 0) {
                        if (this.igualdad.dinosaurios[0].id == dinosaurio.id) {
                            this.igualdad.dinosaurios.push(dinosaurio);
                            return true;
                        }
                        else {
                            alert("No puede colocar Dinosaurios en este recinto");
                            return false;
                        }
                    } else {
                        this.igualdad.dinosaurios.push(dinosaurio);
                        return true;
                    }
                } else {
                    alert("No puede colocar Dinosaurios en este recinto");
                    return false;
                }
            
            case "desigualdad":
                if (this.desigualdad.dinosaurios.length < this.desigualdad.cupos) {
                    for (let i = 0; i < this.desigualdad.dinosaurios.length; i++) {
                        const dinosaurioRecinto = this.desigualdad.dinosaurios[i];
                        if (dinosaurioRecinto.id == dinosaurio.id) {
                            alert("No puede colocar Dinosaurios en este recinto");
                            return false;
                        }
                    }
                    this.desigualdad.dinosaurios.push(dinosaurio);
                    return true;
                } else {
                    alert("No puede colocar Dinosaurios en este recinto");
                    return false;
                }
            
            case "romance":
                if (this.romance.dinosaurios.length < this.romance.cupos) {
                    this.romance.dinosaurios.push(dinosaurio);
                    return true;
                } else {
                    alert("No puede colocar Dinosaurios en este recinto");
                    return false;
                }
            
            case "soledad":
                if (this.soledad.dinosaurios.length < this.soledad.cupos) {
                    this.soledad.dinosaurios.push(dinosaurio);
                    return true;
                } else {
                    alert("No puede colocar Dinosaurios en este recinto");
                    return false;
                }
            
            case "monarquia":
                if (this.monarquia.dinosaurios.length < this.monarquia.cupos) {
                    this.monarquia.dinosaurios.push(dinosaurio);
                    return true;
                } else {
                    alert("No puede colocar Dinosaurios en este recinto");
                    return false;
                }
            
            case "tres":
                if (this.tres.dinosaurios.length < this.tres.cupos) {
                    this.tres.dinosaurios.push(dinosaurio);
                    return true;
                } else {
                    alert("No puede colocar Dinosaurios en este recinto");
                    return false;
                }
            
            case "rio":
                if (this.rio.dinosaurios.length < this.rio.cupos) {
                    this.rio.dinosaurios.push(dinosaurio);
                    return true;
                } else {
                    alert("No puede colocar Dinosaurios en este recinto");
                    return false;
                }
            
            default:
                return false;
        }
    }

    colocarDinosaurioRecinto(dinosaurio, recinto) {
        switch (recinto) {
            case "igualdad":
                switch (this.igualdad.dinosaurios.length) {
                    case 0:
                        dinosaurio.position = [-78, 15, -60];
                        break;
                    case 1:
                        dinosaurio.position = [-70, 15, -60];
                        break;
                    case 2:
                        dinosaurio.position = [-59, 15, -60];
                        break;
                    case 3:
                        dinosaurio.position = [-49, 15, -60];
                        break;
                    case 4:
                        dinosaurio.position = [-38, 15, -60];
                        break;
                    case 5:
                        dinosaurio.position = [-29, 15, -60];
                        break;
                    default:
                        break;
                }
                break;
            case "tres":
                switch (this.tres.dinosaurios.length) {
                    case 0:
                        dinosaurio.position = [-70, 15, 8];
                        break;
                    case 1:
                        dinosaurio.position = [-62, 15, -7];
                        break;
                    case 2:
                        dinosaurio.position = [-53, 15, 9];
                        break;
                    default:
                        break;
                }
                break;
            case "romance":
                console.log(this.romance.dinosaurios.length)
                switch (this.romance.dinosaurios.length) {
                    case 0:
                        dinosaurio.position = [-62, 15, 68];
                        console.log(86728)
                        break;
                    case 1:
                        dinosaurio.position = [-52, 15, 68];
                        break;
                    case 2:
                        dinosaurio.position = [-42, 15, 68];
                        break;
                    case 3:
                        dinosaurio.position = [-62, 15, 48];
                        break;
                    case 4:
                        dinosaurio.position = [-52, 15, 48];
                        break;
                    case 5:
                        dinosaurio.position = [-42, 15, 48];
                        break;
                    default:
                        break;
                }
                break;
            case "monarquia":
                if (this.monarquia.dinosaurios.length < this.monarquia.cupos) {
                    dinosaurio.position = [44, 15, -62];
                }
                break;
            case "desigualdad":
                switch (this.desigualdad.dinosaurios.length) {
                    case 0:
                        dinosaurio.position = [24, 15, 8];
                        break;
                    case 1:
                        dinosaurio.position = [33, 15, 8];
                        break;
                    case 2:
                        dinosaurio.position = [45, 15, 8];
                        break;
                    case 3:
                        dinosaurio.position = [55, 15, 8];
                        break;
                    case 4:
                        dinosaurio.position = [66, 15, 8];
                        break;
                    case 5:
                        dinosaurio.position = [76, 15, 8];
                        break;
                    default:
                        break;
                }
                break;
            case "soledad":
                if (this.soledad.dinosaurios.length < this.soledad.cupos) {
                    dinosaurio.position = [66, 15, 52];
                }
                break;
            case "rio":
                switch (this.rio.dinosaurios.length) {
                    case 0:
                        dinosaurio.position = [-2, 15, 91];
                        break;
                    case 1:
                        dinosaurio.position = [13, 15, 91];
                        break;
                    case 2:
                        dinosaurio.position = [26, 15, 91];
                        break;
                    case 3:
                        dinosaurio.position = [-2, 15, 71];
                        break;
                    case 4:
                        dinosaurio.position = [13, 15, 71];
                        break;
                    case 5:
                        dinosaurio.position = [26, 15, 71];
                        break;
                    default:
                        break;
                }
                break;
        }
    }
}