<?php
require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$client = new Client();

$date_from = $_GET['date_from'] ?? date('Y-m-d 00:00:00', strtotime('-30 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d 23:59:59');

if (str_contains($date_from, 'T') ) $date_from = str_replace('T', ' ', $date_from) . ':00';
if (str_contains($date_to, 'T') ) $date_to = str_replace('T', ' ', $date_to) . ':00';

try {
    $response = $client->post($_ENV['API_URL'].'/getstatuses', [
        'headers' => [
            'token' => $_ENV['API_TOKEN'] ?? '',
            'Content-Type' => 'application/json'
        ],
        'json' => [
            'date_from' => $date_from,
            'date_to' => $date_to,
            'page' => 0,
            'limit' => 100
        ],
        'timeout' => 10,
    ]);

    $result = json_decode($response->getBody()->getContents(), true);
    if (!empty($result['status'])) {
        $raw = $result['data'] ?? '[]';
        $leads = is_string($raw) ? json_decode($raw, true) : $raw;

        if (!is_array($leads)) $leads = [];
    } else {
        $leads = [];
        $error = $result['error'] ?? 'unknown error';
    }
} catch (\Exception $e) {
    $leads = [];
    $error = 'Ошибка при запросе: ' . $e->getMessage();
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Lead Statuses</title>
    <style>
        body{font-family:Arial,Helvetica,sans-serif;max-width:1000px;margin:20px auto;padding:0 12px;}
        table{border-collapse:collapse;width:100%;}
        th,td{border:1px solid #ddd;padding:8px;text-align:left;}
        th{background:#f7f7f7;}
        form{margin-bottom:12px;}
    </style>
</head>
<body>
    <h1>Статусы лидов</h1>
    <form method="get">
        <label>От:
            <input type="datetime-local" name="date_from" value="<?= date('Y-m-d\TH:i', strtotime($date_from)) ?>">
        </label>
        <label>До:
            <input type="datetime-local" name="date_to" value="<?= date('Y-m-d\TH:i', strtotime($date_to)) ?>">
        </label>
        <button type="submit">Фильтровать</button>
    </form>

    <?php if (!empty($error)): ?>
        <div style="background:#ffecec;padding:10px;border-radius:6px;margin-bottom:12px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <p><a href="index.php">Добавить Lead</a></p>
    <table>
        <thead>
            <tr><th>ID</th><th>Email</th><th>Status</th><th>FTD</th></tr>
        </thead>
        <tbody>
            <?php if (empty($leads)): ?>
                <tr><td colspan="4">Нет данных</td></tr>
            <?php else: ?>
                <?php foreach ($leads as $lead): ?>
                    <tr>
                        <td><?= htmlspecialchars($lead['id'] ?? '') ?></td>
                        <td><?= htmlspecialchars($lead['email'] ?? '') ?></td>
                        <td><?= htmlspecialchars( $lead['status'] ?? '' ) ?></td>
                        <td><?= htmlspecialchars($lead['ftd'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
