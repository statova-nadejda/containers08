<?php

require_once __DIR__ . '/testframework.php';

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../modules/database.php';
require_once __DIR__ . '/../modules/page.php';

$tests = new TestFramework();

function testDbConnection() {
    global $config;

    $db = new Database($config["db"]["path"]);

    return assertExpression($db instanceof Database,
        "Database connection established",
        "Database connection failed");
}

function testDbCount() {
    global $config;

    $db = new Database($config["db"]["path"]);
    $count = $db->Count("page");

    return assertExpression($count >= 0,
        "Count method works: {$count}",
        "Count method failed");
}

function testDbCreate() {
    global $config;

    $db = new Database($config["db"]["path"]);

    $id = $db->Create("page", [
        'title' => 'Test trip',
        'country' => 'Test country',
        'city' => 'Test city',
        'travel_date' => '2026-01-01',
        'description' => 'Test description'
    ]);

    return assertExpression($id > 0,
        "Create method works, ID = {$id}",
        "Create method failed");
}

function testDbRead() {
    global $config;

    $db = new Database($config["db"]["path"]);

    $id = $db->Create("page", [
        'title' => 'Read test',
        'country' => 'Italy',
        'city' => 'Rome',
        'travel_date' => '2026-07-15',
        'description' => 'Trip to Rome'
    ]);

    $row = $db->Read("page", $id);

    $success = $row && $row['title'] === 'Read test';

    return assertExpression($success,
        "Read method works",
        "Read method failed");
}

function testDbUpdate() {
    global $config;

    $db = new Database($config["db"]["path"]);

    $id = $db->Create("page", [
        'title' => 'Old trip',
        'country' => 'Old country',
        'city' => 'Old city',
        'travel_date' => '2026-01-01',
        'description' => 'Old description'
    ]);

    $db->Update("page", $id, [
        'title' => 'New trip',
        'country' => 'New country',
        'city' => 'New city',
        'travel_date' => '2026-12-12',
        'description' => 'New description'
    ]);

    $row = $db->Read("page", $id);

    $success = $row && $row['title'] === 'New trip';

    return assertExpression($success,
        "Update method works",
        "Update method failed");
}

function testDbDelete() {
    global $config;

    $db = new Database($config["db"]["path"]);

    $id = $db->Create("page", [
        'title' => 'Delete trip',
        'country' => 'Country',
        'city' => 'City',
        'travel_date' => '2026-01-01',
        'description' => 'Delete this record'
    ]);

    $db->Delete("page", $id);

    $row = $db->Read("page", $id);

    return assertExpression($row === false,
        "Delete method works",
        "Delete method failed");
}

function testDbFetch() {
    global $config;

    $db = new Database($config["db"]["path"]);
    $rows = $db->Fetch("SELECT * FROM page");

    return assertExpression(is_array($rows),
        "Fetch method works",
        "Fetch method failed");
}

function testDbExecute() {
    global $config;

    $db = new Database($config["db"]["path"]);

    $db->Execute("CREATE TABLE IF NOT EXISTS test_table (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT
    )");

    $db->Execute("INSERT INTO test_table (name) VALUES ('test')");

    $count = $db->Count("test_table");

    return assertExpression($count > 0,
        "Execute method works",
        "Execute method failed");
}

function testPageConstructor() {
    $templatePath = __DIR__ . '/../templates/index.tpl';
    $page = new Page($templatePath);

    return assertExpression($page instanceof Page,
        "Page constructor works",
        "Page constructor failed");
}

function testPageRender() {
    $templatePath = __DIR__ . '/../templates/index.tpl';
    $page = new Page($templatePath);

    $html = $page->Render([
        'title' => 'Trip to Italy',
        'country' => 'Italy',
        'city' => 'Rome',
        'travel_date' => '2026-07-15',
        'description' => 'Visit Rome and enjoy local cafes'
    ]);

    $success =
        strpos($html, 'Trip to Italy') !== false &&
        strpos($html, 'Italy') !== false &&
        strpos($html, 'Rome') !== false;

    return assertExpression($success,
        "Render method works",
        "Render method failed");
}

$tests->add('Database connection', 'testDbConnection');
$tests->add('Database count', 'testDbCount');
$tests->add('Database create', 'testDbCreate');
$tests->add('Database read', 'testDbRead');
$tests->add('Database update', 'testDbUpdate');
$tests->add('Database delete', 'testDbDelete');
$tests->add('Database fetch', 'testDbFetch');
$tests->add('Database execute', 'testDbExecute');
$tests->add('Page constructor', 'testPageConstructor');
$tests->add('Page render', 'testPageRender');

$tests->run();

echo "Result: " . $tests->getResult() . PHP_EOL;