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
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
$input = json_decode(file_get_contents('php://input'), true);

// retrieve the entity and key from the path
$entity = preg_replace('/[^a-z0-9_]+/i', '', array_shift($request));
$key = array_shift($request);


function returnJson($data_proccesed)
{
    header('Content-Type: application/json');
    echo $data_proccesed;
}

switch ($method) {
    case 'GET':
        if ($entity == "LandmarksOrHistoricalBuildings") {
            if (is_numeric($key)) {
                $landmark = new Landmark();
                $landmark->readFromFile($key);
                $data_proccesed = $landmark->toJSON();
            } else {
                $array_data = array();
                $dir = "./$entity/";
                $a = array_slice(scandir($dir), 2);
                foreach ($a as &$item) {
                    $landmark = new Landmark();
                    $string = file_get_contents("./" . $entity . "/" . $item);
                    $landmark->set(json_decode($string, true));
                    $array_data[] = $landmark;
                }
                $data_proccesed = json_encode($array_data, JSON_PRETTY_PRINT);
            }
        } elseif ($entity == "Places") {
            if (is_numeric($key)) {
                $place = new Place();
                $place->readFromFile($key);
                $data_proccesed = $place->toJSON();
            } else {
                $array_data = array();
                $dir = "./$entity/";
                $a = array_slice(scandir($dir), 2);
                foreach ($a as &$item) {
                    $place = new Place();
                    $string = file_get_contents("./" . $entity . "/" . $item);
                    $place->set(json_decode($string, true));
                    $array_data[] = $place;
                }
                $data_proccesed = json_encode($array_data, JSON_PRETTY_PRINT);
            }
        } else {
            $data_proccesed = file_get_contents("./entities/entities.json");
        }
        returnJson($data_proccesed);
        break;
    case 'PUT':
        if ($entity == "LandmarksOrHistoricalBuildings") {
            if (is_numeric($key)) {
                $landmark = new Landmark();
                $landmark->set($input);
                $landmark->writeToFile($key);
                returnJson($landmark->toJSON());
            }
        } elseif ($entity == "Places") {
            if (is_numeric($key)) {
                $place = new Place();
                $place->set($input);
                $place->writeToFile($key);
                returnJson($place->toJSON());
            }
        }
        break;
    case 'POST':
        if ($entity == "LandmarksOrHistoricalBuildings") {
            $dir = "./$entity/";
            $array = scandir($dir, 1);
            //First element
            $last_file = reset($array);
            $key = intval($last_file) + 1;
            $landmark = new Landmark();
            $landmark->set($input);
            $landmark->writeToFile($key);
            $data_proccesed = $landmark->toJSON();
            returnJson($data_proccesed);
        } elseif ($entity == "Places") {
            $dir = "./$entity/";
            $array = scandir($dir, 1);
            //First element
            $last_file = reset($array);
            $key = intval($last_file) + 1;
            $place = new Place();
            $place->set($input);
            $place->writeToFile($key);
            $data_proccesed = $place->toJSON();
            returnJson($data_proccesed);
        }
        break;
    case 'DELETE':
        if ($entity == "LandmarksOrHistoricalBuildings") {
            if (is_numeric($key)) {
                $landmark = new Landmark();
                $landmark->deleteFile($key);
                returnJson($landmark->toJSON());
            }
        } elseif ($entity == "Places") {
            if (is_numeric($key)) {
                $place = new Place();
                $place->deleteFile($key);
                returnJson($place->toJSON());
            }
        }
        break;
}


class Landmark
{

    public function set($json)
    {
        foreach ($json AS $key => $value) $this->{$key} = $value;
    }

    public function isValid()
    {
        return $this->{'@type'} == "LandmarksOrHistoricalBuildings" and $this->{'@context'} == "http://schema.org/";
    }

    public function readFromFile($id)
    {
        $this->set(json_decode(file_get_contents('./LandmarksOrHistoricalBuildings/' . $id . '.json'), true));
    }

    public function writeToFile($id)
    {
        file_put_contents('./LandmarksOrHistoricalBuildings/' . $id . '.json', $this->toJSON());
    }

    public function deleteFile($id)
    {
        $this->readFromFile($id);
        unlink('./LandmarksOrHistoricalBuildings/' . $id . '.json');
    }

    public function toJSON()
    {
        return json_encode($this, JSON_PRETTY_PRINT);
    }
}

class Place
{

    public function set($json)
    {
        foreach ($json AS $key => $value) $this->{$key} = $value;
    }

    public function isValid()
    {
        return $this->{'@type'} == "Place" and $this->{'@context'} == "http://schema.org/";
    }

    public function readFromFile($id)
    {
        $this->set(json_decode(file_get_contents('./Places/' . $id . '.json'), true));
    }

    public function writeToFile($id)
    {
        file_put_contents('./Places/' . $id . '.json', $this->toJSON());
    }

    public function deleteFile($id)
    {
        $this->readFromFile($id);
        unlink('./Places/' . $id . '.json');
    }

    public function toJSON()
    {
        return json_encode($this, JSON_PRETTY_PRINT);
    }
}
