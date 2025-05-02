# Animated Sign-Up Form with Shake Effect

This project is a clean and visually appealing Sign-Up form that includes a shake animation when input validation fails. It is designed to provide users with a calming and friendly interface, suitable for platforms related to mental health, wellness, or any modern web application.

---

## Features

- Modern and responsive sign-up form
- Input validation with shake animation on errors
- Dark mode UI styling
- Fields included: Full Name, Email, Phone Number, Password, Confirm Password
- Smooth CSS animations
- Simple HTML, CSS (or Tailwind CSS), and JavaScript

---

## Technologies Used

- HTML5
- CSS3 (or Tailwind CSS)
- JavaScript (for form validation and animation triggering)

---

## How to Use

1. Download or clone the repository:
   git clone https://github.com/yourusername/animated-signup-form.git

2. Open the project folder:
   cd animated-signup-form

3. Launch the `index.html` file in your browser:
   You can double-click the file or open it using a local development server.

---

## How the Shake Animation Works

The shake effect is triggered when form validation fails (e.g., required fields are empty). It uses a CSS animation defined as:

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  20%, 60% { transform: translateX(-10px); }
  40%, 80% { transform: translateX(10px); }
}

A class named `.shake` is applied to the form or input container via JavaScript and removed after 500ms to show the shake effect only when necessary.

---

## Folder Structure

project/
├── index.html
├── style.css
├── script.js
├── assets/
│   └── images/
│       └── illustration.png
├── README.md

---

## To Do

- Add password strength and email validation
- Connect with backend to store user data
- Improve accessibility and mobile responsiveness

---

## License

This project is licensed under the MIT License.

---

## Contact

For questions or feedback, please contact: shiwammaxx@gamil.com

