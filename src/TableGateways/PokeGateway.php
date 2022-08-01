<?php
namespace Src\TableGateways;

class PokeGateway {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }


    public function new(Array $input)
    {
        $statement = "
            INSERT INTO `poke` 
                (sender, recipient, date_time)
            VALUES
                (:sender, :recipient, :date_time);
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'sender' => $input['sender'],
                'recipient'  => $input['recipient'],
                'date_time'  => (new \DateTime())->format('Y-m-d')
            ));
            $query = $this->db->prepare("SELECT * FROM `poke` ORDER BY id DESC LIMIT 1");
            $query->execute();
            $result = $query->fetch(\PDO::FETCH_ASSOC);
            return $result['id'];
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function getAllPokes(Array $input)
    {
        $recipient = $input['email'];
        $statement = "
            SELECT 
               COUNT(recipient) as count
            FROM
                `poke`
            WHERE recipient = '$recipient'
        ";

        try {
            $statement = $this->db->query($statement);
            return $statement->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function getUserPokes(Array $input)
    {
        $email = $input['email'];
        $limit = $input['limit'];
        $statement = "
            SELECT 
               *
            FROM
                `poke`
            WHERE recipient = '$email'
            LIMIT $limit
        ";

        try {
            $statement = $this->db->query($statement);
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function updatePoke(Array $input)
    {
        $statement = "
            UPDATE `poke`
            SET 
                recipient = :newEmail
            WHERE recipient = :oldEmail;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'oldEmail' => $input['oldEmail'],
                'newEmail'  => $input['newEmail'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function import(Array $input)
    {
        $responseObj = $input['file'];
        $statement = "
            INSERT INTO `poke` 
                (sender, recipient, date_time)
            VALUES
                (:sender, :recipient, :date_time);
        ";
        $statement = $this->db->prepare($statement);
        foreach (json_decode($responseObj) as $pokes){
            $statement->execute(array(
                'sender' => $pokes->from,
                'recipient'  => $pokes->to,
                'date_time'  => $pokes->date
            ));
        }
        return true;
    }
}