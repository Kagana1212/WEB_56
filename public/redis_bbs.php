<?php
$redis = new Redis();
$redis->connect('redis', 6379);
$key = 'bbs_keiziban';
$keiziban = $redis->exists($key) ? $redis->get($key) : '';
if (!empty($_POST['keiziban'])) {
  $keiziban = $_POST['keiziban'];
  $redis->set($key, strval($keiziban));
    return header('Location: ./redis_bbs.php');
  }
?>
<form method="POST">
  <textarea name="keiziban"></textarea>
  <button type="submit">更新</button>
</form>
<br>
<hr>
今の内容<br>
<br>
<div><?= nl2br(htmlspecialchars($keiziban)) ?></div>
