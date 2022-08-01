<?php
namespace Src\TableGateways;

class UserGateway {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function findAll()
    {
        $statement = "
            SELECT 
                id, firstName, lastName, email
            FROM
                `user`;
        ";

        try {
            $statement = $this->db->query($statement);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function find($id)
    {
        $statement = "
            SELECT 
                *
            FROM
                `user`
            WHERE id = ?;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($id));
            $result = $statement->fetch(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function register(Array $input)
    {
        $statement = "
            INSERT INTO `user` 
                (firstName, lastName, email, username, password)
            VALUES
                (:firstName, :lastName, :email, :username, :password);
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'firstName' => $input['firstName'],
                'lastName'  => $input['lastName'],
                'email'  => $input['email'],
                'password'  => password_hash($input['password'], PASSWORD_DEFAULT),
                'username'  => $input['username'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function login(Array $input)
    {
        session_start();
        $username = $input['username'];
        $password = $input['password'];
        $statement = "
            SELECT * FROM `user` WHERE username = '$username'
        ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $result = $statement->fetch(\PDO::FETCH_ASSOC);
            if (password_verify($password, $result['password'])){
                $_SESSION['user_id'] = $result['id'];
                return $result;
            }
            else
                return array();

        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function logout()
    {
        session_start();
        session_destroy();
        return $_SESSION;
    }
    public function userByEmail(Array $input)
    {
        $email = $input['email'];
        $statement = "
            SELECT firstName, lastName FROM `user`
            WHERE email = '$email'
        ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            return $statement->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function update($id, Array $input)
    {
        $statement = "
            UPDATE `user`
            SET 
                firstName = :firstName,
                lastName  = :lastName,
                email = :email,
                password = :password,
                username = :username
            WHERE id = :id;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'id' => (int) $id,
                'firstName' => $input['firstName'],
                'lastName'  => $input['lastName'],
                'email'  => $input['email'],
                'password'  => password_hash($input['password'], PASSWORD_DEFAULT),
                'username'  => $input['username'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function delete($id)
    {
        $statement = "
            DELETE FROM `user`
            WHERE id = :id;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('id' => $id));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function validateInput($q, $input)
    {
        $statement = "
            SELECT * FROM `user` WHERE $q = '$input';
        ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            return $statement->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function importUsers(Array $input)
    {
        $responseObj = $input['file'];
        $statement = "
            INSERT INTO `user` 
                (firstName, lastName, email, username, password)
            VALUES
                (:firstName, :lastName, :email, :username, :password);
        ";
        $statement = $this->db->prepare($statement);
        foreach (json_decode($responseObj) as $userInfo)
        {
            $statement->execute(array(
                'firstName' => $userInfo->first_name,
                'lastName'  => $userInfo->last_name,
                'email'  => $userInfo->email,
                'username' => $userInfo->first_name . $userInfo->last_name,
                'password'  => password_hash($this->randomString(10), PASSWORD_DEFAULT),
            ));
        }
        return true;
    }
    private function randomString($length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZąĄčČĘęĖėįĮšŠųŲŪū';
        $randstring = '';
        for ($i = 0; $i < $length; $i++) {
            $randstring .= $characters[rand(0, strlen($characters))];
        }
        return $randstring;
    }
}