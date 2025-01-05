# Game Pong Project

A simple web-based Pong game with user authentication and profile management. This project allows users to log in, play the game, update their profile, and delete their account.

## Features

- **User Authentication**: Users can log in with their email and password.
- **Game**: After logging in, users can play the Pong game.
- **Profile Management**: Users can update their profile information (name and password).
- **Account Deletion**: Users can delete their accounts.
- **Session Management**: Once logged in, users can access their profile and the game without needing to log in again until they log out.

## Project Structure

The project consists of the following main files:

- **`index.php`**: The landing page and Login page where users authenticate with their email and password.
- **`register.php`**: Registration page where users create an account.
- **`main.php`**: The main dashboard page after the user logs in, displaying their email and profile.
- **`profile.php`**: Page where users can update their profile details (name and password) and users can delete their account.
- **`logout.php`**: Logs the user out and redirects them to the index page(login page).
- **`db.php`**: Database connection file used across all pages to manage the connection to the MySQL database.

## Requirements

- PHP 7 or later
- MySQL database
- A web server (Apache or Nginx) with PHP support

## Setup

Follow these steps to get your environment running:

### 1. Clone the repository

```bash
git clone https://github.com/Fakhrul-Hakimy/pong.git
cd pong
