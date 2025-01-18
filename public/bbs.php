<?php
session_start();
$dbh = new PDO('mysql:host=mysql;dbname=kyototech', 'root', '');

if (isset($_POST['body']) && !empty($_SESSION['login_user_id'])) {


  $image_filename = null;
  if (isset($_FILES['image']) && !empty($_FILES['image']['tmp_name'])) {

    if (preg_match('/^image\//', mime_content_type($_FILES['image']['tmp_name'])) !== 1) {

      header("HTTP/1.1 302 Found");
      header("Location: ./bbs.php");
      return;
    }

    $pathinfo = pathinfo($_FILES['image']['name']);
    $extension = $pathinfo['extension'];

    $image_filename = strval(time()) . bin2hex(random_bytes(25)) . '.' . $extension;
    $filepath =  '/var/www/upload/image/' . $image_filename;
    move_uploaded_file($_FILES['image']['tmp_name'], $filepath);
  }


  $insert_sth = $dbh->prepare("INSERT INTO bbs_entries (user_id, body, image_filename) VALUES (:user_id, :body, :image_filename);");
  $insert_sth->execute([
    ':user_id' => $_SESSION['login_user_id'],
    ':body' => $_POST['body'],
    ':image_filename' => $image_filename,
  ]);


  header("HTTP/1.1 302 Found");
  header("Location: ./bbs.php");
  return;
}
?>

<?php if(empty($_SESSION['login_user_id'])): ?>
  投稿するには<a href="/login.php">ログイン</a>が必要です。
<?php else: ?>
</div><a href="/icon.php">アイコン画像の設定はこちら</a>。</div>
<form method="POST" action="./bbs.php" enctype="multipart/form-data">
  <textarea name="body"></textarea>
  <div style="margin: 1em 0;">
    <input type="file" accept="image/*" name="image" id="imageInput">
  </div>
  <button type="submit">送信</button>
</form>
<?php endif; ?>

<hr>

<dl id="entryTemplate" style="display: none; margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
  <dt>番号</dt>
  <dd data-role="entryIdArea"></dd>
  <dt>投稿者</dt>
  <dd>
    <a href="" data-role="entryUserAnchor">
      <img data-role="entryUserIconImage"
        style="height: 2em; width: 2em; border-radius: 50%; object-fit: cover;">
      <span data-role="entryUserNameArea"></span>
    </a>
  </dd>
  <dt>日時</dt>
  <dd data-role="entryCreatedAtArea"></dd>
  <dt>内容</dt>
  <dd data-role="entryBodyArea">
  </dd>
</dl>
<div id="entriesRenderArea"></div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const entryTemplate = document.getElementById('entryTemplate');
  const entriesRenderArea = document.getElementById('entriesRenderArea');
  const request = new XMLHttpRequest();
  request.onload = (event) => {
    const response = event.target.response;
    response.entries.forEach((entry) => {

      const entryCopied = entryTemplate.cloneNode(true);

      entryCopied.style.display = 'block';


      entryCopied.querySelector('[data-role="entryIdArea"]').innerText = entry.id.toString();


      if (entry.user_icon_file_url) {
        entryCopied.querySelector('[data-role="entryUserIconImage"]').src = entry.user_icon_file_url;
      } else {
        entryCopied.querySelector('[data-role="entryUserIconImage"]').style.display = 'none';
     }

      entryCopied.querySelector('[data-role="entryUserNameArea"]').innerText = entry.user_name;


      entryCopied.querySelector('[data-role="entryCreatedAtArea"]').innerText = entry.created_at;


      entryCopied.querySelector('[data-role="entryBodyArea"]').innerHTML = entry.body;


      if (entry.image_file_url) {
        const imageElement = new Image();
        imageElement.src = entry.image_file_url;
        imageElement.style.display = 'block';
        imageElement.style.marginTop = '1em';
        imageElement.style.maxHeight = '300px';
        imageElement.style.maxWidth = '300px';
        entryCopied.querySelector('[data-role="entryBodyArea"]').appendChild(imageElement);
      }


      entriesRenderArea.appendChild(entryCopied);
    });
  }
  request.open('GET', '/bbs_json.php', true);
  request.responseType = 'json';
  request.send();

  const imageInput = document.getElementById("imageInput");
  imageInput.addEventListener("change", () => {
    if (imageInput.files.length < 1) {

      return;
    }
    if (imageInput.files[0].size > 5 * 1024 * 1024) {

      alert("5MB以下のファイルを選択してください。");
      imageInput.value = "";
    }
  });
});
</script>

