<?php 
require './db/conn.php';

$db = new DbConn();
$pdo = $db->getPdo(); // Obtén el objeto PDO

function verificarCredenciales($email, $password, $pdo) {
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $password = filter_var($password, FILTER_SANITIZE_STRING);

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE UsuarioCorreo = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['UsuarioPass'])) {
        $_SESSION['user_id'] = $user['UsuarioId'];
        return true;
    } else {
        return false;
    }

}

$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (verificarCredenciales($email, $password, $pdo)) {

        $stmt = $pdo->prepare('SELECT UsuarioNombre FROM usuarios WHERE UsuarioId = :id');
        $stmt->bindParam(':id', $_SESSION['user_id']);
        $stmt->execute();
        $results = $stmt->fetch(PDO::FETCH_ASSOC);
      
        $sesion = null;
      
        if (count($results) > 0) {
          $sesion = $results;
        }

        echo "
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Has iniciado sesión.',
                    text: 'Bienvenido " . $sesion['UsuarioNombre'] . ".', 
                    icon: 'success'
                }).then(() => {
                    window.location.href = 'views/index.php';
                });
            });
        </script>";
    } else {
        echo " 
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Error',
                    text: 'La contraseña o el usuario son incorrectos.',
                    icon: 'error'
                });
            });
        </script>";
    }
}

if (isset($_SESSION['login_required']) && $_SESSION['login_required'] == true) {
    echo "<script>
      document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
          icon: 'info',
          title: 'Inicia sesión',
          text: 'Por favor inicia sesión para continuar.',
          confirmButtonColor: '#3085d6'
        });
      });
    </script>";
    unset($_SESSION['login_required']);
  }
?>
