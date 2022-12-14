<?php
namespace Src\Controller;

use Src\TableGateways\UserGateway;

class UserController {

    private $dbConnection;
    private $requestMethod;
    private $action;
    private $id;

    private $userGateway;

    public function __construct($dbConnection, $requestMethod, $action, $id)
    {
        $this->dbConnection = $dbConnection;
        $this->requestMethod = $requestMethod;
        $this->action = $action;
        $this->id = $id;

        $this->userGateway = new UserGateway($dbConnection);
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->action === 'user') {
                    $response = $this->getAllUsers();
                    //$response = $this->getUser($this->id);
                } else if ($this->action === 'logout') {
                    $response = $this->logoutRequest();
                } else if ($this->action === 'user-info') {
                    $response = $this->getUser($this->id);
                }
                else {
                    $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
                };
                break;
            case 'POST':
                if ($this->action === 'register') {
                    $response = $this->createUserFromRequest();
                }
                else if ($this->action === 'login') {
                    $response = $this->loginFromRequest();
                }
                else if ($this->action === 'user-by-email') {
                    $response = $this->userByEmail();
                }
                else if ($this->action === 'update-user'){
                    $response = $this->updateUserFromRequest($this->id);
                }
                else if ($this->action === 'user-import'){
                    $response = $this->userImport();
                }
                else {
                    $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
                };
                break;

            case 'DELETE':
                $response = $this->deleteUser($this->id);
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

    private function getAllUsers()
    {
        $result = $this->userGateway->findAll();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(['data' => $result]);
        return $response;
    }

    private function getUser($id)
    {
        $result = $this->userGateway->find($id);
        if (! $result) {
            return $this->notFoundResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function createUserFromRequest()
    {
        if (! $this->validatePerson($_POST, true)) {
            return $this->unprocessableEntityResponse($_POST, true);
        }
        $this->userGateway->register($_POST);
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode([
            'success' => 'Vartotojas s??kmingai sukurtas'
        ]);
        return $response;
    }
    private function loginFromRequest()
    {

        $result = $this->userGateway->login($_POST);
        $response['status_code_header'] = 'HTTP/1.1 201 Login';
        if(!empty($result)){
            $response['body'] = json_encode([
                'success' => 'S??kmingai prisijungta',
                'data' => $result
            ]);
            return $response;
        }
        else {
            $response['body'] = json_encode([
                'warning' => 'Neteisingi prisijungimo duomenys',
                'error' => [
                    'username' => 'Vartotojo vardas neteisingas',
                    'password' => 'Neteisingas slapta??odis'
                ]
            ]);
            return $response;
        }
    }
    private function logoutRequest()
    {
        $this->userGateway->logout();
        $response['body'] = json_encode([
            'data' => ''
        ]);
        $response['status_code_header'] = 'HTTP/1.1 201 Logout';
        return $response;
    }
    private function userByEmail()
    {
        $result = $this->userGateway->userByEmail($_POST);
        $response['status_code_header'] = 'HTTP/1.1 201 OK';
        $response['body'] = json_encode($result);
        return $response;

    }
    private function updateUserFromRequest($id)
    {
        $result = $this->userGateway->find($id);
        if (! $result) {
            return $this->notFoundResponse();
        }
        if (! $this->validatePerson($_POST, false)) {
            return $this->unprocessableEntityResponse($_POST, false);
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->userGateway->update($id, $_POST);

        $response['body'] = json_encode(['success' => 'S??kmingai atnaujinta']);
        return $response;
    }
    private function userImport()
    {
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $result = $this->userGateway->importUsers($_POST);
        $response['body'] = json_encode(['success' => 'vartotoju importas s??kmingas', 'errors' => '', 'data' => $result]);
        return $response;
    }
    private function deleteUser($id)
    {
        $result = $this->userGateway->find($id);
        if (! $result) {
            return $this->notFoundResponse();
        }
        $this->userGateway->delete($id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    private function validatePerson($input, $username_check)
    {
        if($username_check){
            $validateUsername = $this->userGateway->validateInput('username', $input['username']);
        }

        $validateEmailFree = $this->userGateway->validateInput('email', $input['email']);
        $validateYourEmail = $this->userGateway->validateInput('email', $input['oldEmail']);
        if($validateUsername['username']){
            return false;
        }
        if($validateEmailFree['email'] && !($validateEmailFree['email'] === $validateYourEmail['email'])){
            return  false;
        }
        if (! isset($input['firstName']) || $input['firstName'] === '') {
            return false;
        }
        if (! isset($input['lastName']) || $input['lastName'] === '') {
            return false;
        }
        if (! isset($input['username']) || $input['username'] === '') {
            return false;
        }
        if (! isset($input['email']) || $input['email'] === '') {
            return false;
        }
        if (! isset($input['password']) || $input['password'] === '') {
            return false;
        }
        if (! isset($input['password_confirm']) || $input['password_confirm'] === '') {
            return false;
        }
        if ($input['password'] !== $input['password_confirm']) {
            return false;
        }
        if (ctype_lower($input['password'])) {
            return false;
        }
        if (!preg_match('#\d#',$input['password'])){
            return false;
        }
        else{

        }
        return true;
    }

    private function unprocessableEntityResponse($input, $username_check)
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $errors= (object) array();
        $validateEmailFree = $this->userGateway->validateInput('email', $input['email']);
        $validateYourEmail = $this->userGateway->validateInput('email', $input['oldEmail']);
        if($username_check){
            $validateUsername = $this->userGateway->validateInput('username', $input['username']);
        }
        if($validateUsername['username']){
            $errors->username = '??is vartotojo vardas jau yra u??imtas';
        }
        if($validateEmailFree['email'] && !($validateEmailFree['email'] === $validateYourEmail['email'])){
            $errors->email_exists = '??is el. pa??tas jau yra u??imtas';
        }
        if($input['firstName'] === '') {
            $errors->firstName = 'Vardas b??tinas';
        }
        if($input['lastName'] === '') {
            $errors->lastName = 'Pavard?? b??tina';
        }
        if($input['email'] === '') {
            $errors->email = 'El. pa??tas b??tinas';
        }
        if($input['username'] === '') {
            $errors->username = 'Vartotojo vardas b??tinas';
        }
        if($input['password'] === '') {
            $errors->password = 'Slapta??odis b??tinas';
        }
        if($input['password_confirm'] === '') {
            $errors->password_confirm = 'Slapta??odis b??tinas';
        }
        if($input['password'] !== $input['password_confirm']){
            $errors->password_not_equal = 'Slapta??odiai nesutampa';
        }
        if(ctype_lower($input['password'])){
            $errors->pass_upper = 'Slapta??odyje turi b??ti did??ioji raid??';
        }
        if (!preg_match('#\d#',$input['password'])){
            $errors->pass_num = 'Slapta??odyje turi b??ti skai??ius';
        }
        $response['body'] = json_encode(['data' => ['errors' => $errors]]);
        return $response;
    }

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }
}