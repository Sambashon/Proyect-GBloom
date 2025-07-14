const registerBtn = document.getElementById("registerBtn");
const loginBtn = document.getElementById("loginBtn");
const playwithoutaccBtn = document.getElementById("playWithoutBtn");


xOut.addEventListener("click", () => {
  window.location = "../../homePage/home.html";
})

registerBtn.addEventListener("click", () =>{
        window.location = "../register/register.html";
})

loginBtn.addEventListener("click", () =>{
        window.location = "../login/login.html";
})

playwithoutaccBtn.addEventListener("click", () =>{
    window.location = "../../homepage/home.html";
})
