
const form = document.querySelector("form");

form.addEventListener("submit", function(event) {
    const firstName = document.getElementById("fname");
    const lastName = document.getElementById("lname");
    const email = document.getElementById("email");
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirm");

    const namePattern = /^[A-Za-z][A-Za-z0-9_ ]*$/;
    const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-z]{2,}$/;
    const passwordPattern = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/;

    console.log("Password validation:", password.value, passwordPattern.test(password.value));

    if (!namePattern.test(firstName.value)) {
        alert("Invalid First Name. Name must start with a letter and can contain letters, numbers, spaces or underscores only.");
        event.preventDefault();
        return;
    }

    if (!namePattern.test(lastName.value)) {
        alert("Invalid Last Name. Name must start with a letter and can contain letters, numbers, spaces or underscores only.");
        event.preventDefault();
        return;
    }

    if (!emailPattern.test(email.value)) {
        alert("Please enter a valid email address.");
        event.preventDefault();
        return;
    }

    if (!passwordPattern.test(password.value)) {
        alert("Password must contain at least one uppercase letter, one lowercase letter, one number, and be at least 8 characters long.");
        event.preventDefault();
        return;
    }

    if (password.value !== confirmPassword.value) {
        alert("Passwords do not match.");
        event.preventDefault();
        return;
    }
});