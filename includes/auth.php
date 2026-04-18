<?php
require_once __DIR__ . '/db.php';

function loginUser(string $email, string $password): array {
    try {
        $db = getDB();
        // On vérifie le nom de la colonne email_utilisateur
        $stmt = $db->prepare("SELECT * FROM Utilisateur WHERE email_utilisateur = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            $role = 'nouveau';
            
            // On vérifie les colonnes de rôles
            if (isset($user['est_admin']) && $user['est_admin'] == 1) {
                $role = 'admin';
            } elseif (isset($user['est_prestataire']) && $user['est_prestataire'] == 1) {
                $role = 'prestataire';
            } elseif (isset($user['est_client']) && $user['est_client'] == 1) {
                $role = 'client';
            }

            $_SESSION['user_id'] = $user['id_utilisateur'];
            $_SESSION['role']    = $role;
            $_SESSION['user']    = $user;

            return ['success' => true, 'role' => $role];
        }
        return ['success' => false, 'message' => 'Identifiants incorrects.'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
    }
}

/**
 * Pour l'inscription (Vérifie aussi les noms de colonnes ici)
 */
function registerUser(array $data): array {
    try {
        $db = getDB();
        
        // Vérifier si l'email existe déjà
        $check = $db->prepare("SELECT id_utilisateur FROM Utilisateur WHERE email_utilisateur = ?");
        $check->execute([$data['email']]);
        if ($check->fetch()) {
            return ['success' => false, 'message' => 'Cet email est déjà utilisé.'];
        }

        $sql = "INSERT INTO Utilisateur (nom_utilisateur, prenom_utilisateur, email_utilisateur, mot_de_passe, num_utilisateur, id_quartier) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $data['nom'],
            $data['prenom'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['telephone'],
            $data['id_quartier']
        ]);

        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
} 
function setUserRole($role) {
    if ($role === 'client' || $role === 'prestataire') {
        $_SESSION['user_role'] = $role;
        return true;
    }
    return false;
}
