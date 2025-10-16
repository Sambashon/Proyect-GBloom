let isHost;
let language;
let gameres;

language = localStorage.getItem("language") || "ENGLISH";
gameres = localStorage.getItem("gameres") || "MEDIUM";

const languageDropdown = document.querySelectorAll(".dropdown-item#language");
const gameresDropdown = document.querySelectorAll(".dropdown-item#gameres");
const leaveBtn = document.querySelector("#LEAVEBtn");

setInterval(partidaOver, 1000);

document.addEventListener("DOMContentLoaded", async () => {
  await getHost();

  if (isHost === true) {
    leaveBtn.removeAttribute('data-bs-toggle');
    leaveBtn.removeAttribute('data-bs-target');

    document.querySelector("#backBtn").addEventListener("click", async () =>{
      const action = await fetch("/php/scripts/game/partida/terminarPartida.php", {method: "POST"}).then(function (response) {
        return response.json();
      });

      window.location.href = "/homePage/home.html";
    });
    
  } else {
    leaveBtn.addEventListener("click", () =>{
      window.location.href = "/homePage/home.html";
    });
  }
})

languageDropdown.forEach(item => {
    const dropdown = item.closest(".dropdown");
    const button = dropdown.querySelector(".dropdown-toggle");
    button.innerText = language;

    item.addEventListener("click", () => {
        button.innerText = item.innerText;
        language = item.innerText;
        localStorage.setItem("language", language);
    });
});

gameresDropdown.forEach(item => {
    const dropdown = item.closest(".dropdown");
    const button = dropdown.querySelector(".dropdown-toggle");
    button.innerText = gameres;

    item.addEventListener("click", () => {
        button.innerText = item.innerText;
        gameres = item.innerText;
        localStorage.setItem("gameres", gameres);
    });
});

const sfxSlider = document.getElementById("sfxSlider");
const sfxValue = document.getElementById("sfxValue");
sfxSlider.value = localStorage.getItem("sfx") || "100";
sfxValue.textContent = localStorage.getItem("sfx") || "100";
sfxSlider.addEventListener("input", (e) =>{
    const volume = e.target.value;
    sfxValue.textContent = volume;
    radio.setEffectsVolume(parseInt(volume));
    localStorage.setItem("sfx", volume);
});
radio.setEffectsVolume(parseInt(localStorage.getItem("sfx") || "100"));

const mscSlider = document.getElementById("mscSlider");
const mscValue = document.getElementById("mscValue");
mscSlider.value = localStorage.getItem("music") || "100";
mscValue.textContent = localStorage.getItem("music") || "100";
mscSlider.addEventListener("input", (e) =>{
    const volume = e.target.value;
    mscValue.textContent = volume;
    radio.setMusicVolume(parseInt(volume));
    localStorage.setItem("music", volume);
})
radio.setMusicVolume(parseInt(localStorage.getItem("music") || "100"));

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

async function partidaOver() {
  const response = await fetch("/php/scripts/game/getters/isPartidaOver.php").then(function (response) {
    return response.json();
  });

  if (response.status !== "success" || response.result) {
    window.location.href = "/homePage/home.html";
  }
}