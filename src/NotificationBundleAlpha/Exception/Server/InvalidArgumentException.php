<?php
/**
 * Created by PhpStorm.
 * User: versus
 * Date: 02/04/18
 * Time: 10:45
 */

namespace NotificationBundle\Exception\Server;


class InvalidArgumentException extends \Exception
{
    protected $message = "La valeur de la connexion doit être une instance de ConnectionInterface";
}