# WEB_56

ローカルにクローン
```bash
git clone https://github.com/Kagana1212/WEB_56.git
```

Dockerfileを使用して環境構築
```bash
docker compose up
```

SQL
```bash
docker compose exec mysql mysql kyototech
```

テーブル作成
```bash
CREATE TABLE `bbs_entries` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `body` TEXT NOT NULL,
    `image_filename` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

localhost/bbs.php　にアクセス
