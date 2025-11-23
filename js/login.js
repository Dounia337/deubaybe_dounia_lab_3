
const form = document.querySelector("form");

form.addEventListener("submit", function(event) {
    const email = document.getElementById("email");
    const password = document.getElementById("password");

    const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-z]{2,}$/;
    const passwordPattern = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/;

    if (!emailPattern.test(email.value)) {
        alert("Please enter a valid email address.");
        event.preventDefault();
        return;
    }

    if (!passwordPattern.test(password.value)) {
        alert("Password must contain one uppercase, one lowercase, one number, and be at least 8 characters long.");
        event.preventDefault();
        return;
    }
});