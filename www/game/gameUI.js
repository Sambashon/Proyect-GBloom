let isHost;

document.addEventListener("DOMContentLoaded", async () => {
  await getHost();

  if (isHost === true) {
    leaveBtn.removeAttribute('data-bs-toggle');
    leaveBtn.removeAttribute('data-bs-target');

    document.querySelector("#backBtn").addEventListener("click", async () =>{
      const action = await fetch("/php/scripts/game/partida/terminarPartida.php", {method: "POST"}).then(function (response) {
        return response.json();
      });

      window.location.href = "../../homePage/home.html";
    });
    
  } else {
    leaveBtn.addEventListener("click", () =>{
      window.location.href = "../../homePage/home.html";
    });
  }
})

document.querySelectorAll(".dropdown-item").forEach(item => {
  item.addEventListener("click", () => {
    const dropdown = item.closest(".dropdown");
    const button = dropdown.querySelector(".dropdown-toggle");
    button.innerText = item.innerText;
    console.log("Selected:", item.innerText);
  });
});
const leaveBtn = document.getElementById("LEAVEBtn");
const volumeSlider = document.getElementById("volSlider");
const volValue = document.getElementById("volValue");
volumeSlider.addEventListener("input", (e) =>{
    const volume = e.target.value;
    volValue.textContent = volume;
})
const sfxSlider = document.getElementById("sfxSlider");
const sfxValue = document.getElementById("sfxValue");
sfxSlider.addEventListener("input", (e) =>{
    const volume = e.target.value;
    sfxValue.textContent = volume;
})
const mscSlider = document.getElementById("mscSlider");
const mscValue = document.getElementById("mscValue");
mscSlider.addEventListener("input", (e) =>{
    const volume = e.target.value;
    mscValue.textContent = volume;
})

async function getHost() {
  const response = await fetch("/php/scripts/game/getters/isHost.php").then(function (response) {
    return response.json();
  });

  if (response.status == "success") {
    isHost = true;
  } else {
    isHost = false;
  }
}