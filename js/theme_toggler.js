const sideMenu = document.querySelector("aside");
const menuBtn = document.querySelector("#menu-btn");
const closeBtn = document.querySelector("#close-btn");
const themeToggler = document.querySelector(".theme-toggler");


menuBtn.addEventListener("click", () => {
    sideMenu.style.display = "block";
});


closeBtn.addEventListener("click", () => {
    sideMenu.style.display = "none";
});


document.addEventListener("DOMContentLoaded", () => {
    if (localStorage.getItem("theme") === "dark") {
        document.body.classList.add("dark-theme-variables");
        themeToggler.querySelector("span:nth-child(1)").classList.remove("active");
        themeToggler.querySelector("span:nth-child(2)").classList.add("active");
    }
});


themeToggler.addEventListener("click", () => {
    document.body.classList.toggle("dark-theme-variables");

    // Guardar el estado en localStorage
    if (document.body.classList.contains("dark-theme-variables")) {
        localStorage.setItem("theme", "dark");
    } else {
        localStorage.setItem("theme", "light");
    }

    themeToggler.querySelector("span:nth-child(1)").classList.toggle("active");
    themeToggler.querySelector("span:nth-child(2)").classList.toggle("active");
});
