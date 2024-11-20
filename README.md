projet_sprintdev
│
├── config/
│   ├── db.php                   # Fichier de configuration pour la connexion à la base de
├── public/
│   ├── index.php                # Point d'entrée principal de l'application
│   ├── logout.php               # pour la deconnexion
│   ├── styles.css               # Feuille de style principale
│   ├── images/                  # Dossier pour les images de l'application
│   └── uploads/                 # Dossier pour les fichiers téléchargés (ressources et devoirs)
├── src/
│   ├── controllers/
│   │   ├── CourseController.php # Contrôleur pour gérer les cours
│   │   ├── UserController.php   # Contrôleur pour gérer les utilisateurs
│   │   ├── AssignmentController.php # Contrôleur pour les devoirs
│   │   └── ForumController.php  # Contrôleur pour gérer les forums et les messages
│   │
│   ├── models/
│   │   ├── Course.php           # Modèle pour les cours
│   │   ├── User.php             # Modèle pour les utilisateurs
│   │   ├── Assignment.php       # Modèle pour les devoirs
│   │   └── Forum.php            # Modèle pour les forums
│   │
│   ├── views/
│   │   ├── courses/
│   │   │   ├── create.php       # Vue pour créer un cours
│   │   │   ├── list.php         # Vue pour lister les cours
│   │   │   └── create_module.php# Vue pour lister les devoirs
│   │   │   └── create_chapter.php# Vue pour lister les devoirs
│   │   ├── users/
│   │   │   ├── profile.php      	 
│   │   │   └── login.php        # Vue pour la connexion
│   │   ├── assignments/
│   │   │   ├── submit.php       # Vue pour soumettre un devoir
│   │   │   └── list.php         # Vue pour lister les devoirs
│   │   │   └── download.php     # Vue pour telecharger les fichiers precedemment uploades
│   │   │   └── feedback.php     # Vue pour qu'un user avec le role teacher donne un feedback
│   │   ├── discussion.php       # Vue pour une discussion du forum
├── app.log                      # Fichier de journalisation des erreurs et activités
├── qodana.yaml





sprintdevchat
│
├── Users
│   ├── user_id (INT, PRIMARY KEY, AUTO_INCREMENT)
│   ├── first_name (VARCHAR)
│   ├── last_name (VARCHAR)
│   ├── email (VARCHAR, UNIQUE)
│   ├── password (VARCHAR)
│   ├── role (ENUM: 'admin', 'teacher', 'student')
│   ├── created_at (TIMESTAMP)
│   ├── updated_at (TIMESTAMP)
│
├── Courses
│   ├── course_id (INT, PRIMARY KEY, AUTO_INCREMENT)
│   ├── title (VARCHAR)
│   ├── description (TEXT)
│   ├── created_at (TIMESTAMP)
│   ├── updated_at (TIMESTAMP)
│
├── Modules
│   ├── module_id (INT, PRIMARY KEY, AUTO_INCREMENT)
│   ├── course_id (INT, FOREIGN KEY REFERENCES Courses(course_id))
│   ├── title (VARCHAR)
│   ├── description (TEXT)
│   ├── created_at (TIMESTAMP)
│   ├── updated_at (TIMESTAMP)
│
├── Chapters
│   ├── chapter_id (INT, PRIMARY KEY, AUTO_INCREMENT)
│   ├── module_id (INT, FOREIGN KEY REFERENCES Modules(module_id))
│   ├── title (VARCHAR)
│   ├── content (TEXT)
│   ├── created_at (TIMESTAMP)
│   ├── updated_at (TIMESTAMP)
│
├── Assignments
│   ├── assignment_id (INT, PRIMARY KEY, AUTO_INCREMENT)
│   ├── title (VARCHAR)
│   ├── description (TEXT)
│   ├── course_id (INT, FOREIGN KEY REFERENCES Courses(course_id))
│   ├── due_date (DATE)
│   ├── file_path (VARCHAR)
│   ├── created_at (TIMESTAMP)
│   ├── updated_at (TIMESTAMP)
│
├── Submissions
│   ├── submission_id (INT, PRIMARY KEY, AUTO_INCREMENT)
│   ├── assignment_id (INT, FOREIGN KEY REFERENCES Assignments(assignment_id))
│   ├── user_id (INT, FOREIGN KEY REFERENCES Users(user_id))
│   ├── file_path (VARCHAR)
│   ├── submission_date (TIMESTAMP)
│   ├── grade (DECIMAL)
│   ├── feedback (TEXT)
│
├── Messages
│   ├── message_id (INT, PRIMARY KEY, AUTO_INCREMENT)
│   ├── user_id (INT, FOREIGN KEY REFERENCES Users(user_id))
│   ├── content (TEXT)
│   ├── created_at (TIMESTAMP)
│
├── Forums
│   ├── forum_id (INT, PRIMARY KEY, AUTO_INCREMENT)
│   ├── title (VARCHAR)
│   ├── description (TEXT)
│   ├── created_at (TIMESTAMP)
│   ├── updated_at (TIMESTAMP)
│
├── ForumPosts
│   ├── post_id (INT, PRIMARY KEY, AUTO_INCREMENT)
│   ├── forum_id (INT, FOREIGN KEY REFERENCES Forums(forum_id))
│   ├── user_id (INT, FOREIGN KEY REFERENCES Users(user_id))
│   ├── content (TEXT)
│   ├── created_at (TIMESTAMP)





on utilise du java pour conserver ouvert l affichae des modules au sein d un cours
En effet pour eviter une double requete au rechargement de la page, on redirige vers la meme page avec $_SERVER['REQUEST_URI'].
Cela permet d'éliminer les données POST et de recharger la page proprement.

