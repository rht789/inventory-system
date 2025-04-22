<?php
include '../db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$override = $_POST['_method'] ?? null;

if ($method === 'POST' && $override === 'DELETE') {
    $id = $_POST['id'] ?? null;

    if (!$id) {
        echo json_encode(['error' => 'No user ID provided.']);
        exit;
    }

    try {
        //cadmin cannot delete himself😗💀💀🚩🚩😀😀🤔🤔😄😄😄🙄

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount()) {
            echo json_encode(['success' => 'User deleted successfully.']);
        } else {
            echo json_encode(['error' => 'User not found.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Failed to delete user.']);
    }
    exit;
}

switch ($method) {
    case 'GET':
        $stmt = $pdo->query("SELECT id, username, email, phone, role FROM users ORDER BY id ASC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $grouped = ['admin' => [], 'staff' => []];

        foreach ($users as $user) {
            $grouped[$user['role']][] = $user;
        }

        echo json_encode($grouped);
        break;

    case 'POST':
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $role = $_POST['role'] ?? 'staff';

        if (!$username || !$email || !$password) {
            echo json_encode(['error' => 'Please fill all required fields.']);
            exit;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, phone, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hash, $phone, $role]);
            echo json_encode(['success' => 'User created successfully.']);
        } catch (PDOException $e) {
            if ($e->errorInfo[1] === 1062) {
                echo json_encode(['error' => 'Username or email already exists.']);
            } else {
                echo json_encode(['error' => 'Something went wrong.']);
            }
        }
        break;

    default:
        echo json_encode(['error' => 'Invalid request method.']);
}