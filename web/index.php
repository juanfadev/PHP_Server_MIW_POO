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


function returnResponse($data_proccesed)
{
    $format = $_SERVER['HTTP_ACCEPT'] or 'html';
    switch ($format) {
        case 'application/json':
            header('Content-Type: application/json');
            echo $data_proccesed->toJSON();
            break;
        default:
            header('Content-type: text/html');
            echo $data_proccesed->toHTML();
            break;

    }
}

function returnArrayResponse($data_proccesed)
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
                $data_proccesed = $landmark;
                returnResponse($data_proccesed);
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
                returnArrayResponse($data_proccesed);
            }
            returnResponse($data_proccesed);
        } elseif ($entity == "Places") {
            if (is_numeric($key)) {
                $place = new Place();
                $place->readFromFile($key);
                $data_proccesed = $place;
                returnResponse($data_proccesed);
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
                returnArrayResponse($data_proccesed);
            }

        } else {
            $data_proccesed = file_get_contents("./entities/entities.json");
            returnArrayResponse($data_proccesed);
        }
        break;
    case 'PUT':
        if ($entity == "LandmarksOrHistoricalBuildings") {
            if (is_numeric($key)) {
                $landmark = new Landmark();
                $landmark->set($input);
                $landmark->writeToFile($key);
                returnResponse($landmark);
            }
        } elseif ($entity == "Places") {
            if (is_numeric($key)) {
                $place = new Place();
                $place->set($input);
                $place->writeToFile($key);
                returnResponse($place);
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
            returnResponse($landmark);
        } elseif ($entity == "Places") {
            $dir = "./$entity/";
            $array = scandir($dir, 1);
            //First element
            $last_file = reset($array);
            $key = intval($last_file) + 1;
            $place = new Place();
            $place->set($input);
            $place->writeToFile($key);
            returnResponse($place);
        }
        break;
    case 'DELETE':
        if ($entity == "LandmarksOrHistoricalBuildings") {
            if (is_numeric($key)) {
                $landmark = new Landmark();
                $landmark->deleteFile($key);
                returnResponse($landmark);
            }
        } elseif ($entity == "Places") {
            if (is_numeric($key)) {
                $place = new Place();
                $place->deleteFile($key);
                returnResponse($place);
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

    public function toHTML()
    {
        return "<!DOCTYPE html>
            <html>
            <head>
                <meta charset=\"utf-8\" />
                <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
                <title>Landmark" . $this->name . "</title>
                <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
                <script type=\"application/ld+json\">
                    " . $this->toJSON() . "
                </script>
            </head>
            <body>
                <h1>Landmark:" . $this->name . " </h1 >
                <h2 > Description: </h2 >
                    <p >" . $this->description . "</p>
                <h2 > Address: </h2 >
                <ul >
                    <li >
                    Locality: " . $this->address['addressLocality'] . "
                    </li >
                                    <li >
                    Region: " . $this->address['addressRegion'] . "
                    </li >
                                    <li >
                    Country: " . $this->address['addressCountry'] . "
                    </li >
                </ul >
                <img src = \"" . $this->photo . "\" alt=\"" . $this->name . "photo\" />
                <a href=\"" . $this->mainEntityOfPage . "\">Main URL</a>
            </body>
            </html>";
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

    public function toHTML()
    {
        return "<!DOCTYPE html>
            <html>
            <head>
                <meta charset=\"utf-8\" />
                <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
                <title>Landmark" . $this->name . "</title>
                <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
                <script type=\"application/ld+json\">
                    " . $this->toJSON() . "
                </script>
            </head>
            <body>
                <h1>Landmark:" . $this->name . " </h1 >
                <h2 > Description: </h2 >
                    <p >" . $this->description . "</p>
                <h2 > Address: </h2 >
                <ul >
                    <li >
                    Locality: " . $this->address['addressLocality'] . "
                    </li >
                                    <li >
                    Region: " . $this->address['addressRegion'] . "
                    </li >
                                    <li >
                    Country: " . $this->address['addressCountry'] . "
                    </li >
                </ul >
                <img src = \"" . $this->photo . "\" alt=\"" . $this->name . "photo\" />
                <a href=\"" . $this->mainEntityOfPage . "\">Main URL</a>
            </body>
            </html>";
    }
}
