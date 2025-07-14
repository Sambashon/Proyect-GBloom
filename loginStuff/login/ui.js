const xOut = document.querySelectorAll("#xOut");

xOut.forEach(button => {
  button.addEventListener("click", () => {
    if (!window.location.href.includes("confirmation.html")) {
      window.location = "../confirmation/confirmation.html";
    }else{
      window.location = "../../homePage/home.html";
    }
  });
});


const doneBtn = document.querySelector("#doneBtn");
doneBtn.addEventListener("click", () =>{
    window.location = "../../homePage/home.html";
})