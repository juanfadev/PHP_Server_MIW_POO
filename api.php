<?php
/**
 * Created by PhpStorm.
 * User: juanf
 * Date: 26/11/2018
 * Time: 15:57
 */

// get the HTTP method, path and body of the request
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
header("Access-Control-Allow-Origin: *");
$input = json_decode(file_get_contents('php://input'), true);

// retrieve the entity and key from the path
$entity = preg_replace('/[^a-z0-9_]+/i', '', array_shift($request));
$key = array_shift($request);

switch ($method) {
    case 'GET':
        if ($entity == "LandmarksOrHistoricalBuildings" or $entity == "Places") {
            if (is_numeric($key)){
                $data_proccesed = file_get_contents('./' . $entity . '/' . $key . '.json');
            }
            else {
                $array_data = array();
                $dir = "./$entity/";
                $a = array_slice(scandir($dir), 2);
                foreach ($a as &$item) {
                    $string = file_get_contents("./" . $entity . "/" . $item);
                    $array_data[] = json_decode($string, true);
                }
                $data_proccesed = json_encode($array_data, JSON_PRETTY_PRINT);
            }
        } else {
            $data_proccesed = file_get_contents("./entities/entities.json");
        }
        returnJson($data_proccesed);
        break;
    case 'PUT':
        if ($entity == "LandmarksOrHistoricalBuildings" or $entity == "Places") {
            if (is_numeric($key)) {
                file_put_contents('./' . $entity . '/' . $key . '.json', json_encode($input, JSON_FORCE_OBJECT));
                $data_proccesed = file_get_contents('./' . $entity . '/' . $key . '.json');
                returnJson($data_proccesed);
            }
        }
        break;
    case 'POST':
        if ($entity == "LandmarksOrHistoricalBuildings" or $entity == "Places") {
            $a = scandir($dir, 1);
            //First element
            $last_file = reset($array);
            $key = intval($last_file) + 1;
            file_put_contents('./' . $entity . '/' . $key . '.json', json_encode($input, JSON_FORCE_OBJECT));
            $data_proccesed = file_get_contents('./' . $entity . '/' . $key . '.json');
        }
        returnJson($data_proccesed);
        break;
    case 'DELETE':
        if ($entity == "LandmarksOrHistoricalBuildings" or $entity == "Places") {
            if (is_numeric($key)) {
                $data_proccesed = file_get_contents('./' . $entity . '/' . $key . '.json');
                unlink('./' . $entity . '/' . $key . '.json');
                returnJson($data_proccesed);
            }
        }
        break;
}

function returnJson($data_proccesed){
    header('Content-Type: application/json');
    echo $data_proccesed;
}
