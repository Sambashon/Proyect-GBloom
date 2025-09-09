const xOut = document.querySelectorAll("#xOut");

xOut.forEach(button => {
  button.addEventListener("click", () => {
    if (!window.location.href.includes("confirmation.html")) {
      window.location.href = "../homePage/home.html";
    }
  });
});

