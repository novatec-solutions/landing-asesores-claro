<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

function getVars($enviroment = "test")
{
    $varsCon = ["test" => ["host" => "localhost", "dbname" => "ClaroTest", "user" => "root", "pass" => ""], "prod" => ["host" => "10.2.0.26:53306", "dbname" => "ClaroTest", "user" => "clarotestusr", "pass" => "pQxg58*7"], "OStest" => ["host" => "10.1.0.5", "dbname" => "ClaroTest", "user" => "clarotestusr", "pass" => "pQxg58*7"]];
    return $varsCon[$enviroment];
}

function ConexDBLanding($destination = "test")
{
    try {
        $dataCon = getVars($destination);
        $conn = new PDO("mysql:host={$dataCon["host"]};dbname={$dataCon["dbname"]}", $dataCon["user"], $dataCon["pass"]);
        $conn->exec("SET CHARACTER SET utf8mb4");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        return $conn;
    } catch (PDOException $e) {
        echo 'Falló la conexión: ' . $e->getMessage();
    }
}

function ConexDB($query)
{

    $conn = new PDO('mysql:host=10.2.0.26:53306;dbname=ivr_digital_db', 'clarotestusr', 'pQxg58*7');
    $conn->exec("SET CHARACTER SET utf8mb4");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = $conn->query($query)->fetchAll();

    return $sql;
}

function ConexDBInsert($query)
{

    $conn = new PDO('mysql:host=10.2.0.26:53306;dbname=ivr_digital_db', 'clarotestusr', 'pQxg58*7');
    $conn->exec("SET CHARACTER SET utf8mb4");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = $conn->query($query);

    return $sql;
}

function ConexDBIVR($query)
{
    $conn = new PDO('mysql:host=10.2.0.26:53306;dbname=ivr_digital_db', 'clarotestusr', 'pQxg58*7');
    $conn->exec("SET CHARACTER SET utf8mb4");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = $conn->query($query)->fetchAll();

    return $sql;
}

function ConexDBInsertIVR($query)
{
    $conn = new PDO('mysql:host=10.2.0.26:53306;dbname=ivr_digital_db', 'clarotestusr', 'pQxg58*7');
    $conn->exec("SET CHARACTER SET utf8mb4");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = $conn->query($query);

    return $sql;
}


function DbAsesor($query, $parameters = [], $expectSingleResult = false)
{
    $conn = new PDO('mysql:host=40.79.78.250;dbname=db_asesor', 'claroasesor', 'TGDob3/JM3hd.');
    if (is_string($query) && $query !== "" && is_array($parameters) && is_bool($expectSingleResult)) {
        try {
            $tquerey = $conn->prepare($query);
            foreach ($parameters as $placeholder => $value) {
                if (is_string($value)) {
                    $type = PDO::PARAM_STR;
                } elseif (is_int($value)) {
                    $type = PDO::PARAM_INT;
                } elseif (is_bool($value)) {
                    $type = PDO::PARAM_BOOL;
                } else {
                    $type = PDO::PARAM_NULL;
                }
                $tquerey->bindValue($placeholder, $value, $type);
            }
            $tquerey->execute();
            if ($expectSingleResult === true) {
                $results = $tquerey->fetch();
            } else {
                $results = $tquerey->fetchAll();
            }
            unset($tquerey);
            return $results;
        } catch (PDOException $e) {
            echo $e->getMessage();
            //$this->error = $e->getMessage();
        }
    } else {
        //$this->error = "Invalid Querey or Paramaters";
        return null;
    }
}

function DbAsesorConsulta($q, $uno = false)
{
    $conn = new PDO('mysql:host=65.151.179.241;dbname=asesor', 'asesorusr', '4%bcYWce=/8n');
    $conn->exec("SET CHARACTER SET utf8mb4");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = $conn->query($q);
    if ($uno) {
        return $sql->fetch(PDO::FETCH_ASSOC);
    } else {
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

}

function DbAsesorTransaccion($q)
{
    $conn = new PDO('mysql:host=65.151.179.241;dbname=asesor', 'asesorusr', '4%bcYWce=/8n');
    $conn->exec("SET CHARACTER SET utf8mb4");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = $conn->query($q);

    return $sql;
}
