# 🧠 Mental Health Web Platform

A responsive and animated full-stack web platform for mental health support, built with **PHP**, **Tailwind CSS**, and **JavaScript**. It features a smooth UI, user-friendly navigation, and animation effects like shake feedback on form validation.

---

## ✨ Features

- 👤 **User Registration & Login**
- 📊 **Dashboard** to track activities
- 📚 **Articles and Resources** section
- 🌗 **Dark Mode Interface**
- 💥 **Shake Animation** on form error
- 🧘‍♀️ Calm, friendly and modern UI
- 🔐 **Session-based Authentication**

---

## 🛠️ Tech Stack

- **Frontend**: HTML5, Tailwind CSS, JavaScript
- **Backend**: PHP
- **Styling Tools**: Tailwind + custom CSS
- **Animation**: CSS keyframes + JS trigger
- **Package Management**: Node.js, npm

---

## 📁 Folder Structure

Project/
├── package.json
├── package-lock.json
├── tailwind.config.js
├── html/
│ ├── activity.php
│ ├── articles.php
│ ├── dash.php
│ ├── db_connection.php
│ ├── landing.php
│ ├── login.php
│ ├── logout.php
│ ├── main.php
│ ├── resources.php
│ ├── signup.php
├── img/
│ ├── signup.png
│ ├── logo.png
│ ├── firstpage.jpg
│ └── (various icons and topic illustrations)
├── node_modules/
│ └── (Tailwind CSS and supporting packages)


---

## 🎬 Shake Animation (Error Feedback)

### CSS:

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  20%, 60% { transform: translateX(-10px); }
  40%, 80% { transform: translateX(10px); }
}

.shake {
  animation: shake 0.5s;
}

JavaScript:
js
Copy
Edit
form.classList.add("shake");
setTimeout(() => form.classList.remove("shake"), 500);

⚙️ Setup Instructions
📦 Install dependencies:

bash
Copy
Edit
npm install

🧵 Compile Tailwind CSS:

bash
Copy
Edit
npx tailwindcss -i ./input.css -o ./output.css --watch

💻 Start local PHP server:

bash
Copy
Edit
php -S localhost:8000 -t html

🌐 Open your browser at:
http://localhost:8000

🔮 Future Improvements
🔒 Add password hashing and user authentication via database

📱 Improve responsiveness for mobile

🌐 Add localization/multilingual support

🧩 Add API integration for mood tracking or chatbot

🙋‍♂️ Author & Contact
Developed by Your Name
📧 your-shiwammaxx@gmail.com
🐙 GitHub: shivam-37


