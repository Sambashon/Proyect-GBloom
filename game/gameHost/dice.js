const dice = document.getElementById("dice");
const roll = document.getElementById("diceBtn");

const diceFaces = {
    face1: "../../Resources/dado/dado1.svg",
    face2: "../../Resources/dado/dado2.svg",
    face3: "../../Resources/dado/dado3.svg",
    face4: "../../Resources/dado/dado4.svg",
    face5: "../../Resources/dado/dado5.svg",
    face6: "../../Resources/dado/dado6.svg",
}

roll.addEventListener("click",() =>{
    const res = Math.floor(Math.random() * 6) + 1;
    console.log(res);

    dice.src =  diceFaces[`face${res}`];
})