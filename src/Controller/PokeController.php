<?php
namespace Src\Controller;

use Src\TableGateways\PokeGateway;

class PokeController
{

    private $dbConnection;
    private $requestMethod;
    private $action;
    private $id;

    private $pokeGateway;

    public function __construct($dbConnection, $requestMethod, $action, $id)
    {
        $this->dbConnection = $dbConnection;
        $this->requestMethod = $requestMethod;
        $this->action = $action;
        $this->id = $id;

        $this->pokeGateway = new PokeGateway($dbConnection);
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'POST':
                if ($this->action === 'poke-user') {
                    $response = $this->newPoke();
                } else if ($this->action === 'get-pokes') {
                    $response = $this->getPokes();
                } else if ($this->action === 'get-user-pokes') {
                    $response = $this->getUserPokes();
                } else if ($this->action === 'update-poke') {
                    $response = $this->updatePoke();
                } else if ($this->action === 'poke-import') {
                    $response = $this->pokeImport();
                } else {
                    $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
                };
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function newPoke()
    {
        if (!$this->validatePoke($_POST)) {
            return $this->pokeRecipientError();
        }
        $result = $this->pokeGateway->new($_POST);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(['success' => 'Sėkmingai bakstelta', 'data' => $result]);
        return $response;
    }

    private function getPokes()
    {
        $result = $this->pokeGateway->getAllPokes($_POST);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getUserPokes()
    {
        $result = $this->pokeGateway->getUserPokes($_POST);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }
    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }
    private function validatePoke($input)
    {
        if ($input['sender'] === $input['recipient']) {
            return false;
        }

        return true;
    }

    private function pokeRecipientError()
    {
        $response['status_code_header'] = 'HTTP/1.1 201 Poke Error';
        $response['body'] = json_encode(['error' => 'Negalima bakstelti savęs']);
        return $response;
    }
    private function updatePoke()
    {
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $result = $this->pokeGateway->updatePoke($_POST);
        $response['body'] = json_encode($result);
        return $response;
    }
    private function pokeImport()
    {
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $result = $this->pokeGateway->import($_POST);
        $response['body'] = json_encode(['success' => 'poke importas sėkmingas', 'data' => $result]);
        return $response;
    }

}