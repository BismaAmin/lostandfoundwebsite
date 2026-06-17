# Lost And Found Website
A web platform that helps people report, search, and recover lost items quickly and easily.


# 1. Objective:
The lost and found web system is designed to help the user report, search, and recover their lost belongings in an organized and efficient manner. It replaces manual and unstructured methods like notice boards, social media posts, and messaging groups with a centralized digital platform.

The platform should:
1.	Allow users to report lost items. 
2.	Allow users to report found items. 
3.	Help match lost items with found items. 
4.	Enable communication between users. 
5.	Maintain a searchable database. 
6.	Protect user privacy. 

# 2. System Users
The platform supports three main user roles:
1. Students: Report and search for lost/found items.
2. Staff: Assist in reporting and recovery process.
3. Admin: Manage users, posts, and overall system moderation.

# 3. Technology Stack
1. Frontend: HTML, CSS, JavaScript
2. Backend: PHP
3. Database: MySQL
4. Server Environment: Apache / XAMPP 

# 4. Project Phases

# Phase 1: Requirement Analysis

Identifying user needs:
- **Students:** Report and search lost/found items  
- **Staff:** Report items and assist in recovery process  
- **Admin:** System management and monitoring
  
System Features
- Report lost items  
- Report found items  
- Search and filter items  
- Claim and verification system  
- Admin control panel
  
Functional Requirements
- User login and authentication  
- Item posting (lost/found)  
- Search and filtering system  
- Claim management system  

Non-Functional Requirements
- Security (data protection and authentication)  
- Usability (simple and user-friendly interface)  
- Performance (fast search and response time)  
   
# Phase 2: System Design

# 1. ERD diagram

   ![ERD diagarm](https://github.com/BismaAmin/lostandfoundwebsite/blob/6c5f96d75eda4dcf195089fb8a714bfb54e317a3/lostandfounderd.drawio.png)
   
# 2. System architecture
  
   ![Architecture Design](https://github.com/BismaAmin/lostandfoundwebsite/blob/71b67d2bf46266354f32a2aba57c06ae045bc577/lostfoundarhidesign.drawio.png)
   
- **User Layer:** Students and staff register/login, report lost or found items, and submit claims.
- **Frontend Layer:** Web or mobile interface for reporting, searching, browsing, and claiming items.
- **Application Server:** Handles authentication, item management, claim processing, and status updates.
- **Database Layer:** Stores user accounts, lost items, found items, and claim records with approval status.

# Phase 3: Frontend Development

Building the user interface.

- Develop frontend using HTML, CSS, JavaScript  
- Ensure responsive design for desktop and mobile users  

### Pages:
- **Login/Register Page:** Allows users to create an account and log in securely.
- **Home Page:** Displays recently posted lost and found items.
- **Post Lost Item Page:** Users submit details of lost items (title, description, category, location, date, image).
- **Post Found Item Page:** Users report found items with relevant details.
- **Search Page:** Search and filter items by keywords, category, or location.
- **User Dashboard:** Shows user’s posted items, claims, and status updates.
- **Admin Dashboard:** Allows monitoring of all reports and managing users/items.

# Phase 4: Backend Development

The server-side logic will be implemented by the developer.
- Backend will be implemented using PHP  

### APIs:
- User authentication (login/register)
- Lost item management
- Found item management
- Search functionality
- Claim system

### Business Logic:
- Matching lost and found items
- Handling claim approval/rejection

### Security:
- Password encryption
- Session or JWT authentication
- Input validation

# Phase 5: Database Integration

The database will be connected to the backend.

- Create database using MySQL  
- Store and manage the user data, Lost items, Found items, Claims  
- Optimize queries for efficient searching and filtering 

# Phase 6: Testing

All system modules will be tested.

- Test authentication system  
- Test item posting functionality  
- Test search and filter features  
- Test claim system  
- Fix bugs and improve performance  

# Phase 7: Deployment

The system will be deployed to a live environment.

- Deploy frontend and backend on hosting platform  
- Connect database to live server  
- Perform final testing 

# Expected Outcome:

The system will provide a centralized platform for reporting and recovering lost items, improving efficiency and reducing manual effort. It will help users quickly find their belongings and manage lost/found reports in a structured way.
