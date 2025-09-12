document.querySelectorAll(".dropdown-item").forEach(item => {
  item.addEventListener("click", () => {
    const dropdown = item.closest(".dropdown");
    const button = dropdown.querySelector(".dropdown-toggle");
    button.innerText = item.innerText;
    console.log("Selected:", item.innerText);
  });
});
const leaveBtn = document.getElementById("LEAVEBtn");
leaveBtn.addEventListener("click", () =>{
  console.log()
  window.location.href = "../../homePage/home.html";
})
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