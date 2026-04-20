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

function testDbCreate() {
    global $config;

    $db = new Database($config["db"]["path"]);

    $id = $db->Create("page", [
        'title' => 'Test page',
        'content' => 'Test content'
    ]);

    return assertExpression($id > 0,
        "Create method works",
        "Create method failed");
}

function testDbRead() {
    global $config;

    $db = new Database($config["db"]["path"]);

    $id = $db->Create("page", [
        'title' => 'Read test',
        'content' => 'Read content'
    ]);

    $row = $db->Read("page", $id);

    return assertExpression($row && $row['title'] === 'Read test',
        "Read method works",
        "Read method failed");
}

function testDbUpdate() {
    global $config;

    $db = new Database($config["db"]["path"]);

    $id = $db->Create("page", [
        'title' => 'Old',
        'content' => 'Old content'
    ]);

    $db->Update("page", $id, [
        'title' => 'New',
        'content' => 'New content'
    ]);

    $row = $db->Read("page", $id);

    return assertExpression($row['title'] === 'New',
        "Update method works",
        "Update method failed");
}

function testDbDelete() {
    global $config;

    $db = new Database($config["db"]["path"]);

    $id = $db->Create("page", [
        'title' => 'Delete test',
        'content' => 'Delete content'
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

function testPageRender() {
    $template = __DIR__ . '/../templates/index.tpl';

    $page = new Page($template);

    $html = $page->Render([
        'title' => 'Hello',
        'content' => 'World'
    ]);

    return assertExpression(strpos($html, 'Hello') !== false,
        "Render method works",
        "Render method failed");
}

$tests->add('Database connection', 'testDbConnection');
$tests->add('Database create', 'testDbCreate');
$tests->add('Database read', 'testDbRead');
$tests->add('Database update', 'testDbUpdate');
$tests->add('Database delete', 'testDbDelete');
$tests->add('Database fetch', 'testDbFetch');
$tests->add('Page render', 'testPageRender');

$tests->run();

echo "Result: " . $tests->getResult() . PHP_EOL;