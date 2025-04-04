<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
$dataFile = 'user.json';

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';


function readData() {
    global $dataFile;
    if (!file_exists($dataFile)) {
        file_put_contents($dataFile, '[]');
    } else {
        echo file_get_contents($dataFile);
    }
}

function updateData($input) {
    global $dataFile;
    $userId = $input['userId'] ?? null;
    $url = $input['url'] ?? null;

    if (!$userId || !$url) {
        echo json_encode(['error' => 'Missing data']);
        return;
    }

    $data = json_decode(file_get_contents($dataFile), true);
    $user_null = false;

    foreach ($data as &$user) {
        if ($userId == $user['id']) {
            $user['selectedPhotos'][] = $url;
            $user_null = true;
            break;
        }
    }

    if (!$user_null) {
        $data[] = [
            'id' => $userId,
            'selectedPhotos' => [$url]
        ];
    }

    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
    echo json_encode(['success' => true]);
}

function deleteItem($input) {
    global $dataFile;
    if ($input['action'] === 'delete') {         
        $userId = $input['userId'];         
        $data = json_decode(file_get_contents($dataFile), true);         
    
        foreach ($data as $index => $user) {         
            if ($user['id'] == $userId) {          
                array_shift($data[$index]['selectedPhotos']);   
            }        
        }         
    
        if (is_writable($dataFile)) {
            file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            // echo "Файл доступен для записи!";
        } else {
            // echo "Ошибка: Нет прав на запись!";
        }
       
        // echo json_encode([
        //     'status' => 'success',
        //     'deleted' => $deletedItem,
        //     'debug' => [
        //         'user_id' => $input['userId'],
        //         'remaining_photos' => $data[$index]['selectedPhotos'] ?? []
        //     ]
        // ]);
        // return;
    }
}

switch ($action) {
    case 'read':
        readData();
        break;
    case 'update':
        updateData($input);
        break;
    case 'delete':
        deleteItem($input);
        break;
    default:
        echo json_encode(['error' => 'Unknown action']);
}