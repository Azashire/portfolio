<!DOCTYPE html>
<html lang="fr">
<?php include "head.php" ?>


<body>
<?php include "header.php" ?>
<?php include "navbar.php" ?>

<?php

session_start();
if (!empty($_SESSION['admin_connecte'])) {
    header('Location: ajouter_projet.php');
    exit;
}

require_once "db.php";

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $identifiant = trim($_POST['identifiant'] ?? '');
    $motDePasse  = $_POST['mot_de_passe']     ?? '';
    if ($identifiant === '' || $motDePasse === '') {
        $erreur = 'Identifiant et mot de passe requis.';

    } else {
        $stmt = $pdo->prepare('SELECT id, mot_de_passe FROM admin WHERE identifiant = :identifiant');
        $stmt->execute([':identifiant' => $identifiant]);
        $admin = $stmt->fetch();
        if ($admin && password_verify($motDePasse, $admin['mot_de_passe'])) {
            session_regenerate_id(true);

            $_SESSION['admin_connecte'] = true;
            $_SESSION['admin_id']       = $admin['id'];
            $retour = $_GET['retour'] ?? '';
            if ($retour && str_starts_with(urldecode($retour), '/')) {
                header('Location: ' . urldecode($retour));
            } else {
                header('Location: ajouter_projet.php');
            }
            exit;

        } else {
            $erreur = 'Identifiant ou mot de passe incorrect.';
        }
    }
}
?>

<div class="loginBody">
    <section class="loginFormSection">

        <h2>Connexion</h2>

        <?php if ($erreur !== ''): ?>
            <p class="loginErreur"><?= htmlspecialchars($erreur) ?></p>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="champFormulaire">
                <label for="identifiant">Identifiant</label>
                <input
                    type="text"
                    id="identifiant"
                    name="identifiant"
                    required
                    autocomplete="username"
                    value="<?= htmlspecialchars($identifiant ?? '') ?>">
            </div>

            <div class="champFormulaire">
                <label for="mot_de_passe">Mot de passe</label>
                <input
                    type="password"
                    id="mot_de_passe"
                    name="mot_de_passe"
                    required
                    autocomplete="current-password">
            </div>

            <button type="submit" class="btnSubmit">Se connecter</button>

        </form>

    </section>
</div>

<?php include "footer.php" ?>
</body>
</html>
