<?php
$redis = new Redis();
$redis->connect('redis', 6379);
$key = 'bbs_keiziban_list_json';
$keiziban_list = $redis->exists($key) ? json_decode($redis->get($key)) : [];
if (!empty($_POST['keiziban'])) {
  $keiziban = $_POST['keiziban'];
  array_unshift($keiziban_list, $keiziban);
  $redis->set($key, json_encode($keiziban_list));
  return header('Location: .redis_bbs.php');
}
?>
<form method="POST">
  <textarea name="keiziban"></textarea><br>
  <button type="submit">更新</button>
</form>
<br>
<hr>
<?php foreach($keiziban_list as $keiziban): ?>
<div>
  <br>
  <?= nl2br(htmlspecialchars($keiziban)) ?><br>
  <br>
  <hr>
</div>
<?php endforeach; ?>

