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
})

backBtn.addEventListener("click", () =>{
    console.log("waza")
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

startBtn.addEventListener("click", () =>{
    if(virtualSelected){
        window.location = "lobby.html";
    }else if(syncSelected){
        alert("Page under construction...");
    }
})