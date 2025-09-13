const passwordInputs = document.querySelectorAll(".passwordInputs");

passwordInputs.forEach(input => {
    const togglePasswordBtn = document.createElement("button");
    togglePasswordBtn.type = "button"; // prevent form submit
    togglePasswordBtn.classList.add("custom-button", "icon");
    
    const eyeIcon = document.createElement("i");
    eyeIcon.classList.add("bi", "bi-eye");

    const parent = input.parentElement;
    parent.appendChild(togglePasswordBtn);
    togglePasswordBtn.appendChild(eyeIcon);

    togglePasswordBtn.addEventListener("click", () => {
        // Toggle the input type
        const type = input.getAttribute("type") === "password" ? "text" : "password";
        input.setAttribute("type", type);

        // Toggle the icon
        eyeIcon.classList.toggle("bi-eye");
        eyeIcon.classList.toggle("bi-eye-slash");
    });
});
