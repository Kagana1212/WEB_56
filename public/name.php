<?php
session_start();


if (empty($_SESSION['login_user_id'])) {
  header("HTTP/1.1 302 Found");
  header("Location: ./login.php");
  return;
}


$dbh = new PDO('mysql:host=mysql;dbname=kyototech', 'root', '');

$insert_sth = $dbh->prepare("SELECT * FROM users WHERE id = :id");
$insert_sth->execute([
    ':id' => $_SESSION['login_user_id'],
]);
$user = $insert_sth->fetch();

if (isset($_POST['name'])) {



  $insert_sth = $dbh->prepare("UPDATE users SET name = :name WHERE id = :id");
  $insert_sth->execute([
    ':id' => $user['id'],
    ':name' => $_POST['name'],
  ]);

  header("HTTP/1.1 302 Found");
  header("Location: ./name.php?success=1");
  return;
}
?>

<h1>名前変更</h1>
<form method="POST">
  <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>">
  <button type="submit">決定</button>
</form>

<?php if(!empty($_GET['success'])): ?>
<div>
  名前の変更が完了しました
</div>
<?php endif; ?>

<form action="login_finish.php" method="POST">
  <button type="submit">ログインページへ</button>
</form>
