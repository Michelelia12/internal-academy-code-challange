# Code Challenge: The Internal Academy
Introduction
Hi! If you are reading this document, it means we have seen potential in you and would like to understand if you are the missing piece of our team.

We strongly believe in providing developers with the best tools and the right environment to do what they love: writing great software. For this reason, instead of the usual theoretical questions, we prefer to give you a practical challenge that will help us understand how you think "in the field."

# The Scenario
Our CEO walked into the "Dev Room" this morning yelling that we are wasting knowledge. "Everyone in here knows a lot of things, but no one teaches them to others!"

He has decided to found the Internal Academy, a place where employees can hold technical (and non-technical) workshops for their colleagues. The problem? The platform to manage it is missing. It's up to you to develop it.

# Functional Requirements
The goal is to develop a web application to manage company workshops and registrations.

## Technological Stack:
* Backend: Laravel (latest stable version)
* Frontend: Vue.js
* Integration: Inertia.js (highly recommended)
* Database: MySQL or SQLite


# The basic functionalities ("Must Have") are:
- [ ] **Roles and Authentication**
Implement two distinct roles: Admin (HR/Manager) and Employee (Developer/Generic User).
Users must be able to log in and see different interfaces based on their role.
- [ ] **Workshop Management (Admin)**
The Admin can create, modify, and delete Workshops.
- [ ] **Each Workshop**
must have:
- [ ] Title
- [ ] Description
- [ ] Date and Time
- [ ] Maximum Number of Seats (Capacity)
- [ ] **Registration and Participation (Employee)**
- [ ] All employees can view the list of future workshops on their dashboard.
- [ ] An employee can sign up for a workshop with one click, but only if there are still available seats.
- [ ] An employee can cancel their registration if they change their mind, immediately freeing up the spot for someone else.

# Nice to have
Too easy? Show off your skills
If the basic requirements seem too simple, here's how you can impress us by managing more realistic scenarios:
- [ ] **The Waiting List**: If a workshop is full, the user is not rejected but can sign up for the "Waiting List." If a confirmed participant cancels their registration, the first user on the waiting list is automatically promoted to participant (manage FIFO logic).
- [ ] **No Ubiquity**: Prevent a user from signing up for two workshops that overlap in time. No one can be in two places at once!
- [ ] **Command Line Reminder**: Create a custom artisan command (e.g., php artisan academy:remind) that, when launched, sends a reminder email to all participants of workshops scheduled for the following day.


# The Top Player Zone
If you're here, you really want to raise the bar.
- [ ] **Statistics Dashboard**:Implement a Statistics Dashboard for the Admin that shows:
- [ ] The most popular workshop.
- [ ] The number of registrations in Real Time: if a user signs up, the counter on the admin dashboard must update "magically" without refreshing the page (Websockets / Laravel Reverb / Polling).
- [ ] Furthermore, ensure the code is rock-solid by writing complete Unit Tests and Feature Tests (Pest or PHPUnit). Without tests, code is just an opinion.

# Non-Functional Requirements & Mindset
You will be working on a complex system, and we want to ensure we share the same mindset. For this reason:
* We work Agile with PHP and Vue.
* We love standard RESTful APIs and clean controllers.
* We make great use of Git: commit often and with clear messages, as if you were already on the team.
* We love README.md: clearly explain how to install the app, how to run the tests, and how to generate test data (Seeder).
* We hate unnecessary dependencies, we love package managers.

# Delivery
This challenge is designed to take approximately 2 days of work. Choose the right compromise between speed and quality, and make a note of the architectural decisions you make: we will discuss them together.

- Start by forking a clean Laravel repo.
- Develop your solution.
- Send us the link to your repo when you are ready!
- We will review the code, and one of our Devs will contact you for a joint Code Review.

Good luck and happy coding!
The Dev Team
