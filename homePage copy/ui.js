const settingsbtn = document.getElementById("SETTINGSbtn");
const playbtn = document.getElementById("PLAYbtn");
const profilebtn = document.getElementById("PROFILEbtn");

const footer = document.querySelector("footer");
const footerheader = document.createElement("header");
footerheader.classList.add("footerheader");

const footertitle = document.createElement("h2");
footertitle.classList.add("footertitle");

const settings = document.querySelector(".footerSettings");
settings.classList.add("locked");


settingsbtn.addEventListener("click", () => {
    footer.classList.toggle("visible");
    settings.classList.toggle("locked");

});

profilebtn.addEventListener("click", () => {
    footer.classList.toggle("visible");

});
