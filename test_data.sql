-- Données de test pour le projet

-- Membres (avec mots de passe hashés pour 'admin' et 'user')
INSERT INTO membres (nom, prenom, email, poste, grade, mot_de_passe, role, actif) VALUES
('Admin', 'Système', 'admin@esi.dz', 'Administrateur', 'Professeur', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1),
('Utilisateur', 'Test', 'user@esi.dz', 'Doctorant', 'Doctorant', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'membre', 1),
('Benali', 'Ahmed', 'a.benali@esi.dz', 'Directeur de laboratoire', 'Professeur', NULL, 'membre', 1),
('Saidi', 'Fatima', 'f.saidi@esi.dz', 'Chef d\'équipe IA', 'Maître de conférences A', NULL, 'membre', 1),
('Meziane', 'Karim', 'k.meziane@esi.dz', 'Chef d\'équipe Sécurité', 'Professeur', NULL, 'membre', 1),
('Hamdi', 'Leila', 'l.hamdi@esi.dz', 'Enseignant-chercheur', 'Maître de conférences B', NULL, 'membre', 1),
('Belkacem', 'Yacine', 'y.belkacem@esi.dz', 'Doctorant', 'Doctorant', NULL, 'membre', 1),
('Mansouri', 'Sarah', 's.mansouri@esi.dz', 'Doctorante', 'Doctorante', NULL, 'membre', 1);

-- Statuts de projet
INSERT INTO statuts_projet (nom_statut) VALUES
('En cours'),
('Terminé'),
('Soumis'),
('En attente');

-- Projets
INSERT INTO projets (titre, description, objectifs, date_debut, date_fin, id_thematique, id_status, id_type_financement, responsable_id, budget) VALUES
('Système de détection d\'intrusions par IA', 
 'Développement d\'un système intelligent de détection d\'intrusions utilisant des algorithmes de deep learning pour analyser le trafic réseau en temps réel.',
 'Créer un système capable de détecter les anomalies et les menaces avec une précision supérieure à 95%. Intégrer des modèles de machine learning adaptatifs.',
 '2024-01-15', NULL, 1, 1, 1, 4, 2500000),

('Plateforme Cloud pour l\'éducation', 
 'Conception et développement d\'une plateforme cloud sécurisée pour l\'enseignement à distance avec des fonctionnalités avancées de collaboration.',
 'Offrir une solution scalable supportant 10000+ utilisateurs simultanés. Garantir la sécurité des données et l\'accessibilité.',
 '2023-09-01', '2025-08-31', 3, 1, 2, 3, 5000000),

('Optimisation des protocoles IoT', 
 'Recherche sur l\'optimisation des protocoles de communication pour les réseaux IoT à faible consommation d\'énergie.',
 'Réduire la consommation énergétique de 40% tout en maintenant la fiabilité. Proposer un nouveau protocole optimisé.',
 '2023-03-01', '2024-12-31', 4, 2, 3, 6, 1800000),

('Analyse de sentiment sur les réseaux sociaux', 
 'Développement d\'outils d\'analyse de sentiment en arabe dialectal pour les réseaux sociaux utilisant le NLP.',
 'Créer un modèle capable d\'analyser l\'arabe dialectal avec une précision de 85%+. Publier un dataset annoté.',
 '2024-06-01', NULL, 1, 1, 4, 4, 1200000),

('Système embarqué pour la domotique', 
 'Conception d\'un système embarqué intelligent pour la gestion automatisée des appareils domestiques.',
 'Développer un système low-cost compatible avec les standards existants. Réduire la consommation énergétique de 30%.',
 '2022-01-10', '2024-06-30', 5, 2, 1, 5, 900000);

-- Membres des projets
INSERT INTO projet_membres (id_projet, id_membre, role_projet, date_debut, date_fin) VALUES
-- Projet 1
(1, 4, 'Responsable', '2024-01-15', NULL),
(1, 7, 'Doctorant', '2024-01-15', NULL),
(1, 8, 'Doctorante', '2024-02-01', NULL),
-- Projet 2
(2, 3, 'Responsable', '2023-09-01', NULL),
(2, 6, 'Co-encadrant', '2023-09-01', NULL),
(2, 7, 'Développeur', '2023-10-01', NULL),
-- Projet 3
(3, 6, 'Responsable', '2023-03-01', '2024-12-31'),
(3, 8, 'Doctorante', '2023-03-01', '2024-12-31'),
-- Projet 4
(4, 4, 'Responsable', '2024-06-01', NULL),
(4, 7, 'Doctorant', '2024-06-01', NULL),
-- Projet 5
(5, 5, 'Responsable', '2022-01-10', '2024-06-30'),
(5, 8, 'Doctorante', '2022-01-10', '2024-06-30');

-- Publications
INSERT INTO publications (titre, auteurs, annee, type, doi, resume, id_projet) VALUES
('Deep Learning for Intrusion Detection in IoT Networks',
 'F. Saidi, Y. Belkacem, A. Benali',
 2024,
 'article',
 '10.1234/iot.2024.001',
 'This paper presents a novel approach to intrusion detection in IoT networks using deep learning techniques.',
 1),

('Cloud-Based Learning Management System: A Case Study',
 'A. Benali, L. Hamdi, Y. Belkacem',
 2024,
 'communication',
 NULL,
 'Présentation des résultats de notre plateforme cloud pour l\'éducation lors de la conférence ICCE 2024.',
 2),

('Energy-Efficient Protocol for IoT Devices',
 'L. Hamdi, S. Mansouri',
 2024,
 'article',
 '10.1234/iot.2024.045',
 'We propose a new energy-efficient protocol that reduces power consumption in IoT devices by 40%.',
 3),

('Sentiment Analysis in Algerian Dialectal Arabic',
 'F. Saidi, Y. Belkacem',
 2024,
 'rapport',
 NULL,
 'Rapport technique sur l\'analyse de sentiment en arabe dialectal algérien.',
 4);

-- Note: Le mot de passe pour 'admin' et 'user' est 'admin' et 'user' respectivement
-- Hash généré avec password_hash('admin', PASSWORD_DEFAULT) et password_hash('user', PASSWORD_DEFAULT)