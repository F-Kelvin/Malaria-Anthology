# Malaria Anthology

**Full-Stack PHP / MySQL Content Management Platform**

Malaria Anthology is a full-stack web application designed to organize and publish malaria-related information through research articles, case studies, and human experiences.

The platform features a public-facing content library where users can browse articles by category and content type, read individual articles, and search published content. Behind the public website is an authenticated administration system that allows authorized users to create, edit, publish, archive, and categorize articles.

I built the application using **PHP, MySQL/MariaDB, PDO, HTML5, and CSS3**, with a relational database structure connecting articles, categories, users, sources, and supporting content entities.

## Key Features

* Public article and category browsing
* Research, case study, and human experience content types
* Article search across titles, summaries, content, and categories
* Article draft, published, and archived states
* Admin/editor authentication
* Role-based access control
* User creation and account activation/deactivation
* Category management
* Password hashing and verification
* PDO prepared statements
* Server-side input validation
* Escaped HTML output
* Relational database design with foreign keys and indexes
* Responsive, component-based CSS styling

## Technical Highlights

A major part of the project was implementing the relationship between the public publishing system and the administrative backend. Content entered through the administration interface is stored in the database and becomes available to the public website according to its publication status.

The project also demonstrates practical backend security concepts including password hashing, session management, prepared SQL statements, role checks, CSRF protection, and server-side validation.

## Technologies

**PHP · MySQL/MariaDB · PDO · HTML5 · CSS3 · PHP Sessions**

## What This Project Demonstrates

This project demonstrates my ability to build a database-driven web application from the backend through to the user interface, including database modeling, CRUD functionality, authentication, authorization, dynamic content rendering, search, security practices, and responsive frontend styling.
