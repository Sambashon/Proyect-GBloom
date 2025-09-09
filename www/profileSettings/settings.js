let changesSaved = false;
const saveBtn = document.getElementById("saveSubmit");
const leaveBtn = document.getElementById("leaveBtn");
const unsavedModalEl = document.getElementById("unsavedModal");
const unsavedModal = new bootstrap.Modal(unsavedModalEl);

saveBtn.addEventListener("click", (e) =>{
    e.preventDefault();
    alert("Changes saved!");
    changesSaved = true;
})
leaveBtn.addEventListener("click", () => {
    if (!changesSaved) {
        unsavedModal.show();
        
    } else {
        console.log("No unsaved changes, proceed with leaving.");
        window.location.href = '../homePage/home.html';
    }
});

const form = document.querySelector("form");
const inputs = form.querySelectorAll("input, textarea");
    inputs.forEach(input => {
    const parent = input.parentElement;
    const editBtn = parent.querySelector("button");
    input.addEventListener("keydown", (e) =>{
        if(e.key === "Enter"){
            e.preventDefault();
        }
    })
    if(!editBtn) return;
    editBtn.addEventListener("click", (e) =>{
        e.preventDefault();
        input.focus();
    })
});

const modalleaveBtn = document.getElementById("modalleaveBtn");
modalleaveBtn.addEventListener("click", () =>{
    window.location.href = '../homePage/home.html';
})
const logoutBtn = document.getElementById("logoutBtn");
logoutBtn.addEventListener("click", () =>{
    alert("Session closed!");
    window.location.href = '../homePage/home.html';
})