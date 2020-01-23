<?php
/**
 * Created by PhpStorm.
 * User: versus
 * Date: 02/04/18
 * Time: 10:45
 */

namespace NotificationBundle\Exception\Server;


class BadArgumentException extends \Exception
{
    protected $message = "La valeur de la connexion doit être de type 'object'";
}