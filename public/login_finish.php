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
?>

<h1>ログイン完了</h1>

<p>
  ログイン完了しました!
</p>
<hr>
<p>
  また、あなたが現在ログインしている会員情報は以下のとおりです。
</p>

<dl> <!-- 登録情報を出力する際はXSS防止のため htmlspecialchars() を必ず使いましょう -->
  <dt>ID</dt>
  <dd><?= htmlspecialchars($user['id']) ?></dd>
  <dt>メールアドレス</dt>
  <dd><?= htmlspecialchars($user['email']) ?></dd>
  <dt>名前</dt>
  <dd><?= htmlspecialchars($user['name']) ?></dd>
</dl>

<form action="name.php" method="POST">
  <button type="submit">名前変更</button>
</form>
