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
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f5f5f5;
    }
    .container {
      width: 90%;
      max-width: 1200px;
      margin: 0 auto;
    }

    form {
      background-color: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      margin-top: 20px;
    }

    textarea {
      width: 100%;
      padding: 10px;
      font-size: 16px;
      border-radius: 4px;
      border: 1px solid #ddd;
      box-sizing: border-box;
      resize: vertical;
      margin-bottom: 1em;
    }

    input[type="file"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 1em;
      font-size: 16px;
      border: 1px solid #ddd;
      border-radius: 4px;
      box-sizing: border-box;
    }

    button {
      width: 100%;
      padding: 12px;
      background-color: #4CAF50;
      color: white;
      font-size: 16px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    button:hover {
      background-color: #45a049;
    }

    dl {
      margin-bottom: 1.5em;
      background-color: white;
      padding: 1em;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    dt {
      font-weight: bold;
      margin-bottom: 0.5em;
    }

    dd {
      margin-bottom: 1em;
      font-size: 14px;
      line-height: 1.6;
    }

    img {
      max-width: 100%;
      border-radius: 8px;
      margin-top: 1em;
    }

    .entry-header {
      display: flex;
      align-items: center;
      margin-bottom: 1em;
    }

    .entry-header img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      margin-right: 10px;
    }

    .entry-header .entry-user-name {
      font-weight: bold;
    }

    .entry-body {
      font-size: 16px;
    }

    .entry-time {
      font-size: 12px;
      color: #888;
    }

    @media (max-width: 768px) {
      form {
        padding: 15px;
      }

      textarea {
        font-size: 14px;
        padding: 8px;
      }

      input[type="file"], button {
        padding: 10px;
        font-size: 14px;
      }

      .entry-header {
        flex-direction: column;
        align-items: flex-start;
      }

      .entry-header img {
        width: 35px;
        height: 35px;
        margin-bottom: 8px;
      }

      .entry-header .entry-user-name {
        font-size: 14px;
      }

      .entry-body {
        font-size: 14px;
      }

      .entry-time {
        font-size: 11px;
      }
    }

    @media (max-width: 480px) {
      form {
        padding: 10px;
      }

      textarea {
        font-size: 13px;
        padding: 6px;
      }

      input[type="file"], button {
        padding: 8px;
        font-size: 13px;
      }

      .entry-header img {
        width: 30px;
        height: 30px;
      }

      .entry-header .entry-user-name {
        font-size: 12px;
      }

      .entry-body {
        font-size: 13px;
      }
    }
  </style>
</head>

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

