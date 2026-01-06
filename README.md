# ⌨️ Advanced Typing Practice Platform

A comprehensive, full-featured web application designed to help users improve their typing speed and accuracy in multiple languages (English, Hindi Kruti Dev 010, Hindi Inscript). Built with modern PHP, MySQL, and JavaScript.

![Typing Test](https://via.placeholder.com/800x400?text=Typing+Website+Preview)

## ✨ Key Features

### 🚀 For Users
- **Multi-Language Support**: Practice in English, Hindi (Kruti Dev), and Hindi (Inscript/Mangal).
- **Advanced Typing Tests**: 
  - Real-time WPM, CPM, and Accuracy tracking.
  - Custom time limits (1, 2, 5, 10, 15, 20 minutes).
  - Exam modes simulating real government exams (SSC, RRB, CPCT).
- **Detailed Analytics**:
  - Breakdown of Full Mistakes vs. Half Mistakes.
  - Typed content analysis with error highlighting.
  - Historical performance tracking with graphs.
- **Customization**:
  - Upload your own paragraphs for practice.
  - Choose from random words, database paragraphs, or admin-curated content.
  - Toggle backspace and error highlighting.
- **Certification**: Generate and download certificates for completed tests.
- **Responsive Design**: Works seamlessly on desktops, tablets, and mobile devices.

### 🛠️ For Admins
- **Dashboard**: Overview of user statistics and system health.
- **Content Management**: 
  - Add, edit, and delete public paragraphs.
  - Categorize content by language and difficulty.
- **User Management**: Monitor user activity and results.

## 🏗️ Tech Stack

- **Backend**: PHP 8.x (PDO for database security)
- **Frontend**: HTML5, CSS3 (Bootstrap 5), JavaScript (Vanilla)
- **Database**: MySQL / MariaDB
- **Server**: Apache (XAMPP/WAMP/LAMP compatible)

## ⚙️ Installation

1.  **Clone the Repository**
    ```bash
    git clone https://github.com/Akshanshu-kumar/typing-website.git
    cd typing-website
    ```

2.  **Database Setup**
    - Create a new MySQL database named `typing_practice_db`.
    - Import the `sql/schema.sql` file to create necessary tables.
    - Run `update_schema_v4.php` to ensure all latest tables (like `user_paragraphs`, `default_paragraphs`) are created.

3.  **Configuration**
    - Open `config/db.php` and configure your database credentials:
      ```php
      $host = 'localhost';
      $dbname = 'typing_practice_db';
      $username = 'root';
      $password = '';
      ```

4.  **Run the Application**
    - Place the project folder in your web server's root directory (e.g., `htdocs` for XAMPP).
    - Access the application via browser: `http://localhost/typingWebsite`

## 📂 Project Structure

```
typingWebsite/
├── admin/              # Admin panel files
├── api/                # AJAX endpoints (get_paragraph, save_result)
├── assets/             # CSS, JS, Images, Fonts
├── config/             # Database connection and global config
├── includes/           # Header, footer, and reusable components
├── sql/                # Database schema files
├── index.php           # Landing page
├── typing-test.php     # Main typing test interface
├── test-settings.php   # Test configuration page
├── result.php          # Test results and analysis
├── history.php         # User test history
├── certificate.php     # Certificate generation
└── custom-upload.php   # User paragraph upload
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1.  Fork the project
2.  Create your feature branch (`git checkout -b feature/AmazingFeature`)
3.  Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4.  Push to the branch (`git push origin feature/AmazingFeature`)
5.  Open a Pull Request

## 📄 License

This project is open-source and available under the [MIT License](LICENSE).

---

Developed with ❤️ for typing enthusiasts.
