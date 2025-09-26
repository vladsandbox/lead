<?php
require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Dotenv\Dotenv;

// Try to load .env if exists, otherwise use environment variables
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client = new Client();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $landingUrl = ($_SERVER['HTTP_HOST'] ?? 'localhost');

    $payload = [
        'firstName' => $_POST['firstName'] ?? '',
        'lastName'  => $_POST['lastName'] ?? '',
        'phone'     => $_POST['phone'] ?? '',
        'email'     => $_POST['email'] ?? '',
        'countryCode' => $_ENV['COUNTRY_CODE'] ?? 'GB',
        'box_id'    => $_ENV['BOX_ID'] ?? 28,
        'offer_id'  => $_ENV['OFFER_ID'] ?? 5,
        'landingUrl'=> $landingUrl,
        'ip'        => $ip,
        'password'  => $_ENV['PASSWORD'] ?? 'qwerty12',
        'language'  => $_ENV['LANGUAGE'] ?? 'en',
    ];

    try {
        $response = $client->post($_ENV['API_URL'] .'/addlead', [
            'headers' => [
                'token' => $_ENV['API_TOKEN'],
                'Content-Type' => 'application/json'
            ],
            'json' => $payload,
            'timeout' => 10,
        ]);

        $result = json_decode($response->getBody()->getContents(), true);

        if (is_array($result) && !empty($result['status']) && $result['status'] === true) {
            $message = 'Лид добавлен! ID: ' . ($result['id'] ?? '');
        } else {
            $err = $result['error'] ?? json_encode($result);
            $message = 'Ошибка: ' . $err;
        }
    } catch (\Exception $e) {
        $message = 'Ошибка при запросе: ' . $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Add Lead</title>
    <style>
        body{font-family:Arial,Helvetica,sans-serif;max-width:760px;margin:20px auto;padding:0 12px;}
        input{display:block;margin:8px 0;padding:8px;width:100%;box-sizing:border-box;}
        button{padding:10px 16px;}
        .msg{background:#f0f0f0;padding:10px;border-radius:6px;margin-bottom:12px;}
    </style>
</head>
<body>
    <h1>Форма отправки лида</h1>
    <?php if (!empty($message)): ?>
        <div class="msg"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="post" >
        <input type="text" name="firstName" placeholder="Имя" required>
        <input type="text" name="lastName" placeholder="Фамилия" required>
        <input type="text" name="phone" placeholder="Телефон" required>
        <input type="email" name="email" placeholder="Email" required>
        <button type="submit">Отправить</button>
    </form>
    <p><a href="statuses.php">Перейти к статусам</a></p>
</body>
</html>
